<?php

namespace Ordergroove\Subscription\Helper\PurchasePost\DataMapper;

use Magento\Sales\Model\Order;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Model\Config\TokenBuilder;

/**
 * Class DataMap
 * @package Ordergroove\Subscription\Helper\PurchasePost\DataMapper
 */
class DataMap
{
    const ORDERGROOVE_CARD_TYPE_MAP = [
        'VI' => 1,
        'MC' => 2,
        'AE' => 3,
        'DI' => 4,
        'JCB' => 6
    ];

    /**
     * @var TokenBuilder
     */
    private $tokenBuilder;
    /**
     * @var DataMapHelper
     */
    private $dataMapHelper;
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * DataMap constructor.
     * @param TokenBuilder $tokenBuilder
     * @param DataMapHelper $dataMapHelper
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        TokenBuilder $tokenBuilder,
        DataMapHelper $dataMapHelper,
        ConfigHelper $configHelper
    ) {
        $this->tokenBuilder = $tokenBuilder;
        $this->dataMapHelper = $dataMapHelper;
        $this->configHelper = $configHelper;
    }

    /**
     * @param Order $order
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function mapData($order)
    {
        $token = $this->tokenBuilder->buildToken($order);
        $map = [];
        $websiteId = $order->getStore()->getWebsiteId();

        $map['merchant_id'] = $this->configHelper->getPublicId($websiteId);
        $map['merchant_order_id'] = $order->getId();
        $map['user'] = [
            'user_id' => $order->getCustomerId(),
            'first_name' => $order->getCustomerFirstname(),
            'last_name' => $order->getCustomerLastname(),
            'email' => urlencode($order->getCustomerEmail()),
            'shipping_address' => $this->dataMapHelper->getAddressMappedData($order->getShippingAddress()),
            'billing_address' => $this->dataMapHelper->getAddressMappedData($order->getBillingAddress()),
            'phone_number' => $order->getBillingAddress()->getTelephone()
        ];

        $payment = $order->getPayment();
        $addinfo = $payment->getAdditionalInformation();

        if (array_key_exists("og_optins", $addinfo) && in_array($order->getPayment()->getMethod(), ConfigHelper::ALLOWED_GATEWAYS)) {
            $map['og_cart_tracking'] = false;
            $map['tracking'] = json_decode($addinfo['og_optins']);
        }

        $ccExp = $this->dataMapHelper->getExpiration($payment, $websiteId);

        if ($token && $ccExp) {
            $map['payment'] = [
                'token_id' => $token,
                'cc_exp_date' => urlencode($ccExp),
                'cc_type' => self::ORDERGROOVE_CARD_TYPE_MAP[$payment->getCcType()],
            ];
        }

        $allProducts = $this->dataMapHelper->getProducts($order);

        $map['products'] = $allProducts;

        return $map;
    }
}
