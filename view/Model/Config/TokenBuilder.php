<?php

namespace Ordergroove\Subscription\Model\Config;

use Magento\Sales\Model\Order;
use Magento\Vault\Api\PaymentTokenManagementInterface;

class TokenBuilder
{
    const TOKEN_DELIMITER = "::";
    const WEBSITE_ID_PART = 0;
    const TOKEN_PART = 1;
    const PAYMENT_METHOD_PART = 2;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    public function __construct(PaymentTokenManagementInterface $paymentTokenManagement)
    {
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * Build payment token to be sent to Ordergroove for use in Recurring Orders
     * @param Order $order
     * @return string
     */
    public function buildToken($order)
    {
        $tokenId = $this->paymentTokenManagement->getByPaymentId($order->getPayment()->getEntityId());

        if (!$tokenId) {
            return "";
        }

        $tokenArray = [
            $order->getStore()->getWebsiteId(),
            $tokenId->getGatewayToken(),
            $order->getPayment()->getMethod()
        ];

        return implode(self::TOKEN_DELIMITER, $tokenArray);
    }

    /**
     * Destructures a payment token sent by Ordergroove during Recurring Order placement
     * @param string $token
     * @return array
     */
    public function splitToken($token)
    {
        $parts = explode(self::TOKEN_DELIMITER, $token, 3);
        return [
            'website_id' => $parts[self::WEBSITE_ID_PART],
            'token' => $parts[self::TOKEN_PART],
            'method' => $parts[self::PAYMENT_METHOD_PART],
        ];
    }
}
