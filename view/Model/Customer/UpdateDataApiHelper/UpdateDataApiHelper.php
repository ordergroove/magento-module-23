<?php

namespace Ordergroove\Subscription\Model\Customer\UpdateDataApiHelper;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Model\Config\UrlBuilder;
use Ordergroove\Subscription\Model\Signature\Signature;
use Ordergroove\Subscription\Logger\CustomerUpdates\Error\Logger as CustomerErrorLogger;
use Ordergroove\Subscription\Logger\CustomerUpdates\Info\Logger as CustomerInfoLogger;

/**
 * Class UpdateDataApiHelper
 * @package Ordergroove\Subscription\Model\Customer\UpdateDataApiHelper
 */
class UpdateDataApiHelper
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Signature
     */
    protected $signatureHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerErrorLogger
     */
    protected $customerErrorLogger;

    /**
     * @var CustomerInfoLogger
     */
    protected $customerInfoLogger;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;


    /**
     * UpdateDataApiHelper constructor.
     * @param ConfigHelper $configHelper
     * @param Signature $signatureHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressFactory $addressFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param Data $jsonHelper
     * @param UrlBuilder $urlBuilder
     * @param CustomerErrorLogger $customerErrorLogger
     * @param CustomerInfoLogger $customerInfoLogger
     */
    public function __construct(
        ConfigHelper $configHelper,
        Signature $signatureHelper,
        CustomerRepositoryInterface $customerRepository,
        AddressFactory $addressFactory,
        AddressRepositoryInterface $addressRepository,
        Data $jsonHelper,
        UrlBuilder $urlBuilder,
        CustomerErrorLogger $customerErrorLogger,
        CustomerInfoLogger $customerInfoLogger
    ) {
        $this->configHelper = $configHelper;
        $this->signatureHelper = $signatureHelper;
        $this->customerRepository = $customerRepository;
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->jsonHelper = $jsonHelper;
        $this->urlBuilder = $urlBuilder;
        $this->customerErrorLogger = $customerErrorLogger;
        $this->customerInfoLogger = $customerInfoLogger;
    }

    /**
     * @param $email
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareRequest($email)
    {
        $customer = $this->customerRepository->get($email);
        $customerId = $customer->getId();
        $publicId = $this->configHelper->getPublicId();
        $timeStamp = time();

        $signature = $this->signatureHelper->createSignature($customerId);
        if (empty($signature) || !$signature['signature']) {
            $this->customerErrorLogger->error("Signature could not be retrieved from given data");
        }

        $buildAuthorization = [
            'public_id' => $publicId,
            'ts' => $timeStamp,
            'sig_field' => $customerId,
            'sig' => $signature['signature']
        ];

        $jsonEncodedData = $this->jsonHelper->jsonEncode($buildAuthorization);
        $this->customerInfoLogger->info('Authorization Header is created for ' . $email . '. ' . $jsonEncodedData);

        $shippingAddressId = $customer->getDefaultShipping();
        if ($shippingAddressId) {
            $shippingAddress = $this->addressRepository->getById($shippingAddressId);
            $phoneNumber = $shippingAddress->getTelephone();
        } else {
            // Handle the issue of customers not being in ordergroove but the information updated in Magento
            $phoneNumber = "N/A";
        }

        $buildRequestBody = [
            'merchant' => $publicId,
            'merchant_user_id' => $customerId,
            'email' => $email,
            'phone_number' => $phoneNumber
        ];

        $this->customerInfoLogger->info('Request body created for above authorization header. ' . $this->jsonHelper->jsonEncode($buildRequestBody));

        $customerUpdateUrl = $this->urlBuilder->getCustomerUpdateUrl($customerId);
        $this->customerInfoLogger->info('Customer Update URL: ' . $customerUpdateUrl);

        return [
            'customerUpdateUrl' => $customerUpdateUrl,
            'requestBody' => $buildRequestBody,
            'authorizationHeader' => $buildAuthorization
        ];
    }
}
