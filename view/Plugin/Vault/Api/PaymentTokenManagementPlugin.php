<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Plugin\Vault\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

/**
 * Class PaymentTokenManagementPlugin
 * @package Ordergroove\Subscription\Plugin\Vault\Api
 */
class PaymentTokenManagementPlugin
{
    /**
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param callable $proceed
     * @param PaymentTokenInterface $token
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    public function aroundSaveTokenWithPaymentLink(
        PaymentTokenManagementInterface $paymentTokenManagement,
        callable $proceed,
        PaymentTokenInterface $token,
        OrderPaymentInterface $payment
    ): bool {
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        // Guests should already be handled by public_hash, and is not relevant to us.
        if ($order->getCustomerIsGuest()) {
            return $proceed($token, $payment);
        }

        $existingToken = $paymentTokenManagement->getByGatewayToken(
            $token->getGatewayToken(),
            $payment->getMethodInstance()->getCode(),
            $order->getCustomerId()
        );

        // If we don't have a token for specified gateway token, fallback to public_hash logic.
        if ($existingToken === null) {
            return $proceed($token, $payment);
        }

        // Merge the token that is being saved with our existing token.
        $existingToken->addData($token->getData());
        return $proceed($existingToken, $payment);
    }
}
