<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Registry;

use Holdenovi\Profiler\Api\Data\RunInterface;
use Holdenovi\Profiler\Api\Data\RunInterfaceFactory;

class CurrentRun
{
    /**
     * @var RunInterface
     */
    protected $run;

    /**
     * @var RunInterfaceFactory
     */
    protected $runFactory;

    /**
     * @param RunInterfaceFactory $runFactory
     */
    public function __construct(RunInterfaceFactory $runFactory)
    {
        $this->runFactory = $runFactory;
    }

    /**
     * @param RunInterface $run
     */
    public function set(RunInterface $run): void
    {
        $this->run = $run;
    }

    /**
     * @return RunInterface
     */
    public function get(): RunInterface
    {
        return $this->run ?? $this->createNullRun();
    }

    /**
     * @return RunInterface
     */
    protected function createNullRun(): RunInterface
    {
        return $this->runFactory->create();
    }
}
