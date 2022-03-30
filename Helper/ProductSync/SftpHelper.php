<?php

namespace Ordergroove\Subscription\Helper\ProductSync;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Store\Model\ScopeInterface;
use Ordergroove\Subscription\Logger\ProductSync\Error\Logger as ErrorLogger;
use Ordergroove\Subscription\Logger\ProductSync\Info\Logger as InfoLogger;
use Ordergroove\Subscription\Model\Logging\OrdergrooveLoggingFactory;

/**
 * Class SftpHelper
 * @package Ordergroove\Subscription\Helper\ProductSync
 */
class SftpHelper
{
    /**
     * @var Sftp
     */
    private $sftp;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var OrdergrooveLoggingFactory
     */
    protected $ordergrooveLoggingFactory;

    /**
     * SftpHelper constructor.
     * @param Sftp $sftp
     * @param ScopeConfigInterface $scopeConfig
     */
    private $encryptor;
    /**
     * @var ErrorLogger
     */
    private $errorLogger;
    /**
     * @var InfoLogger
     */
    private $infoLogger;

    /**
     * SftpHelper constructor.
     * @param Sftp $sftp
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorLogger $errorLogger
     * @param InfoLogger $infoLogger
     * @param OrdergrooveLoggingFactory $ordergrooveLoggingFactory
     */
    public function __construct(
        Sftp $sftp,
        ScopeConfigInterface $scopeConfig,
        ErrorLogger $errorLogger,
        InfoLogger $infoLogger,
        OrdergrooveLoggingFactory $ordergrooveLoggingFactory
    ) {
        $this->sftp = $sftp;
        $this->scopeConfig = $scopeConfig;
        $this->errorLogger = $errorLogger;
        $this->infoLogger = $infoLogger;
        $this->ordergrooveLoggingFactory = $ordergrooveLoggingFactory;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param string|int $websiteId
     */
    public function sendProductFeed($xml, $websiteId)
    {
        $host = $this->scopeConfig
            ->getValue('ordergroove_subscription/sftp/host', ScopeInterface::SCOPE_WEBSITES, $websiteId);
        $port = $this->scopeConfig
            ->getValue('ordergroove_subscription/sftp/port', ScopeInterface::SCOPE_WEBSITES, $websiteId);
        $username = $this->scopeConfig
            ->getValue('ordergroove_subscription/sftp/username', ScopeInterface::SCOPE_WEBSITES, $websiteId);
        $password = $this->scopeConfig
            ->getValue('ordergroove_subscription/sftp/password', ScopeInterface::SCOPE_WEBSITES, $websiteId);
        $publicId = $this->scopeConfig
            ->getValue('ordergroove_subscription/general/public_id', ScopeInterface::SCOPE_WEBSITES, $websiteId);

        if ($username == null || $password == null) {
            return;
        }

        $fileName = $this->getSftpFilename($publicId);
        try {
            $this->sftp->open([
                "host" => $host,
                "port" => $port,
                "username" => $username,
                "password" => $password
            ]);
            $this->sftp->write($fileName, $xml->asXML());

            $this->infoLogger->info("Product Sync Complete for Public ID: " . $publicId);
        } catch (\Exception $e) {
            $this->errorLogger
                ->error('Error while establishing SFTP connection for Public ID: ' . $publicId, ['exception' => $e]);
            $ordergrooveLogging = $this->ordergrooveLoggingFactory->create();
            $ordergrooveLogging->addData([
                "log_date" => time(),
                "file_path" => $e->getFile() . " on Line " . $e->getLine(),
                "error_message" => $e->getMessage()
            ])->save();
        }
    }

    /**
     * @param string|int $publicId
     * @return string
     */
    public function getSftpFilename($publicId)
    {
        return "/incoming/" . $publicId . ".Products.xml";
    }
}
