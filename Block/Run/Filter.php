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
use Magento\Backend\Block\Template\Context;

class Filter extends \Magento\Backend\Block\Template
{
    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @param Context $context
     * @param DataHelper $dataHelper
     * @param array $data
     */
    public function __construct(Context $context, DataHelper $dataHelper, array $data = [])
    {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }


    /**
     * @return DataHelper
     */
    public function getDataHelper()
    {
        return $this->dataHelper;
    }
}
