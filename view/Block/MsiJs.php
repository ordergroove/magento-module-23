<?php

namespace Ordergroove\Subscription\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ordergroove\Subscription\Model\Config\UrlBuilder;

/**
 * Class MsiJs
 * @package Ordergroove\Subscription\Block
 */
class MsiJs extends Template
{
    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * MsiJs constructor.
     * @param Context $context
     * @param UrlBuilder $urlBuilder
     */
    public function __construct(Context $context, UrlBuilder $urlBuilder)
    {
        parent::__construct($context);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getMsiJsUrl()
    {
        return $this->urlBuilder->getPublicIdUrl("", "/msi.js");
    }
}
