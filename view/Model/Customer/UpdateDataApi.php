<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Model\Customer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Json\Helper\Data;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Model\Config\UrlBuilder;
use Ordergroove\Subscription\Model\Signature\Signature;
use Ordergroove\Subscription\Logger\CustomerUpdates\Error\Logger as CustomerErrorLogger;
use Ordergroove\Subscription\Logger\CustomerUpdates\Info\Logger as CustomerInfoLogger;
use Ordergroove\Subscription\Model\Customer\UpdateDataApiHelper\UpdateDataApiHelper;
use Ordergroove\Subscription\Model\Logging\OrdergrooveLoggingFactory;


/**
 * Class UpdateDataApi
 * @package Ordergroove\Subscription\Model\Customer
 */
class UpdateDataApi
{
    /**
     * @var Curl
     */
    protected $curl;
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
     * @var UpdateDataApiHelper
     */
    protected $updateDataApiHelper;

    /**
     * @var OrdergrooveLoggingFactory
     */
    protected $ordergrooveLoggingFactory;


    /**
     * UpdateDataApi constructor.
     * @param Curl $curl
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerErrorLogger $customerErrorLogger
     * @param CustomerInfoLogger $customerInfoLogger
     * @param AddressFactory $addressFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param Data $jsonHelper
     * @param UpdateDataApiHelper $updateDataApiHelper
     * @param OrdergrooveLoggingFactory $ordergrooveLoggingFactory
     */
    public function __construct(
        Curl $curl,
        CustomerRepositoryInterface $customerRepository,
        CustomerErrorLogger $customerErrorLogger,
        CustomerInfoLogger $customerInfoLogger,
        AddressFactory $addressFactory,
        AddressRepositoryInterface $addressRepository,
        Data $jsonHelper,
        UpdateDataApiHelper $updateDataApiHelper,
        OrdergrooveLoggingFactory $ordergrooveLoggingFactory
    )
    {
        $this->curl = $curl;
        $this->customerRepository = $customerRepository;
        $this->customerErrorLogger = $customerErrorLogger;
        $this->customerInfoLogger = $customerInfoLogger;
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->jsonHelper = $jsonHelper;
        $this->updateDataApiHelper = $updateDataApiHelper;
        $this->ordergrooveLoggingFactory = $ordergrooveLoggingFactory;
    }

    /**
     * @param $email
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function executeUpdateRequest($email)
    {
        $params = $this->updateDataApiHelper->prepareRequest($email);

        $url = $params['customerUpdateUrl'];
        $body = $params['requestBody'];
        $authorization = $params['authorizationHeader'];
        $formattedAuthorizationHeader = json_encode($authorization, JSON_UNESCAPED_SLASHES);
        $this->customerInfoLogger->info(
            'Formatted Authorization header : ' .
            print_r($formattedAuthorizationHeader, true)
        );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            "Authorization: $formattedAuthorizationHeader",
            "Content-Type: application/json"
        ];

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $patchFields = json_encode($body);
        $this->customerErrorLogger->info('Formatted body: ' . $patchFields);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $patchFields);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!($httpCode == '200')) {
            $this->customerErrorLogger->error("Response. " . print_r($response, true));
            $ordergrooveLogging = $this->ordergrooveLoggingFactory->create();
            $ordergrooveLogging->addData([
                "log_date" => time(),
                "file_path" => __FILE__,
                "error_message" => $response
            ])->save();
            throw new LocalizedException(__("Cannot update customer information because we received invalid response. Please make sure that the customer exists in ordergroove."));
        }
        $this->customerInfoLogger->info("Response. " . print_r($response, true));
    }
}
