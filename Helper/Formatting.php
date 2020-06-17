<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Formatting extends AbstractHelper
{
    /**
     * @param $duration
     * @param int $precision
     * @return float
     */
    public function format_time($duration, $precision = 0)
    {
        return round($duration * 1000, $precision);
    }

    /**
     * @param $bytes
     * @return string
     */
    public function format_realmem($bytes)
    {
        return $this->format_emalloc($bytes);
    }

    /**
     * @param $bytes
     * @return string
     */
    public function format_emalloc($bytes)
    {
        $res = number_format($bytes / (1024 * 1024), 2);
        if ($res == '-0.00') {
            $res = '0.00'; // looks silly otherwise
        }
        return $res;
    }

    /**
     * @param $value
     * @return float
     */
    public function formatTimeDecorator($value)
    {
        return round($value, 3);
    }

    /**
     * @param $value
     * @return string
     */
    public function formatMemoryDecorator($value)
    {
        return $this->format_emalloc($value);
    }
}
