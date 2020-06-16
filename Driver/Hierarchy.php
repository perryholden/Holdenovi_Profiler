<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Driver;

use Holdenovi\Profiler\Driver\Standard\Stat;
use Magento\Framework\Profiler\Driver\Standard;

class Hierarchy extends Standard
{
    /**
     * Storage for timers statistics
     *
     * @var Stat
     */
    protected $_stat;

    /**
     * @inheritDoc
     */
    public function display(): void
    {
        // Instead of displaying, write to the run table via an observer
        return;
    }

    /**
     * @inheritDoc
     */
    protected function _initOutputs(array $config = null): void
    {
        // Skip initializing outputs, as we will output to the run table
        return;
    }

    /**
     * @inheritDoc
     */
    protected function _initStat(array $config = null): void
    {
        if (isset($config['stat']) && $config['stat'] instanceof Stat) {
            $this->_stat = $config['stat'];
        } else {
            // This is the exact same method, but I can instantiate my own "Stat" class instead
            $this->_stat = new Stat();
        }
    }
}
