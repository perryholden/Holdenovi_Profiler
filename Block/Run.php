<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Block;

use Holdenovi\Profiler\Api\Data\RunInterface;
use Holdenovi\Profiler\Registry\CurrentRun;

class Run extends \Magento\Backend\Block\Template
{
    /**
     * @var CurrentRun
     */
    protected $currentRunRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param CurrentRun $currentRunRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        CurrentRun $currentRunRegistry,
        array $data = []
    ) {
        $this->currentRunRegistry = $currentRunRegistry;
        parent::__construct($context, $data);
    }

    /**
     * @return RunInterface
     */
    public function getRun(): RunInterface
    {
        return $this->currentRunRegistry->get();
    }
}
