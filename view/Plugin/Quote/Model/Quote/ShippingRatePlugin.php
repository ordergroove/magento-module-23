<?php

namespace Ordergroove\Subscription\Plugin\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Address;

class ShippingRatePlugin
{

    public function beforeAddShippingRate(Address $subject, $rate)
    {
        if ($subject->getIsOrdergrooveShipping()) {
            $rate->setPrice($subject->getOrdergrooveShippingAmount());
        }

        return [$rate];
    }

}
