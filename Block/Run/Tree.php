<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Block\Run;

use Holdenovi\Profiler\Helper\Data as DataHelper;
use Holdenovi\Profiler\Helper\Formatting as FormattingHelper;
use Holdenovi\Profiler\Registry\CurrentRun;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ToolbarInterface;

class Tree extends \Holdenovi\Profiler\Block\Run
{
    /**
     * @var FormattingHelper
     */
    protected $formattingHelper;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var array metrics to be displayed
     */
    protected $metrics = ['time', 'realmem'/*, 'emalloc'*/];

    /**
     * @var array units
     */
    protected $units = array(
        'time' => 'ms',
        'realmem' => 'MB',
        'emalloc' => 'MB'
    );

    /**
     * @var array stack log data
     */
    protected $stackLog;

    /**
     * @var array hierarchical representation of the stack log data
     */
    protected $treeData;

    /**
     * @param Context $context
     * @param CurrentRun $currentRunRegistry
     * @param ButtonList $buttonList
     * @param ToolbarInterface $toolbar
     * @param FormattingHelper $formattingHelper
     * @param DataHelper $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrentRun $currentRunRegistry,
        ButtonList $buttonList,
        ToolbarInterface $toolbar,
        FormattingHelper $formattingHelper,
        DataHelper $dataHelper,
        array $data = [])
    {
        $this->formattingHelper = $formattingHelper;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $currentRunRegistry, $buttonList, $toolbar, $data);
    }


    /**
     * Render tree (recursive function)
     *
     * @param array $data
     * @return string
     */
    public function renderTree(array $data)
    {
        $output = '';
        foreach ($data as $key => $uniqueId) {
            if (strpos((string)$key, '_children') === false) {

                $tmp = $this->stackLog[$uniqueId];

                $hasChildren = isset($data[$key . '_children']) && count($data[$key . '_children']) > 0;

                $duration = round($tmp['time_total'] * 1000);
                $output .= '<li duration="' . $duration . '" class="' . ($tmp['level'] > 1 ? 'collapsed' : '') . ' level-' . $tmp['level'] . ' ' . ($hasChildren ? 'has-children' : '') . '">';

                $output .= '<div class="info">';

                $output .= '<div class="profiler-label">';
                if ($hasChildren) {
                    $output .= '<div class="toggle profiler-open">&nbsp;</div>';
                    $output .= '<div class="toggle profiler-closed">&nbsp;</div>';
                }

                $label = end($tmp['stack']);

                if (isset($tmp['detail'])) {
                    $label .= ' (' . htmlspecialchars($tmp['detail']) . ')';
                }

                $type = \Holdenovi\Profiler\Driver\Standard\Stat::getType($tmp['type'], $label);

                $output .= '<span class="caption type-' . $type . '" title="' . htmlspecialchars($label) . '" />';

                if (isset($tmp['file'])) {
                    $remoteCallUrlTemplate = $this->dataHelper->getRemoteCallUrlTemplate();
                    $linkTemplate = '<a href="%s" onclick="var ajax = new XMLHttpRequest(); ajax.open(\'GET\', this.href); ajax.send(null); return false">%s</a>';
                    $url = sprintf($remoteCallUrlTemplate, $tmp['file'], intval($tmp['line']));
                    $output .= sprintf($linkTemplate, $url, htmlspecialchars($label));
                } else {
                    $output .= htmlspecialchars($label);
                }

                $output .= '</span>';

                $output .= '</div>'; // class="label"

                $output .= '<div class="profiler-columns">';
                foreach ($this->metrics as $metric) {
                    $formatterMethod = 'format_' . $metric;
                    $ownTitle = 'Own: ' . $this->formattingHelper->$formatterMethod($tmp[$metric . '_own']) . ' '
                        . $this->units[$metric] . ' / ' . round($tmp[$metric . '_rel_own'] * 100, 2) . '%';
                    $subTitle = 'Sub: ' . $this->formattingHelper->$formatterMethod($tmp[$metric . '_sub']) . ' '
                        . $this->units[$metric] . ' / ' . round($tmp[$metric . '_rel_sub'] * 100, 2) . '%';
                    $totalTitle = $this->formattingHelper->$formatterMethod($tmp[$metric . '_own'] + $tmp[$metric . '_sub']) . ' '
                        . $this->units[$metric] . ' / '
                        . round(($tmp[$metric . '_rel_own'] + $tmp[$metric . '_rel_sub']) * 100, 2) . '%';
                    $fullTitle = $totalTitle . ' (' . $ownTitle . ', ' . $subTitle . ')';

                    $output .= '<div class="metric" title="' . $fullTitle . '">';

                    $progressBar = $this->renderProgressBar(
                        $tmp[$metric . '_rel_own'] * 100,
                        $tmp[$metric . '_rel_sub'] * 100,
                        $tmp[$metric . '_rel_offset'] * 100
                    );
                    $output .= '<div class="' . $metric . ' profiler-column">' . $progressBar . '</div>';

                    $output .= '</div>'; // class="metric"

                }
                $output .= '</div>'; // class="profiler-columns"

                $output .= '</div>'; // class="info"

                if ($hasChildren) {
                    $output .= '<ul>' . $this->renderTree($data[$key . '_children']) . '</ul>';
                }

                $output .= '</li>';
            }
        }
        return $output;
    }

    /**
     * To HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        /* @var $stackModel \Holdenovi\Profiler\Model\Data\Run */
        $stackModel = $this->currentRunRegistry->get();

        $stackModel->processRawData();

        $this->stackLog = $stackModel->getStackLog();
        $this->treeData = $stackModel->getTreeData();

        $output = '<div id="profiler">';

//        if (Mage::getSingleton('core/resource')->getConnection('core_read')->getProfiler()->getEnabled()) {
//            $output .= '<p>Number of database queries: ' . Mage::getSingleton('core/resource')->getConnection('core_read')->getProfiler()->getTotalNumQueries() . '</p>';
//        }

        $output .= $this->renderHeader();

        $output .= '<ul id="treeView" class="treeView">';
        $output .= $this->renderTree($this->treeData);
        $output .= '</ul>';

        $output .= '</div>';

        return $output;
    }

    /**
     * Render header
     *
     * @return string
     */
    protected function renderHeader()
    {
        $captions = '<ul>
            <li class="captions">
                <div class="info">';
        $captions .= '<div class="profiler-columns">';
        foreach ($this->metrics as $metric) {
            $captions .= '<div class="metric">';
            $captions .= '<div class="profiler-column3">';
            $captions .= __($metric);
            $captions .= '</div>';
            $captions .= '</div>';
        }
        $captions .= '</div>';
        $captions .= '</div>
            </li>
        </ul>';

        $captions .= '<ul>
            <li class="captions captions-line">
                <div class="info">
                    <div class="profiler-label">' . __('Name') . '
                        <a id="expand-all" href="#">[' . __('expand all') . ']</a>
                        <a id="collapse-all" href="#">[' . __('collapse all') . ']</a>
                    </div>';
        $captions .= '<div class="profiler-columns">';
        foreach ($this->metrics as $metric) {
            $formatterMethod = 'format_' . $metric;
            $captions .= '<div class="metric">';
            $captions .= '<div class="profiler-column3">';
            $captions .= $this->formattingHelper->$formatterMethod($this->stackLog['timetracker_0'][$metric . '_total']) . ' ' . $this->units[$metric];
            $captions .= '</div>';
            $captions .= '</div>';
        }
        $captions .= '</div>';
        $captions .= '</div>
            </li>
        </ul>';

        return $captions;
    }

    /**
     * Render css progress bar
     *
     * @param $percent1
     * @param int $percent2
     * @param int $offset
     * @return string
     */
    protected function renderProgressBar($percent1, $percent2, $offset)
    {
        $percent1 = round(max(1, $percent1));
        $offset = round(max(0, $offset));
        $offset = round(min(99, $offset));

        $output = '<div class="progress">';
        $output .= '<div class="progress-bar">';
        $output .= '<div class="progress-bar1" style="width: ' . $percent1 . '%; margin-left: ' . $offset . '%;"></div>';

        if ($percent2 > 0) {
            $percent2 = round(max(1, $percent2));
            if ($percent1 + $percent2 + $offset > 100) {
                // preventing line break in css progress bar if widths and margins are bigger than 100%
                $percent2 = 100 - $percent1 - $offset;
                $percent2 = max(0, $percent2);
            }
            $output .= '<div class="progress-bar2" style="width: ' . $percent2 . '%"></div>';
        }

        $output .= '</div>';
        $output .= '</div>';
        return $output;
    }
}
