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
}

