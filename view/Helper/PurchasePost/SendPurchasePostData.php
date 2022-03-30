<?php

namespace Ordergroove\Subscription\Helper\PurchasePost;

use Magento\Framework\HTTP\ZendClientFactory;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Logger\PurchasePost\Info\Logger as InfoLogger;
use Ordergroove\Subscription\Logger\PurchasePost\Error\Logger as ErrorLogger;
use Ordergroove\Subscription\Model\Config\UrlBuilder;
use Ordergroove\Subscription\Model\Signature\Signature;
use Ordergroove\Subscription\Model\Logging\OrdergrooveLoggingFactory;

/**
 * Class SendPurchasePostData
 * @package Ordergroove\Subscription\Helper\PurchasePost
 */
class SendPurchasePostData
{
    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;
    /**
     * @var UrlBuilder
     */
    private $urlBuilder;
    /**
     * @var Signature
     */
    private $signature;
    /**
     * @var ConfigHelper
     */
    private $configHelper;
    /**
     * @var ErrorLogger
     */
    private $errorLogger;
    /**
     * @var InfoLogger
     */
    private $infoLogger;

    /**
     * @var OrdergrooveLoggingFactory
     */
    protected $ordergrooveLoggingFactory;

    /**
     * SendPurchasePostData constructor.
     * @param ZendClientFactory $httpClientFactory
     * @param UrlBuilder $urlBuilder
     * @param Signature $signature
     * @param ConfigHelper $configHelper
     * @param ErrorLogger $errorLogger
     * @param InfoLogger $infoLogger
     * @param OrdergrooveLoggingFactory $ordergrooveLoggingFactory
     */
    public function __construct(
        ZendClientFactory $httpClientFactory,
        UrlBuilder $urlBuilder,
        Signature $signature,
        ConfigHelper $configHelper,
        ErrorLogger $errorLogger,
        InfoLogger $infoLogger,
        OrdergrooveLoggingFactory $ordergrooveLoggingFactory
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->urlBuilder = $urlBuilder;
        $this->signature = $signature;
        $this->configHelper = $configHelper;
        $this->errorLogger = $errorLogger;
        $this->infoLogger = $infoLogger;
        $this->ordergrooveLoggingFactory = $ordergrooveLoggingFactory;
    }

    /**
     * @param $datamap
     * @param $websiteId
     * @return \Zend_Http_Response
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function sendPurchasePostToOrdergroove($datamap, $websiteId)
    {
        try {
            $client = $this->httpClientFactory->create();
            $client->setUri($this->urlBuilder->getPurchasePostUrl($websiteId));
            $client->setMethod(\Zend_Http_Client::POST);
            $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'application/json');
            $client->setHeaders('Accept', 'application/json');
            $sigData = $this->signature->createSignature($datamap['user']['user_id'], $websiteId);
            $postHeader = [
                'public_id' => $this->configHelper->getPublicId($websiteId),
                'ts' => $sigData['timestamp'],
                'sig_field' => $sigData['field'],
                'sig' => $sigData['signature']
            ];
            $client->setHeaders('authorization', json_encode($postHeader));
            $sendPostData = 'create_request=' . json_encode($datamap);
            $client->setRawData($sendPostData);
            $response = $client->request();
            if ($response->getMessage() === "BAD REQUEST") {
                //Don't error out so other orders can process but log this error.
                $this->errorLogger->error($response->getBody());
            }
            return $response;
        } catch (\Exception $error) {
            $this->errorLogger->error($error);
            $ordergrooveLogging = $this->ordergrooveLoggingFactory->create();
            $ordergrooveLogging->addData([
                "log_date" => time(),
                "file_path" => $error->getFile() . " on Line " . $error->getLine(),
                "error_message" => $error->getMessage()
            ])->save();
            throw $error;
        }
    }
}
