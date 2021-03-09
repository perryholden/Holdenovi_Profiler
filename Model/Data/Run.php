<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Model\Data;

use Holdenovi\Profiler\Api\Data\RunInterface;

class Run extends \Magento\Framework\Model\AbstractExtensibleModel implements RunInterface
{
    /**
     * @var array
     */
    protected $stackLog;

    /**
     * @var array
     */
    protected $treeData;

    /**
     * @var array
     */
    protected $metrics = ['time', 'realmem'/*, 'emalloc'*/];

    /**
     * Get run_id
     *
     * @return string
     */
    public function getRunId()
    {
        return $this->getData(self::RUN_ID);
    }

    /**
     * Set run_id
     *
     * @param string $runId
     * @return $this
     */
    public function setRunId($runId)
    {
        return $this->setData(self::RUN_ID, $runId);
    }

    /**
     * Get created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created_at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get stack_data
     *
     * @return string
     */
    public function getStackData()
    {
        return $this->getData(self::STACK_DATA);
    }

    /**
     * Set stack_data
     *
     * @param string $stackData
     * @return $this
     */
    public function setStackData($stackData)
    {
        return $this->setData(self::STACK_DATA, $stackData);
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->getData(self::ROUTE);
    }

    /**
     * Set route
     *
     * @param string $route
     * @return $this
     */
    public function setRoute($route)
    {
        return $this->setData(self::ROUTE, $route);
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * Set url
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * Get session_id
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->getData(self::SESSION_ID);
    }

    /**
     * Set session_id
     *
     * @param string $sessionId
     * @return $this
     */
    public function setSessionId($sessionId)
    {
        return $this->setData(self::SESSION_ID, $sessionId);
    }

    /**
     * Get total_time
     *
     * @return float
     */
    public function getTotalTime()
    {
        return $this->getData(self::TOTAL_TIME);
    }

    /**
     * Set total_time
     *
     * @param float $totalTime
     * @return $this
     */
    public function setTotalTime($totalTime)
    {
        return $this->setData(self::TOTAL_TIME, $totalTime);
    }

    /**
     * Get total_real_memory
     *
     * @return float
     */
    public function getTotalRealMemory()
    {
        return $this->getData(self::TOTAL_REAL_MEMORY);
    }

    /**
     * Set total_real_memory
     *
     * @param float $totalRealMemory
     * @return $this
     */
    public function setTotalRealMemory($totalRealMemory)
    {
        return $this->setData(self::TOTAL_REAL_MEMORY, $totalRealMemory);
    }

    /**
     * Get total_allocated_memory
     *
     * @return float
     */
    public function getTotalAllocatedMemory()
    {
        return $this->getData(self::TOTAL_ALLOCATED_MEMORY);
    }

    /**
     * Set total_allocated_memory
     *
     * @param float $totalAllocatedMemory
     * @return $this
     */
    public function setTotalAllocatedMemory($totalAllocatedMemory)
    {
        return $this->setData(self::TOTAL_ALLOCATED_MEMORY, $totalAllocatedMemory);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Holdenovi\Profiler\Api\Data\RunExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Holdenovi\Profiler\Api\Data\RunExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Holdenovi\Profiler\Api\Data\RunExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }


    /**
     * @return \Holdenovi\Profiler\Model\Run
     */
    public function processRawData() : self
    {
        // First, manually set this property here
        $this->stackLog = unserialize($this->getStackData(), ['allowed_classes' => false]);

        // Create hierarchical array of keys pointing to the stack
        foreach ($this->stackLog as $uniqueId => $data) {
            $this->createHierarchyArray($this->treeData, $data['level'], $uniqueId);
        }

        $this->treeData = end($this->treeData);
        $this->updateValues($this->treeData);

        $this->calcRelativeValues();

        return $this;
    }

    /**
     * Helper function for internal data manipulation (Recursive function)
     *
     * @param array $arr
     * @param int $pointer
     * @param string $uniqueId
     * @return void
     */
    protected function createHierarchyArray(&$arr, $pointer, $uniqueId) : void
    {
        if (!is_array($arr)) {
            $arr = [];
        }
        if ($pointer > 0) {
            end($arr);
            $k = key($arr);
            $this->createHierarchyArray($arr[(int)$k . '_children'], $pointer - 1, $uniqueId);
        } else {
            $arr[] = $uniqueId;
        }
    }

    /**
     * Update values. (Recursive function)
     *
     * @param $arr
     * @param string $vKey
     */
    protected function updateValues(&$arr, $vKey = '')
    {
        $subSum = array_flip($this->metrics);
        foreach ($arr as $k => $v) {

            if (strpos((string)$k, '_children') === false) {
                $tempKey = $k . '_children';
                if (isset($arr[$tempKey]) && is_array($arr[$k . '_children'])) {
                    $this->updateValues($arr[$k . '_children'], $v);
                } else {
                    foreach ($subSum as $key => $value) {
                        $this->stackLog[$v][$key . '_sub'] = 0;
                        $this->stackLog[$v][$key . '_own'] = $this->stackLog[$v][$key . '_total'];
                    }
                }
                foreach ($subSum as $key => $value) {
                    $subSum[$key] += $this->stackLog[$v][$key . '_total'];
                }
            }
        }
        if (isset($this->stackLog[$vKey])) {
            foreach ($subSum as $key => $value) {
                $this->stackLog[$vKey][$key . '_sub'] = $subSum[$key];
                $this->stackLog[$vKey][$key . '_own'] = $this->stackLog[$vKey][$key . '_total'] - $subSum[$key];
            }
        }
    }

    /**
     * Calculate relative values on the entire stack log
     */
    protected function calcRelativeValues()
    {
        foreach ($this->stackLog as $key => $value) {
            foreach ($this->metrics as $metric) {
                foreach (['own', 'sub', 'total'] as $column) {
                    $tempKey = $metric . '_' . $column;
                    if (!isset($this->stackLog[$key][$tempKey])) {
                        continue;
                    }
                    try {
                        $this->stackLog[$key][$metric . '_rel_' . $column] = $this->stackLog[$key][$metric . '_' . $column] / $this->stackLog['timetracker_0'][$metric . '_total'];
                    } catch (\Exception $e) {
                        $this->stackLog[$key][$metric . '_rel_' . $column] = 0;
                    }
                }
                try {
                    $this->stackLog[$key][$metric . '_rel_offset'] = $this->stackLog[$key][$metric . '_start_relative'] / $this->stackLog['timetracker_0'][$metric . '_total'];
                } catch (\Exception $e) {
                    $this->stackLog[$key][$metric . '_rel_offset'] = 0;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getTreeData()
    {
        return $this->treeData;
    }

    /**
     * @return array
     */
    public function getStackLog()
    {
        return $this->stackLog;
    }
}

