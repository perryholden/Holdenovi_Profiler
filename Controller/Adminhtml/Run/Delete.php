<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Controller\Adminhtml\Run;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Holdenovi\Profiler\Api\RunRepositoryInterface;

class Delete extends \Holdenovi\Profiler\Controller\Adminhtml\Run implements HttpPostActionInterface
{
    /**
     * @var RunRepositoryInterface
     */
    protected $runRepository;

    /**
     * @param Context $context
     * @param RunRepositoryInterface $runRepository
     */
    public function __construct(
        Context $context,
        RunRepositoryInterface $runRepository
    ) {
        parent::__construct($context);
        $this->runRepository = $runRepository;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        if ($runId = $this->getRequest()->getParam('run_id')) {
            $this->runRepository->deleteById($runId);
            $this->messageManager->addSuccessMessage(__('Run %1 has been deleted.', $runId));
        } else {
            $this->messageManager->addSuccessMessage(__('No ID given'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('profiler/run/index');
    }
}
