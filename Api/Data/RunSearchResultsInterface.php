<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Api\Data;

interface RunSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Run list.
     *
     * @return \Holdenovi\Profiler\Api\Data\RunInterface[]
     */
    public function getItems();

    /**
     * Set content list.
     *
     * @param \Holdenovi\Profiler\Api\Data\RunInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
