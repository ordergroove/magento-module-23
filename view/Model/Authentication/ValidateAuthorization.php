<?php

namespace Ordergroove\Subscription\Model\Authentication;

use Magento\Framework\Exception\NoSuchEntityException;
use Ordergroove\Subscription\Model\Signature\Signature;

/**
 * Class ValidateAuthorization
 * @package Ordergroove\Subscription\Model\Authentication
 */
class ValidateAuthorization
{
    /**
     * @var Signature
     */
    protected $signature;

    /**
     * ValidateAuthorization constructor.
     * @param Signature $signature
     */
    public function __construct(
        Signature $signature
    ) {
        $this->signature = $signature;
    }

    /**
     * @param $sigField
     * @param $sig
     * @param $timestamp
     * @return bool
     * @throws NoSuchEntityException
     */
    public function validateAuthentication($sigField, $sig, $timestamp)
    {
        $validateSignature = $this->signature->createSignature($sigField, '', $timestamp);
        if (!($sig === $validateSignature['signature'])) {
            return false;
        }
        return true;
    }
}
