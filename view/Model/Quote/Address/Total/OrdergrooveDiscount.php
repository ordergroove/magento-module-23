<?php

namespace Ordergroove\Subscription\Model\Quote\Address\Total;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

/**
 * Class OrdergrooveDiscount
 * @package Ordergroove\Subscription\Model\Quote\Address\Total
 */
class OrdergrooveDiscount extends AbstractTotal
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    protected $ordergrooveDiscountAmount = 0;

    /**
     * OrdergrooveDiscount constructor.
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this|OrdergrooveDiscount
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()->getAddress();

        $label = 'Ordergroove Discount';

        if ($address->getIsOrdergrooveDiscount()) {
            $ordergrooveDiscountAmount = $address->getOrdergrooveCustomOrderDiscount();
            $total->setDiscountDescription($label);
            $total->setDiscountAmount($ordergrooveDiscountAmount);
            $total->setBaseDiscountAmount($ordergrooveDiscountAmount);
            $total->setSubtotalWithDiscount($total->getSubtotal() + $ordergrooveDiscountAmount);
            $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $ordergrooveDiscountAmount);
            $total->setGrandTotal($total->getGrandTotal() - $ordergrooveDiscountAmount);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() - $ordergrooveDiscountAmount);
        }
        return $this;
    }
}
