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
}
