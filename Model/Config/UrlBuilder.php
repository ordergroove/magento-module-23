<?php

namespace Ordergroove\Subscription\Model\Config;

use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Helper\UrlBuilderHelper;

class UrlBuilder
{
    const OG_STATIC_URL = "static.ordergroove.com";
    const OG_PURCHASE_POST_URL = "sc.ordergroove.com/subscription/create";
    const OG_CUSTOMER_UPDATE_URL = "restapi.ordergroove.com/customers/{customer_id}/set_contact_details";

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var UrlBuilderHelper
     */
    private $urlBuilderHelper;

    /**
     * UrlBuilder constructor.
     * @param ConfigHelper $configHelper
     * @param UrlBuilderHelper $urlBuilderHelper
     */
    public function __construct(ConfigHelper $configHelper, UrlBuilderHelper $urlBuilderHelper)
    {
        $this->configHelper = $configHelper;
        $this->urlBuilderHelper = $urlBuilderHelper;
    }

    /**
     * Returns a URL with the public ID and any append string appended to the end.
     * @param string $websiteId
     * @param string $append
     * @return string
     */
    public function getPublicIdUrl($websiteId = "", $append = "")
    {
        $url = $this->urlBuilderHelper->getUrlHeading($websiteId) . self::OG_STATIC_URL;
        $url .= "/";
        $url .= $this->configHelper->getPublicId($websiteId);
        return $url . $append;
    }

    /**
     * Returns a URL for Purchase Post
     * @param $websiteId
     * @return string
     */
    public function getPurchasePostUrl($websiteId = "")
    {
        return $this->urlBuilderHelper->getUrlHeading($websiteId) . self::OG_PURCHASE_POST_URL;
    }
  
    /**
     * Returns a URL for Customer Update
     * @param $customerId
     * @param string $websiteId
     * @return string
     */
    public function getCustomerUpdateUrl($customerId, $websiteId = "")
    {
        $url = $this->urlBuilderHelper->getUrlHeading($websiteId) . self::OG_CUSTOMER_UPDATE_URL;
        $url = str_replace("{customer_id}", $customerId, $url);
        return $url;
    }
}
