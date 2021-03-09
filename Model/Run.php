<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Model;

use Holdenovi\Profiler\Api\Data\RunInterface;
use Holdenovi\Profiler\Api\Data\RunInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Run extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var RunInterfaceFactory
     */
    protected $runDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var string
     */
    protected $_eventPrefix = 'holdenovi_profiler_run';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param RunInterfaceFactory $runDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Holdenovi\Profiler\Model\ResourceModel\Run $resource
     * @param \Holdenovi\Profiler\Model\ResourceModel\Run\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        RunInterfaceFactory $runDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Holdenovi\Profiler\Model\ResourceModel\Run $resource,
        \Holdenovi\Profiler\Model\ResourceModel\Run\Collection $resourceCollection,
        array $data = []
    ) {
        $this->runDataFactory = $runDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve run model with run data
     *
     * @return RunInterface
     */
    public function getDataModel() : RunInterface
    {
        $runData = $this->getData();
        
        $runDataObject = $this->runDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $runDataObject,
            $runData,
            RunInterface::class
        );
        
        return $runDataObject;
    }
}

