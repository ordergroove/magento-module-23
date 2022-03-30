<?php

namespace Ordergroove\Subscription\Helper;

use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigHelper
{
    const ALLOWED_GATEWAYS = [ConfigProvider::CODE];
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ConfigHelper constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Returns Ordergroove Public Id of the websiteId argument.
     * If no websiteId is passed, uses the current store's website Id.
     * @param string $websiteId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPublicId($websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        return $this->scopeConfig
            ->getValue('ordergroove_subscription/general/public_id', ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }

    /**
     * Returns if to use staging with the websiteId argument.
     * If no websiteId is passed, uses the current store's website Id.
     * @param string $websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStaging($websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        return $this->scopeConfig
            ->isSetFlag('ordergroove_subscription/general/staging', ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }


    /**
     * Returns if the Ordergroove app is enabled for the websiteId argument.
     * If no websiteId is passed, uses the current store's website Id.
     * @param string $websiteId
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled($websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return $this->scopeConfig->isSetFlag('ordergroove_subscription/general/enable', ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }

    /**
     * Returns Ordergroove Hash Key of the websiteId argument.
     * If no websiteId is passed, uses the current store's website Id.
     * @param string $websiteId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHashKey($websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return $this->scopeConfig
            ->getValue('ordergroove_subscription/general/hash_key', ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }

}
