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
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    protected const XML_PATH_PROFILER_HIDE_LINES_FASTER_THAN = 'dev/profiler/hide_lines_faster_than';
    protected const XML_PATH_PROFILER_LOG_INVALID_NESTING = 'dev/profiler/log_invalid_nesting';
    protected const XML_PATH_PROFILER_REMOTE_CALL_URL_TEMPLATE = 'dev/profiler/remote_call_url_template';
    protected const XML_PATH_PROFILER_SCHEDULER_CRON_EXPR_PROFILER = 'dev/profiler/scheduler_cron_expr_profiler';
    protected const XML_PATH_PROFILER_KEEP_DAYS = 'dev/profiler/keep_days';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreInterface
     */
    protected $currentStore;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Context $context, StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    protected function getCurrentStore(): StoreInterface
    {
        if (!$this->currentStore) {
            $this->currentStore = $this->storeManager->getStore();
        }
        return $this->currentStore;
    }

    /**
     * Get "hide lines faster than" value
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getHideLinesFasterThan(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PROFILER_HIDE_LINES_FASTER_THAN,
            ScopeInterface::SCOPE_STORE,
            $this->getCurrentStore()->getCode()
        );
    }

    /**
     * Get "log invalid nesting" value
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getLogInvalidNesting(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PROFILER_LOG_INVALID_NESTING,
            ScopeInterface::SCOPE_STORE,
            $this->getCurrentStore()->getCode()
        );
    }

    /**
     * Get "remote call url template" value
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getRemoteCallUrlTemplate(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PROFILER_REMOTE_CALL_URL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $this->getCurrentStore()->getCode()
        );
    }

    /**
     * Get "scheduler cron expr profiler" value
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getSchedulerCronExprProfiler(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PROFILER_SCHEDULER_CRON_EXPR_PROFILER,
            ScopeInterface::SCOPE_STORE,
            $this->getCurrentStore()->getCode()
        );
    }

    /**
     * Get "keep days" value
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getKeepDays(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PROFILER_KEEP_DAYS,
            ScopeInterface::SCOPE_STORE,
            $this->getCurrentStore()->getCode()
        );
    }
}
