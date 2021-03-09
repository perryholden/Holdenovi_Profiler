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
use stdClass;

class Hierarchy extends Standard
{
    protected const FILENAME_CONFIG = 'holdenovi_profiler.xml';

    /**
     * Storage for timers statistics
     *
     * @var Stat
     */
    protected $_stat;

    // Config and enabled fields
    static protected $_configuration;
    static protected $_checkedEnabled = false;
    static protected $_enabled = false;

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

    /**
     * Start collecting statistics for specified timer
     *
     * @param string $timerId
     * @param array|null $tags
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function start($timerId, array $tags = null)
    {
        if (!self::isInternalEnabled()) {
            return;
        }
        Stat::start($timerId, microtime(true), memory_get_usage(true), memory_get_usage());
    }

    /**
     * Stop recording statistics for specified timer.
     *
     * @param string $timerId
     * @return void
     */
    public function stop($timerId)
    {
        if (!self::isInternalEnabled()) {
            return;
        }
        Stat::stop($timerId, microtime(true), memory_get_usage(true), memory_get_usage());
    }

    /**
     * Get configuration object
     *
     * @return stdClass
     */
    public static function getConfiguration() : stdClass
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
     * Check if profiler is enabled, and if not, then read the config, see if it ought to be enabled, then calculate start values
     *
     * @static
     * @return bool
     */
    public static function isInternalEnabled() : bool
    {
        if (!self::$_checkedEnabled) {
            self::$_checkedEnabled = true;

            $conf = self::getConfiguration();

            $shouldBeEnabled = false;
            if (strtolower($conf->trigger) === 'always') {
                $shouldBeEnabled = true;
            } elseif (strtolower($conf->trigger) === 'parameter') {
                if ((isset($_GET['profile']) && $_GET['profile'] == true) || (isset($_COOKIE['profile']) && $_COOKIE['profile'] == true)) {
                    $shouldBeEnabled = true;
                }
            }

            // Process filters
            if ($shouldBeEnabled && $conf->enableFilters) {

                // Sampling filter
                if ($shouldBeEnabled && rand(0,100000) > $conf->filters->sampling * 1000) {
                    $shouldBeEnabled = false;
                }

                // Request URI Whitelist/Blacklist
                $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''; // TODO: use script name instead for cli?
                if ($shouldBeEnabled && $conf->filters->requestUriWhiteList && !preg_match($conf->filters->requestUriWhiteList, $requestUri)) {
                    $shouldBeEnabled = false;
                }
                if ($shouldBeEnabled && $conf->filters->requestUriBlackList && preg_match($conf->filters->requestUriBlackList, $requestUri)) {
                    $shouldBeEnabled = false;
                }

                // NOTE: timeThreshold and memoryThreshold will be checked before persisting records. In these cases data will still be recorded during the request.
            }

            if ($shouldBeEnabled) {

                // 1. Calculate the starting values
                Stat::$startValues = [
                    'time' => microtime(true),
                    'realmem' => memory_get_usage(true),
                    'emalloc' => memory_get_usage(false),
                ];

                // 2. Set this as actually enabled
                self::$_enabled = true;

                // 3. Create a container for the various stacks (including the cache ones)
                \Magento\Framework\Profiler::start('container');

            }
        }
        return self::$_enabled;
    }

    /**
     * Disabling profiler
     *
     * @return void
     * @throws \Exception
     */
    public static function disable() : void
    {

        if (self::isInternalEnabled()) {
            $stackCopy = Stat::$stack;
            // Grab first item from the stack and stop it
            while ($timerName = array_pop($stackCopy)) {
                Stat::stop($timerName, microtime(true), memory_get_usage(true), memory_get_usage());
            }
        }
        self::$_enabled = false;

        Stat::calculate();
    }

    /**
     * This determines if the filtering thresholds have been reached
     *
     * @return bool
     */
    public static function checkThresholds() : bool
    {
        $conf = self::getConfiguration();
        $totals = Stat::getTotals();

        // TODO: Refactor return statement
        return empty($conf->enableFilters) || (!$conf->filters->timeThreshold || $totals['time'] > $conf->filters->timeThreshold) &&
            (!$conf->filters->memoryThreshold || $totals['realmem'] > $conf->filters->memoryThreshold);
    }

    /**
     * Get raw stack log data
     *
     * @return array
     * @throws \Exception
     */
    public static function getStackLog() : array
    {
        if (self::isInternalEnabled()) {
            throw new \Exception('Disable profiler first!');
        }
        return Stat::$stackLog;
    }
}
