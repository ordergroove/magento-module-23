<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Plugin\Account\Logout;

use Magento\Customer\Controller\Account\Logout;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Logger\PurchasePost\Info\Logger as InfoLogger;
use Ordergroove\Subscription\Logger\PurchasePost\Error\Logger as ErrorLogger;
use Ordergroove\Subscription\Model\Cookie\OgAuthCookie;
use Ordergroove\Subscription\Model\Logging\OrdergrooveLoggingFactory;

/**
 * Class DeleteCookie
 * @package Ordergroove\Subscription\Plugin\Account\Logout
 */
class DeleteCookie
{
    /**
     * Cookie name for Ordergroove
     */
    const COOKIE_NAME = 'og_auth';
    /**
     * @var OgAuthCookie
     */
    protected $cookie;
    /**
     * @var ErrorLogger
     */
    protected $errorLogger;
    /**
     * @var InfoLogger
     */
    protected $infoLogger;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var OrdergrooveLoggingFactory
     */
    protected $ordergrooveLoggingFactory;

    /**
     * DeleteCookie constructor.
     * @param ErrorLogger $errorLogger
     * @param InfoLogger $infoLogger
     * @param OgAuthCookie $cookie
     * @param Session $customerSession
     * @param ConfigHelper $configHelper
     * @param OrdergrooveLoggingFactory $ordergrooveLoggingFactory
     */
    public function __construct(
        ErrorLogger $errorLogger,
        InfoLogger $infoLogger,
        OgAuthCookie $cookie,
        Session $customerSession,
        ConfigHelper $configHelper,
        OrdergrooveLoggingFactory $ordergrooveLoggingFactory
    ) {
        $this->errorLogger = $errorLogger;
        $this->infoLogger = $infoLogger;
        $this->cookie = $cookie;
        $this->customerSession = $customerSession;
        $this->configHelper = $configHelper;
        $this->ordergrooveLoggingFactory = $ordergrooveLoggingFactory;
    }


    /**
     * @param Logout $subject
     * @return Logout
     * @throws FailureToSendException
     */
    public function beforeExecute(
        Logout $subject
    ) {
        if (!$this->configHelper->isEnabled()) {
            return $subject;
        }
        if ($this->customerSession->isLoggedIn()) {
            try {
                $deleteCookie = $this->cookie->delete(self::COOKIE_NAME);
                if ($deleteCookie) {
                    $this->infoLogger->info("Cookie " . self::COOKIE_NAME . " has been deleted successfully.");
                }
            } catch (InputException $e) {
                $this->errorLogger->error('The cookie ' . self::COOKIE_NAME . ' is not found');
                $ordergrooveLogging = $this->ordergrooveLoggingFactory->create();
                $ordergrooveLogging->addData([
                    "log_date" => time(),
                    "file_path" => $e->getFile() . " on Line " . $e->getLine(),
                    "error_message" => $e->getMessage()
                ])->save();
            }
        }
        return $subject;
    }
}
