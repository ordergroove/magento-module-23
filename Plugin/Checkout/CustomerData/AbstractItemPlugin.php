<?php

namespace Ordergroove\Subscription\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\AbstractItem;
use Ordergroove\Subscription\Helper\ConfigHelper;

class AbstractItemPlugin
{
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * AbstractItemPlugin constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(ConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    public function afterGetItemData(AbstractItem $subject, $result, $item)
    {
        if (!$this->configHelper->isEnabled()) {
            //Return if module is disabled
            return $result;
        }

        if ($item->getProductType() == "configurable") {
            $product = $item->getOptionByCode('simple_product');
            $result = array_merge($result, ['simple_product_id' => $product->getProductId()]);
        }

        return $result;
    }
}
