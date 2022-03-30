<?php

namespace Ordergroove\Subscription\Model\Cookie;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ordergroove\Subscription\Model\Signature\Signature;
use Ordergroove\Subscription\Logger\PurchasePost\Info\Logger as InfoLogger;
use Ordergroove\Subscription\Logger\PurchasePost\Error\Logger as ErrorLogger;

/**
 * Class OgAuthCookie
 * @package Ordergroove\Subscription\Model\Cookie
 */
class OgAuthCookie
{
    /**
     * Cookie name for Ordergroove
     */
    const COOKIE_NAME = 'og_auth';

    /**
     * Cookie life time
     */
    const COOKIE_LIFE = 7200;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Signature
     */
    protected $signature;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ErrorLogger
     */
    protected $errorLogger;

    /**
     * @var InfoLogger
     */
    protected $infoLogger;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * OgAuthCookie constructor.
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     * @param Signature $signature
     * @param Session $customerSession
     * @param ErrorLogger $errorLogger
     * @param InfoLogger $infoLogger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        Signature $signature,
        Session $customerSession,
        ErrorLogger $errorLogger,
        InfoLogger $infoLogger,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->signature = $signature;
        $this->customerSession = $customerSession;
        $this->errorLogger = $errorLogger;
        $this->infoLogger = $infoLogger;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $customerId
     * @return mixed
     * @throws NoSuchEntityException
     * @throws CookieSizeLimitReachedException
     */
    public function create($customerId)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $timestamp = time();

        $getSignature = $this->signature->createSignature($customerId, $websiteId, $timestamp);

        if (empty($getSignature) || !$getSignature['signature']) {
            $this->errorLogger->error("Signature could not be retrieved from given data");
        }

        $cookieValue = $customerId . "|" . $timestamp . "|" . $getSignature['signature'];

        try {
            $this->set($cookieValue, 7200);
        } catch (InputException $e) {
            $this->errorLogger->error("There is an error in setting cookie value " . $e->getMessage());
        } catch (FailureToSendException $e) {
            $this->errorLogger->error("There is an error in creating cookie value" . $e->getMessage());
        }

        return $cookieValue;
    }

    /**
     * Set data to cookie in remote address
     *
     * @param [string] $value    [value of cookie]
     * @param integer $duration [duration for cookie] 2 hours
     *
     * @return void
     * @throws FailureToSendException
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     */
    public function set($value, $duration = 7200)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());

        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $value, $metadata);
    }


    /**
     * @param $name
     * @return bool
     * @throws FailureToSendException
     * @throws InputException
     */
    public function delete($name)
    {
        if ($name !== self::COOKIE_NAME) {
            $this->errorLogger->error("Cannot delete cookie with name = $name");
        }

        $this->cookieManager->deleteCookie(
            $name,
            $this->cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );

        $this->infoLogger->info("Cookie $name has been deleted successfully.");
        return true;
    }

     /**
     * @param $name
     * @return string|null
     */
    public function get($name)
    {
        return $this->cookieManager->getCookie($name);
    }
}
