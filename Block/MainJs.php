<?php

namespace Ordergroove\Subscription\Block;

use Magento\Framework\View\Element\Template;
use Ordergroove\Subscription\Model\Config\UrlBuilder;

class MainJs extends Template
{
    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * MainJs constructor.
     * @param Template\Context $context
     * @param UrlBuilder $urlBuilder
     * @return void
     */
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, UrlBuilder $urlBuilder)
    {
        parent::__construct($context);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getMainJsUrl()
    {
        return $this->urlBuilder->getPublicIdUrl("", "/main.js");
    }
}
