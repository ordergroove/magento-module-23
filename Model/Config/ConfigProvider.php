<?php

namespace Ordergroove\Subscription\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Ordergroove\Subscription\Helper\ConfigHelper;

class ConfigProvider implements ConfigProviderInterface
{

    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    public function __construct(Session $checkoutSession, ConfigHelper $configHelper)
    {
        $this->checkoutSession = $checkoutSession;
        $this->configHelper = $configHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {

        if (!$this->configHelper->isEnabled()) {
            $additionalVariables['isOrdergrooveModuleEnabled'] = $this->configHelper->isEnabled();
            return $additionalVariables;
        }

        $quote = $this->checkoutSession->getQuote();
        $items = $quote->getItems();
        $productIds = [];
        foreach ($items as $item) {
            $simple = $item->getOptionByCode('simple_product');
            if ($simple) {
                $productIds[] = $simple->getProductId();
            } else {
                $productIds[] = $item->getProductId();
            }
        }
        $additionalVariables['ordergroove_product_ids'] = $productIds;
        $additionalVariables['isOrdergrooveModuleEnabled'] = $this->configHelper->isEnabled();
        return $additionalVariables;
    }
}
