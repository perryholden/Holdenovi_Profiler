<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Model\ResourceModel\Run;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'run_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Holdenovi\Profiler\Model\Run::class,
            \Holdenovi\Profiler\Model\ResourceModel\Run::class
        );
    }
}

