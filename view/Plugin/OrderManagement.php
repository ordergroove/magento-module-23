<?php

namespace Ordergroove\Subscription\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Ordergroove\Subscription\Model\OrderAttribute\AttributeFactory;

/**
 * Class OrderManagement
 */
class OrderManagement
{
    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * OrderManagement constructor.
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        AttributeFactory $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $order
    ) {
        $orderId = $order->getIncrementId();
        if ($orderId) {
            $payment = $order->getPayment();
            $additionalInfo = $payment->getAdditionalInformation();
            $ordergrooveOrderTypeValue = array_key_exists("og_optins", $additionalInfo) ? "Ordergroove Trigger Order" : "";
            $ordergrooveOrderType = $this->attributeFactory->create();
            $collection = $ordergrooveOrderType->getCollection()->addFieldToFilter('order_id', ['eq' => $order->getEntityId()])->getFirstItem();
            if (!$collection->getEntityId()) {
                $ordergrooveOrderType->setOrderId($order->getEntityId());
                $ordergrooveOrderType->setOrdergrooveOrderType($ordergrooveOrderTypeValue);
                $ordergrooveOrderType->save();
            }
            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes->setOrdergrooveOrderType($ordergrooveOrderTypeValue);
            $order->setExtensionAttributes($extensionAttributes);
        }
        return $order;
    }
}
