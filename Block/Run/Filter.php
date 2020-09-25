<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Block\Run;

class Filter extends \Holdenovi\Profiler\Block\Run
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        return '<h2>Filter</h2>' . parent::_toHtml();
    }
}
