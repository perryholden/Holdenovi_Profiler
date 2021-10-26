<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Model;

use Holdenovi\Profiler\Api\Data\RunInterfaceFactory;
use Holdenovi\Profiler\Api\Data\RunSearchResultsInterfaceFactory;
use Holdenovi\Profiler\Api\RunRepositoryInterface;
use Holdenovi\Profiler\Model\ResourceModel\Run as ResourceRun;
use Holdenovi\Profiler\Model\ResourceModel\Run\CollectionFactory as RunCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class RunRepository implements RunRepositoryInterface
{
    /**
     * @var ResourceRun
     */
    protected $resource;

    /**
     * @var RunFactory
     */
    protected $runFactory;

    /**
     * @var RunCollectionFactory
     */
    protected $runCollectionFactory;

    /**
     * @var RunSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var RunInterfaceFactory
     */
    protected $dataRunFactory;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceRun $resource
     * @param RunFactory $runFactory
     * @param RunInterfaceFactory $dataRunFactory
     * @param RunCollectionFactory $runCollectionFactory
     * @param RunSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceRun $resource,
        RunFactory $runFactory,
        RunInterfaceFactory $dataRunFactory,
        RunCollectionFactory $runCollectionFactory,
        RunSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->runFactory = $runFactory;
        $this->runCollectionFactory = $runCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataRunFactory = $dataRunFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Holdenovi\Profiler\Api\Data\RunInterface $run
    ) {
        /* if (empty($run->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $run->setStoreId($storeId);
        } */
        
        $runData = $this->extensibleDataObjectConverter->toNestedArray(
            $run,
            [],
            \Holdenovi\Profiler\Api\Data\RunInterface::class
        );
        
        $runModel = $this->runFactory->create()->setData($runData);
        
        try {
            $this->resource->save($runModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the run: %1',
                $exception->getMessage()
            ));
        }
        return $runModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($runId)
    {
        $run = $this->runFactory->create();
        $this->resource->load($run, $runId);
        if (!$run->getId()) {
            throw new NoSuchEntityException(__('Run with id "%1" does not exist.', $runId));
        }
        return $run->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->runCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Holdenovi\Profiler\Api\Data\RunInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $dataModel = $model->getDataModel();
            $dataModel->setStackData(''); // Blank out stack data, because it's not needed on the grid...
            $items[] = $dataModel;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Holdenovi\Profiler\Api\Data\RunInterface $run
    ) {
        try {
            $runModel = $this->runFactory->create();
            $this->resource->load($runModel, $run->getRunId());
            $this->resource->delete($runModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Run: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($runId)
    {
        return $this->delete($this->get($runId));
    }
}

