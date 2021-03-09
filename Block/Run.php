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
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ToolbarInterface;

class Run extends \Magento\Backend\Block\Template
{
    /**
     * @var CurrentRun
     */
    protected $currentRunRegistry;

    /**
     * @var ButtonList
     */
    protected $buttonList;

    /**
     * @var ToolbarInterface
     */
    protected $toolbar;

    /**
     * @param Context $context
     * @param CurrentRun $currentRunRegistry
     * @param ButtonList $buttonList
     * @param ToolbarInterface $toolbar
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrentRun $currentRunRegistry,
        ButtonList $buttonList,
        ToolbarInterface $toolbar,
        array $data = []
    ) {
        $this->currentRunRegistry = $currentRunRegistry;
        $this->buttonList = $buttonList;
        $this->toolbar = $toolbar;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $this->buttonList->add(
            'back',
            [
                'label' => __('Back'),
                'onclick' => "window.location.href = '" . $this->getUrl('profiler/run/index') . "'",
                'class' => 'back'
            ]
        );

        $this->buttonList->add(
            'delete',
            [
                'label' => __('Delete Run'),
                'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getUrl(
                        'profiler/run/delete',
                        ['run_id' => $this->getRun()->getRunId()]
                    ) . '\', {data: {}})',
                'class' => 'delete'
            ]
        );


        $this->toolbar->pushButtons($this, $this->buttonList);

        return parent::_prepareLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function canRender(\Magento\Backend\Block\Widget\Button\Item $item)
    {
        return !$item->isDeleted();
    }

    /**
     * @return RunInterface
     */
    public function getRun(): RunInterface
    {
        return $this->currentRunRegistry->get();
    }
}
