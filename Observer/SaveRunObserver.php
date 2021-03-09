<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Observer;

use Holdenovi\Profiler\Api\Data\RunInterfaceFactory;
use Holdenovi\Profiler\Api\RunRepositoryInterface;
use Holdenovi\Profiler\Driver\Standard\Stat;
use Holdenovi\Profiler\Driver\Hierarchy;
use Holdenovi\Profiler\Model\Data\Run;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class SaveRunObserver implements ObserverInterface
{
    /**
     * @var RunRepositoryInterface
     */
    protected $runRepository;

    /**
     * @var RunInterfaceFactory
     */
    protected $runFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param RunRepositoryInterface $runRepository
     * @param RunInterfaceFactory $runFactory
     * @param DateTime $dateTime
     * @param SessionManagerInterface $sessionManager
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        RunRepositoryInterface $runRepository,
        RunInterfaceFactory $runFactory,
        DateTime $dateTime,
        SessionManagerInterface $sessionManager,
        RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->runRepository = $runRepository;
        $this->runFactory = $runFactory;
        $this->dateTime = $dateTime;
        $this->session = $sessionManager;
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (Hierarchy::isInternalEnabled()) {

            // This also removes the time entry for this observer
            Hierarchy::disable();

            if (Hierarchy::checkThresholds()) {

                // Save this run to the DB
                /** @var Run $run */
                $run = $this->runFactory->create();

                if ($run->isObjectNew() && !$run->getCreatedAt()) {
                    $run->setCreatedAt($this->dateTime->gmtDate());
                }

                $run->setStackData(serialize(Hierarchy::getStackLog()));
                $run->setUrl($this->request->getRequestUri());
                $run->setRoute($this->request->getFullActionName());
                $run->setSessionId($this->session->getSessionId());

                $totals = Stat::getTotals();
                $run->setTotalTime($totals['time']);
                $run->setTotalRealMemory((float)$totals['realmem']/(1024*1024));
                $run->setTotalAllocatedMemory((float)$totals['emalloc']/(1024*1024));

                try {
                    $this->runRepository->save($run);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }

        }
    }
}
