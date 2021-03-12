<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Driver\Standard;

class Stat
{
    protected const TYPE_TEMPLATE = 'template';
    protected const TYPE_EVENT = 'event';
    protected const TYPE_OBSERVER = 'observer';
    protected const TYPE_DATABASE = 'db';
    protected const TYPE_CACHE = 'cache';
    protected const TYPE_DEFAULT = 'default';

    // Set at the beginning and will be used in relative values "on-stop" as well as totals calculations.
    static public $startValues = [];
    // Tracks the depth of the stack
    static protected $stackLevel = 0;
    // Tracks the current stack
    static public $stack = [];
    // Holds the entire log which will be saved to the DB
    static public $stackLog = [];
    // Used to make stackLog entries unique (e.g. timetracker_0)
    static protected $uniqueCounter = 0;
    // Current pointer where in the stack we are located
    static protected $currentPointerStack = []; // [timetracker_0, timetracker_1]

    static protected $_logCallStack = false;

    /**
     * Pushes to the stack
     *
     * @param string $name
     * @param string $time
     * @param string $realMemory
     * @param string $emallocMemory
     * @param string $type
     * @return void
     */
    public static function start($name, $time, $realMemory, $emallocMemory, $type = '') : void
    {
        $currentPointer = 'timetracker_' . self::$uniqueCounter++;
        self::$currentPointerStack[] = $currentPointer;
        self::$stack[] = $name;

        self::$stackLevel++;

        self::$stackLog[$currentPointer] = array(
            'level' => self::$stackLevel,
            'stack' => self::$stack,
            'time_start' => $time,
            'realmem_start' => $realMemory,
            'emalloc_start' => $emallocMemory,
            'type' => $type,
        );
    }

    /**
     * Get type
     *
     * @param $type
     * @param $label
     * @return string
     */
    public static function getType($type, $label)
    {
        if (empty($type)) {
            if (substr($label, -1 * strlen('.phtml')) === '.phtml') {
                $type = self::TYPE_TEMPLATE;
            } elseif (strpos($label, 'EVENT:') === 0) {
                $type = self::TYPE_EVENT;
            } elseif (strpos($label, 'OBSERVER:') === 0) {
                $type = self::TYPE_OBSERVER;
            } elseif (strpos($label, 'EAV:') === 0) {
                $type = self::TYPE_DATABASE;
            } elseif ((strpos($label, 'cache_load') === 0) || (strpos($label, 'cache_frontend_create') === 0)) {
                $type = self::TYPE_CACHE;
            } else {
                $type = self::TYPE_DEFAULT;
            }
        }
        return $type;
    }

    /**
     * Pull element from stack
     *
     * @param string $name
     * @param string $time
     * @param string $realMemory
     * @param string $emallocMemory
     * @return void
     */
    public static function stop($name, $time, $realMemory, $emallocMemory) : void
    {
        $currentPointer = end(self::$currentPointerStack);

        self::$stackLog[$currentPointer]['time_end'] = $time;
        self::$stackLog[$currentPointer]['realmem_end'] = $realMemory;
        self::$stackLog[$currentPointer]['emalloc_end'] = $emallocMemory;

        self::$stackLevel--;
        array_pop(self::$stack);
        array_pop(self::$currentPointerStack);
    }

    /**
     * Calculate relative data
     *
     * @return void
     */
    public static function calculate() : void
    {
        foreach (self::$stackLog as &$data) {
            foreach (array('time', 'realmem', 'emalloc') as $metric) {
                $data[$metric . '_end_relative'] = $data[$metric . '_end'] - self::$startValues[$metric];
                $data[$metric . '_start_relative'] = $data[$metric . '_start'] - self::$startValues[$metric];
                $data[$metric . '_total'] = $data[$metric . '_end_relative'] - $data[$metric . '_start_relative'];
            }
        }
    }

    /**
     * Set log stack trace
     *
     * @static
     * @param $logStackTrace
     */
    public static function setLogCallStack($logStackTrace) : void
    {
        self::$_logCallStack = $logStackTrace;
    }

    /**
     * Get totals
     *
     * @return array
     */
    public static function getTotals() : array
    {
        $totals = array();
        $lastLog = end(self::$stackLog);
        foreach (array('time', 'realmem', 'emalloc') as $metric) {
            $totals[$metric] = $lastLog[$metric . '_end'] - self::$startValues[$metric];
        }
        return $totals;
    }
}
