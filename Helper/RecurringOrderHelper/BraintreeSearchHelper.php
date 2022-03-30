<?php

namespace Ordergroove\Subscription\Helper\RecurringOrderHelper;

use Braintree\Configuration;
use Braintree\TransactionSearch;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\Exception\NoSuchEntityException;
use Ordergroove\Subscription\Exception\RecurringOrderException;
use Ordergroove\Subscription\Helper\ConfigHelper;

/**
 * Class BraintreeSearchInformation
 * @package Ordergroove\Subscription\Helper\RecurringOrderHelper
 */
class BraintreeSearchHelper
{
    /**
     * @var BraintreeAdapter
     */
    protected $braintreeAdapter;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * BraintreeSearchInformation constructor.
     * @param BraintreeAdapter $braintreeAdapter
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        BraintreeAdapter $braintreeAdapter,
        ConfigHelper $configHelper
    ) {
        $this->braintreeAdapter = $braintreeAdapter;
        $this->configHelper = $configHelper;
    }

    /**
     * @param $creditCardData
     * @return mixed
     * @throws NoSuchEntityException
     * @throws RecurringOrderException
     */
    public function checkCreditCardValidity($creditCardData)
    {
        $merchantConfig = Configuration::gateway()->transaction();
        $collections = $merchantConfig->search([
            TransactionSearch::paymentMethodToken()->is($creditCardData['orderTokenId']),
            TransactionSearch::customerEmail()->is($creditCardData['customerEmail'])
        ]);

        if (!$collections->maximumCount()) {
            throw new RecurringOrderException(__("Unable to fetch information. Invalid token provided"), null, "999");
        }

        $retrievedData = [
            'creditCard' => $collections->firstItem()->creditCard,
            'billing' => $collections->firstItem()->billing
        ];

        $decryptedCC = openssl_decrypt(
            $creditCardData['expirationDate'],
            'aes-256-ecb',
            $this->configHelper->getHashKey((int)$creditCardData['websiteId']),
            OPENSSL_ZERO_PADDING
        );

        $concatExpDate = $retrievedData['creditCard']['expirationMonth'] . '/' . $retrievedData['creditCard']['expirationYear'];
        if ($creditCardData['orderCcType'] !== $retrievedData['creditCard']['cardType']) {
            throw new RecurringOrderException(__("Invalid Credit Card Type"), null, "100");
        }

        if (trim($decryptedCC, '{') !== $concatExpDate) {
            throw new RecurringOrderException(__("Invalid Credit Card Expiration Date"), null, "120");
        }

        if (!isset($retrievedData['billing']['postalCode'])) {
            throw new RecurringOrderException(__("Invalid Billing Address"), null, "130");
        }

        return $retrievedData['billing'];
    }
}
