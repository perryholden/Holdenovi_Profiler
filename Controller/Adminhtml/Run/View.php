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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Holdenovi\Profiler\Api\RunRepositoryInterface;
use Holdenovi\Profiler\Model\Run;
use Holdenovi\Profiler\Registry\CurrentRun;

class View extends \Holdenovi\Profiler\Controller\Adminhtml\Run
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RunRepositoryInterface
     */
    protected $runRepository;

    /**
     * @var CurrentRun
     */
    protected $currentRunRegistry;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RunRepositoryInterface $runRepository
     * @param CurrentRun $currentRunRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RunRepositoryInterface $runRepository,
        CurrentRun $currentRunRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->runRepository = $runRepository;
        $this->currentRunRegistry = $currentRunRegistry;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('run_id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('Cannot manually create a new profile record.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

        /** @var Run $model */
        try {
            $model = $this->runRepository->get($id);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Appliance with id %1 does not exist. Please try again.'), $id);
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

        $this->currentRunRegistry->set($model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->getConfig()->getTitle()->prepend(__('Profiler Run'));
        return $resultPage;
    }
}
