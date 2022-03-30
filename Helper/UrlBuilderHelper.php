<?php

namespace Ordergroove\Subscription\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;

class UrlBuilderHelper
{

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * UrlBuilderHelper constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(ConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * Returns back "https://" and "staging." if the staging setting is enabled on the Ordergroove app.
     * @param string $website
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlHeading($website = "")
    {
        $url = "https://";
        if ($this->configHelper->getStaging($website)) {
            $url .= "staging.";
        }
        return $url;
    }
}
