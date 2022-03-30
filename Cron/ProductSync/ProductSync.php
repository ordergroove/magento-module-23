<?php

namespace Ordergroove\Subscription\Cron\ProductSync;

use \Ordergroove\Subscription\Helper\ProductSync\ProductSync as Sync;
use Ordergroove\Subscription\Logger\PurchasePost\Error\Logger as ErrorLogger;
use Ordergroove\Subscription\Model\Logging\OrdergrooveLoggingFactory;

/**
 * Class ProductSync
 * @package Ordergroove\Subscription\Cron\ProductSync
 */
class ProductSync
{

    /**
     * @var Sync
     */
    private $productSync;
    /**
     * @var ErrorLogger
     */
    private $errorLogger;

    /**
     * @var OrdergrooveLoggingFactory
     */
    protected $ordergrooveLoggingFactory;

    /**
     * ProductSync constructor.
     * @param Sync $productSync
     * @param ErrorLogger $errorLogger
     * @param OrdergrooveLoggingFactory $ordergrooveLoggingFactory
     */
    public function __construct(
        Sync $productSync,
        ErrorLogger $errorLogger,
        OrdergrooveLoggingFactory $ordergrooveLoggingFactory
    ) {
        $this->productSync = $productSync;
        $this->errorLogger = $errorLogger;
        $this->ordergrooveLoggingFactory = $ordergrooveLoggingFactory;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        try {
            $this->productSync->processProductSync();
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
