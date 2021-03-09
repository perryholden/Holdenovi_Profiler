<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Api\Data;

interface RunInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public const RUN_ID = 'run_id';
    public const CREATED_AT = 'created_at';
    public const STACK_DATA = 'stack_data';
    public const ROUTE = 'route';
    public const URL = 'url';
    public const SESSION_ID = 'session_id';
    public const TOTAL_TIME = 'total_time';
    public const TOTAL_REAL_MEMORY = 'real_memory';
    public const TOTAL_ALLOCATED_MEMORY = 'allocated_memory';

    /**
     * Get run_id
     *
     * @return string|null
     */
    public function getRunId();

    /**
     * Set run_id
     *
     * @param string $runId
     * @return $this
     */
    public function setRunId($runId);

    /**
     * Get created_at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created_at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get stack_data
     *
     * @return string
     */
    public function getStackData();

    /**
     * Set stack_data
     *
     * @param string $stackData
     * @return $this
     */
    public function setStackData($stackData);

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute();

    /**
     * Set route
     *
     * @param string $route
     * @return $this
     */
    public function setRoute($route);

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Set url
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url);

    /**
     * Get session_id
     *
     * @return string
     */
    public function getSessionId();

    /**
     * Set session_id
     *
     * @param string $sessionId
     * @return $this
     */
    public function setSessionId($sessionId);

    /**
     * Get total_time
     *
     * @return float
     */
    public function getTotalTime();

    /**
     * Set total_time
     *
     * @param float $totalTime
     * @return $this
     */
    public function setTotalTime($totalTime);

    /**
     * Get total_real_memory
     *
     * @return float
     */
    public function getTotalRealMemory();

    /**
     * Set total_real_memory
     *
     * @param float $totalRealMemory
     * @return $this
     */
    public function setTotalRealMemory($totalRealMemory);

    /**
     * Get total_allocated_memory
     *
     * @return float
     */
    public function getTotalAllocatedMemory();

    /**
     * Set total_allocated_memory
     *
     * @param float $totalAllocatedMemory
     * @return $this
     */
    public function setTotalAllocatedMemory($totalAllocatedMemory);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Holdenovi\Profiler\Api\Data\RunExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Holdenovi\Profiler\Api\Data\RunExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Holdenovi\Profiler\Api\Data\RunExtensionInterface $extensionAttributes
    );
}
