<?php

namespace Ordergroove\Subscription\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Controller\Account\LoginPost;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Logger\PurchasePost\Info\Logger as InfoLogger;
use Ordergroove\Subscription\Logger\PurchasePost\Error\Logger as ErrorLogger;
use Ordergroove\Subscription\Model\Cookie\OgAuthCookie;
use Ordergroove\Subscription\Model\Logging\OrdergrooveLoggingFactory;


class SetOGCookieOnPageLoad implements ObserverInterface
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
     * CreateCookie constructor.
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

    public function execute(Observer $observer)
    {
        if (!$this->configHelper->isEnabled()) {
            return;
        }
        if ($this->customerSession->isLoggedIn() && !$this->cookie->get(self::COOKIE_NAME)) {
            $customerId = $this->customerSession->getCustomer()->getId();
            $ordergrooveLogging = $this->ordergrooveLoggingFactory->create();
            try {
                $setCookie = $this->cookie->create($customerId);
                if (!empty($setCookie)) {
                    $this->infoLogger->info("Cookie has been created successfully for customerID $customerId with cookie value $setCookie");
                }
            } catch (NoSuchEntityException $e) {
                $this->infoLogger->info("There is an issue with creating and setting a cookie.");
                $this->infoLogger->info($e->getMessage());
                $ordergrooveLogging->addData([
                    "log_date" => time(),
                    "file_path" => $e->getFile() . " on Line " . $e->getLine(),
                    "error_message" => $e->getMessage()
                ])->save();
            } catch (CookieSizeLimitReachedException $e) {
                $this->infoLogger->info("Unable to send the cookie.");
                $this->infoLogger->info($e->getMessage());
                $ordergrooveLogging->addData([
                    "log_date" => time(),
                    "file_path" => $e->getFile() . " on Line " . $e->getLine(),
                    "error_message" => $e->getMessage()
                ])->save();
            }
        } else {
            $this->errorLogger->error("Cannot set cookie for not logged in customer.");
        }
    }

}
