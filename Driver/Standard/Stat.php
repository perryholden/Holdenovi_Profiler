<?php
/**
 * @category    Holdenovi
 * @package     Profiler
 * @copyright   Copyright (c) 2020 Holdenovi LLC
 * @license     GPL-3.0 (see COPYING for details)
 */
declare(strict_types=1);

namespace Holdenovi\Profiler\Driver\Standard;

use stdClass;

class Stat
{
    protected const FILENAME_CONFIG = 'holdenovi_profiler.xml';

    protected const TYPE_DEFAULT = 'default';
    protected const TYPE_DEFAULT_NOCHILDREN = 'default-nochildren';
    protected const TYPE_DATABASE = 'db';
    protected const TYPE_TEMPLATE = 'template';
    protected const TYPE_BLOCK = 'block';
    protected const TYPE_OBSERVER = 'observer';
    protected const TYPE_EVENT = 'event';
    protected const TYPE_MODEL = 'model';
    protected const TYPE_EAVMODEL = 'eavmodel';

    static protected  $startValues = array();

    static protected  $stackLevel = 0;
    static protected  $stack = array();
    static protected  $stackLevelMax = array();
    static protected  $stackLog = array();
    static protected  $uniqueCounter = 0;
    static protected  $currentPointerStack = array();

    static protected  $_enabled = false;
    static protected  $_checkedEnabled = false;

    static protected  $_logCallStack = false;

    static protected  $_configuration;
    static protected $_timers = [];

    /**
     * Get configuration object
     *
     * @return stdClass
     */
    public static function getConfiguration()
    {
        if (!self::$_configuration) {
            self::$_configuration = new stdClass();
            self::$_configuration->trigger = 'never';
            self::$_configuration->filters = new stdClass();

            $file = BP . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . self::FILENAME_CONFIG;

            if (is_file($file) && ($conf = simplexml_load_file($file))) {
                self::$_configuration->trigger = (string)$conf->holdenovi_profiler->trigger;
                self::$_configuration->captureModelInfo = (bool)(string)$conf->holdenovi_profiler->captureModelInfo;
                self::$_configuration->captureBacktraces = (bool)(string)$conf->holdenovi_profiler->captureBacktraces;
                self::$_configuration->enableFilters = (bool)(string)$conf->holdenovi_profiler->enableFilters;
                if (self::$_configuration->enableFilters) {
                    self::$_configuration->filters->sampling = (float)$conf->holdenovi_profiler->filters->sampling;
                    self::$_configuration->filters->timeThreshold = (int)$conf->holdenovi_profiler->filters->timeThreshold;
                    self::$_configuration->filters->memoryThreshold = (int)$conf->holdenovi_profiler->filters->memoryThreshold;
                    self::$_configuration->filters->requestUriWhiteList = (string)$conf->holdenovi_profiler->filters->requestUriWhiteList;
                    self::$_configuration->filters->requestUriBlackList = (string)$conf->holdenovi_profiler->filters->requestUriBlackList;
                }
            }
        }
        return self::$_configuration;
    }

    /**
     * Check if profiler is enabled.
     *
     * @static
     * @return bool
     */
    public static function isEnabled()
    {
        if (!self::$_checkedEnabled) {
            self::$_checkedEnabled = true;

            $conf = self::getConfiguration();

            $enabled = false;
            if (strtolower($conf->trigger) === 'always') {
                $enabled = true;
            } elseif (strtolower($conf->trigger) === 'parameter') {
                if ((isset($_GET['profile']) && $_GET['profile'] == true) || (isset($_COOKIE['profile']) && $_COOKIE['profile'] == true)) {
                    $enabled = true;
                }
            }

            // Process filters
            if ($enabled && $conf->enableFilters) {

                // sampling filter
                if ($enabled && rand(0,100000) > $conf->filters->sampling * 1000) {
                    $enabled = false;
                }

                // request uri whitelist/blacklist
                $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''; // TODO: use script name instead for cli?
                if ($enabled && $conf->filters->requestUriWhiteList && !preg_match($conf->filters->requestUriWhiteList, $requestUri)) {
                    $enabled = false;
                }
                if ($enabled && $conf->filters->requestUriBlackList && preg_match($conf->filters->requestUriBlackList, $requestUri)) {
                    $enabled = false;
                }

                // note: timeThreshold and memoryThreshold will be checked before persisting records. In these cases data will still be recorded during the request
            }

            if ($enabled) {
                self::enable();
            }
        }
        return self::$_enabled;
    }

    /**
     * Pushes to the stack
     *
     * @param string $name
     * @param string $type
     * @return void
     */
    public static function start($name, $type = '')
    {
        if (!self::isEnabled()) {
            return;
        }

        $currentPointer = 'timetracker_' . self::$uniqueCounter++;
        self::$currentPointerStack[] = $currentPointer;
        self::$stack[] = $name;

        self::$stackLevel++;
        self::$stackLevelMax[] = self::$stackLevel;

        self::$stackLog[$currentPointer] = array(
            'level' => self::$stackLevel,
            'stack' => self::$stack,
            'time_start' => microtime(true),
            'realmem_start' => memory_get_usage(true),
            'emalloc_start' => memory_get_usage(false),
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
     * Pull element from stack
     *
     * @param string $name
     * @throws Exception
     * @return void
     */
    public static function stop($name)
    {
        if (!self::isEnabled()) {
            return;
        }

//        $currentName = end(self::$stack);
//        if ($currentName != $name) {
//            if (Mage::getStoreConfigFlag('dev/debug/logInvalidNesting')) {
//                Mage::log('[INVALID NESTING!] Found: ' . $name . " | Expecting: $currentName");
//            }
//
//            if (in_array($name, self::$stack)) {
//                // trying to stop something that has been started before,
//                // but there are other unstopped stack items
//                // -> auto-stop them
//                while (($latestStackItem = end(self::$stack)) != $name) {
//                    if (Mage::getStoreConfigFlag('dev/debug/logInvalidNesting')) {
//                        Mage::log('Auto-stopping timer "' . $latestStackItem . '" because of incorrect nesting');
//                    }
//                    self::stop($latestStackItem);
//                }
//            } else {
//                // trying to stop something that hasn't been started before -> just ignore
//                return;
//            }
//
//            // We shouldn't add another name to the stack if we've already crawled up to the current one...
//            // $name = '[INVALID NESTING!] ' . $name;
//            // self::start($name);
//            // return;
//            // throw new Exception(sprintf("Invalid nesting! Expected: '%s', was: '%s'", $currentName, $name));
//        }

        $currentPointer = end(self::$currentPointerStack);

        self::$stackLog[$currentPointer]['time_end'] = microtime(true);
        self::$stackLog[$currentPointer]['realmem_end'] = memory_get_usage(true);
        self::$stackLog[$currentPointer]['emalloc_end'] = memory_get_usage(false);

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
     * Enabling profiler
     *
     * @return void
     */
    public static function enable()
    {
        self::$startValues = array(
            'time' => microtime(true),
            'realmem' => memory_get_usage(true),
            'emalloc' => memory_get_usage(false)
        );
        self::$_enabled = true;
    }

    /**
     * Disabling profiler
     *
     * @return void
     */
    public static function disable()
    {

        if (self::isEnabled()) {
            // stop any timers still on stack (those might be stopped after calculation otherwise)
            $stackCopy = self::$stack;
            while ($timerName = array_pop($stackCopy)) {
                self::stop($timerName);
            }
        }
        self::$_enabled = false;

        self::calculate();
    }

    /**
     * Get raw stack log data
     *
     * @return array
     * @throws \Exception
     */
    public static function getStackLog()
    {
        if (self::isEnabled()) {
            throw new \Exception('Disable profiler first!');
        }
        return self::$stackLog;
    }

    /**
     * Set log stack trace
     *
     * @static
     * @param $logStackTrace
     */
    public static function setLogCallStack($logStackTrace)
    {
        self::$_logCallStack = $logStackTrace;
    }

    /**
     * Calculate relative data
     *
     * @return void
     */
    public static function calculate()
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
     * This determines if the filtering thresholds have been reached
     *
     * @return bool
     */
    public static function checkThresholds()
    {
        $conf = self::getConfiguration();
        $totals = self::getTotals();

        // TODO: Refactor return statement
        return empty($conf->enableFilters) || (!$conf->filters->timeThreshold || $totals['time'] > $conf->filters->timeThreshold) &&
            (!$conf->filters->memoryThreshold || $totals['realmem'] > $conf->filters->memoryThreshold);
    }

    /**
     * Get totals
     *
     * @return array
     */
    public static function getTotals()
    {
        $totals = array();
        $lastLog = end(self::$stackLog);
        foreach (array('time', 'realmem', 'emalloc') as $metric) {
            $totals[$metric] = $lastLog[$metric . '_end'] - self::$startValues[$metric];
        }
        return $totals;
    }
}
