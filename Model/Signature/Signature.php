<?php

namespace Ordergroove\Subscription\Model\Signature;

use Magento\Framework\Encryption\Encryptor;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Model\Logging\OrdergrooveLoggingFactory;

/**
 * Class Signature
 * @package Ordergroove\Subscription\Model\Signature
 */
class Signature
{

    /**
     * @var Encryptor
     */
    private $encryptor;
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var OrdergrooveLoggingFactory
     */
    protected $ordergrooveLoggingFactory;

    /**
     * Signature constructor.
     * @param Encryptor $encryptor
     * @param ConfigHelper $configHelper
     * @param OrdergrooveLoggingFactory $ordergrooveLoggingFactory
     * @return void
     */
    public function __construct(
        Encryptor $encryptor,
        ConfigHelper $configHelper,
        OrdergrooveLoggingFactory $ordergrooveLoggingFactory
    ) {
        $this->encryptor = $encryptor;
        $this->configHelper = $configHelper;
        $this->ordergrooveLoggingFactory = $ordergrooveLoggingFactory;
    }

    /**
     * @param $field
     * @param string $websiteId
     * @param string $timestamp
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createSignature($field, $websiteId = null, $timestamp = null)
    {
        if (!$timestamp) {
            $timestamp = time();
        }

        $hashField = $field . "|" . $timestamp;

        $hashKey = $this->configHelper->getHashKey($websiteId);

        try {
            $this->encryptor->validateKey($hashKey);
        } catch (\Exception $error) {
            $ordergrooveLogging = $this->ordergrooveLoggingFactory->create();
            $ordergrooveLogging->addData([
                "log_date" => time(),
                "file_path" => $error->getFile() . " on Line " . $error->getLine(),
                "error_message" => $error->getMessage()
            ])->save();
            throw new \Exception(
                (string) new \Magento\Framework\Phrase(
                    'Error with Ordergroove Hash Key: ' . $error->getMessage()
                )
            );
        }

        $this->encryptor->setNewKey($hashKey);

        $hmac = $this->encryptor->hash($hashField);

        $binaryHmac = hex2bin($hmac);
        $base64Hmac = base64_encode($binaryHmac);

        return [
            'signature' => $base64Hmac,
            'timestamp' => $timestamp,
            'field' => $field,
        ];
    }
}
