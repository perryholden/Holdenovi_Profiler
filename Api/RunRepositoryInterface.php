<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Api;

use Holdenovi\Profiler\Api\Data\RunInterface;
use Holdenovi\Profiler\Api\Data\RunSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface RunRepositoryInterface
{
    /**
     * Save Run
     *
     * @param RunInterface $run
     * @return RunInterface
     * @throws LocalizedException
     */
    public function save(RunInterface $run
    );

    /**
     * Retrieve Run
     *
     * @param string $runId
     * @return RunInterface
     * @throws LocalizedException
     */
    public function get($runId);

    /**
     * Retrieve Run matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return RunSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Run
     *
     * @param RunInterface $run
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        RunInterface $run
    );

    /**
     * Delete Run by ID
     *
     * @param string $runId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($runId);
}
