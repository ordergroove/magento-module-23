<?php

namespace Ordergroove\Subscription\Helper\PurchasePost\DataMapper;

use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Ordergroove\Subscription\Helper\ConfigHelper;

/**
 * Class DataMapHelper
 * @package Ordergroove\Subscription\Helper\PurchasePost\DataMapper
 */
class DataMapHelper
{

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * DataMapHelper constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(ConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getProducts($order)
    {
        $items = $order->getItems();
        $products = [];
        foreach ($items as $item) {
            $product = [];
            if ($item->getProductType() === 'simple') {
                $product['product'] = $item->getProductId();
                $product['sku'] = $item->getSku() ? $item->getSku() : $item->getProductId();
                $parentItem = $item->getParentItem();
                if ($parentItem['product_type'] !== 'bundle') {
                    //Set amounts using parent item if this is a configurable product , using item if no parent.
                    //The below subtraction accounts for catalog price rules which don't have a discount amount explicit in the database
                    $discountAmount = $parentItem ? $parentItem->getBaseOriginalPrice() - $parentItem->getBasePrice() :
                        $item->getBaseOriginalPrice() - $item->getBasePrice();

                    $qtyOrdered = $parentItem ? intval($parentItem->getQtyOrdered()) : intval($item->getQtyOrdered());
                    $price = $parentItem ? $parentItem->getBasePrice() : $item->getBasePrice();
                    $total = $qtyOrdered * $price;

                    $product['purchase_info'] = [
                        //base price and qty is set for children of configurable products so we don't have to worry about parent price.
                        'quantity' => $qtyOrdered,
                        'price' => $price,
                        'discounted_price' => $discountAmount,
                        'total' => $total
                    ];

                    $products[] = $product;
                }
            }

            if ($item->getProductType() === 'bundle') {
                $product['product'] = $item->getProductId();
                $product['sku'] = $item->getSku() ? $item->getSku() : $item->getProductId();
                $product['purchase_info'] = [
                    //base price and qty is set for children of configurable products so we don't have to worry about parent price.
                    'quantity' => intval($item->getQtyOrdered()),
                    'price' => $item->getBasePrice(),
                    'discounted_price' => $item->getBaseOriginalPrice() - $item->getBasePrice(),
                    'total' => intval($item->getQtyOrdered()) * $item->getBasePrice()
                ];
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return string
     */
    public function getExpiration($payment, $websiteId)
    {
        $ccExp = $payment->getCcExpMonth() . "/" . $payment->getCcExpYear();
        $ccExp = str_pad($ccExp, 32, "{");
        $encryptedCC = openssl_encrypt($ccExp, 'aes-256-ecb', $this->configHelper->getHashKey($websiteId), OPENSSL_ZERO_PADDING);

        return $encryptedCC;
    }

    /**
     * @param Address|OrderAddressInterface $address
     * @return array
     */
    public function getAddressMappedData($address)
    {
        $address = $address->explodeStreetAddress();
        $mappedAddress = [];
        $mappedAddress['last_name'] = $address->getLastName();
        $mappedAddress['first_name'] = $address->getFirstname();
        $mappedAddress['address'] = $address->getData('street1');
        $mappedAddress['address2'] = $address->getData('street2');
        $mappedAddress['city'] = $address->getCity();
        $mappedAddress['zip_postal_code'] = $address->getPostcode();
        $mappedAddress['state_province_code'] = $address->getRegionCode();
        $mappedAddress['country_code'] = $address->getCountryId();
        $mappedAddress['phone'] = $address->getTelephone();

        return array_filter($mappedAddress);
    }
}
