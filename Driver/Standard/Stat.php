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
    protected const TYPE_DEFAULT = 'default';
    protected const TYPE_DEFAULT_NOCHILDREN = 'default-nochildren';
    protected const TYPE_DATABASE = 'db';
    protected const TYPE_TEMPLATE = 'template';
    protected const TYPE_BLOCK = 'block';
    protected const TYPE_OBSERVER = 'observer';
    protected const TYPE_EVENT = 'event';
    protected const TYPE_MODEL = 'model';
    protected const TYPE_EAVMODEL = 'eavmodel';

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

        //if ($name === '__EAV_LOAD_MODEL__' && !empty(self::getConfiguration()->captureModelInfo)) {
        //    $trace = debug_backtrace();
        //    $className = get_class($trace[1]['args'][0]);
        //    $entityId = isset($trace[1]['args'][1]) ? $trace[1]['args'][1] : 'not set';
        //    $attributes = isset($trace[1]['args'][2]) ? $trace[1]['args'][2] : null;
        //    self::$stackLog[$currentPointer]['detail'] = "$className, id: $entityId, attributes: " . var_export($attributes, true);
        //}

        //if (!empty(self::getConfiguration()->captureBacktraces)) {
        //    $trace = isset($trace) ? $trace : debug_backtrace();
        //    $fileAndLine = self::getFileAndLine($trace, $type, $name);
        //    self::$stackLog[$currentPointer]['file'] = $fileAndLine['file'];
        //    self::$stackLog[$currentPointer]['line'] = $fileAndLine['line'];
        //}
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
            } elseif (strpos($label, 'DISPATCH EVENT:') === 0) {
                $type = self::TYPE_EVENT;
            } elseif (strpos($label, 'OBSERVER:') === 0) {
                $type = self::TYPE_OBSERVER;
            } elseif (strpos($label, 'BLOCK:') === 0) {
                $type = self::TYPE_BLOCK;
            } elseif (strpos($label, 'CORE::create_object_of::') === 0) {
                $type = self::TYPE_MODEL;
            } elseif (strpos($label, '__EAV_LOAD_MODEL__') === 0) {
                $type = self::TYPE_EAVMODEL;
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
        $currentName = end(self::$stack);
//        if ($currentName != $name) {
////            if (Mage::getStoreConfigFlag('dev/debug/logInvalidNesting')) {
////                Mage::log('[INVALID NESTING!] Found: ' . $name . " | Expecting: $currentName");
////            }
//
//            if (in_array($name, self::$stack)) {
//                // trying to stop something that has been started before,
//                // but there are other unstopped stack items
//                // -> auto-stop them
//                while (($latestStackItem = end(self::$stack)) != $name) {
////                    if (Mage::getStoreConfigFlag('dev/debug/logInvalidNesting')) {
////                        Mage::log('Auto-stopping timer "' . $latestStackItem . '" because of incorrect nesting');
////                    }
//                    self::stop($latestStackItem, $time, $realMemory, $emallocMemory);
//                }
//            } else {
//                // trying to stop something that hasn't been started before -> just ignore
//                return;
//            }
//
//            // We shouldn't add another name to the stack if we've already crawled up to the current one...
//             $name = '[INVALID NESTING!] ' . $name;
////             self::start($name);
////             return;
////             throw new Exception(sprintf("Invalid nesting! Expected: '%s', was: '%s'", $currentName, $name));
//        }

        $currentPointer = end(self::$currentPointerStack);

        self::$stackLog[$currentPointer]['time_end'] = $time;
        self::$stackLog[$currentPointer]['realmem_end'] = $realMemory;
        self::$stackLog[$currentPointer]['emalloc_end'] = $emallocMemory;

//        if (self::$_logCallStack !== false) {
//            self::$stackLog[$currentPointer]['callstack'] = Varien_Debug::backtrace(true, false);
//        }

        self::$stackLevel--;
        array_pop(self::$stack);
        array_pop(self::$currentPointerStack);
    }

    /**
     * Add data to the current stack
     *
     * @param $data
     * @param null $key
     */
//    public static function addData($data, $key = NULL)
//    {
//        $currentPointer = end(self::$currentPointerStack);
//        if (!isset(self::$stackLog[$currentPointer]['messages'])) {
//            self::$stackLog[$currentPointer]['messages'] = array();
//        }
//        if ($key === null) {
//            self::$stackLog[$currentPointer]['messages'][] = $data;
//        } else {
//            self::$stackLog[$currentPointer]['messages'][$key] = $data;
//        }
//    }

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
