<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Block\Msi;

use Magento\Framework\View\Element\Template\Context;

/**
 * Class Index
 * @package Ordergroove\Subscription\Block\Msi
 */
class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }
}

