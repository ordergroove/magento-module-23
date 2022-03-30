<?php

namespace Ordergroove\Subscription\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Ordergroove\Subscription\Helper\ConfigHelper;

/**
 * Class QuoteRemoveItem
 * @package Ordergroove\Subscription\Observer\Sales
 */
class QuoteRemoveItem implements ObserverInterface
{
    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * QuoteRemoveItem constructor.
     * @param CookieManagerInterface $cookieManager
     * @param ConfigHelper $configHelper
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        ConfigHelper $configHelper,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    ) {
        $this->_cookieManager = $cookieManager;
        $this->configHelper = $configHelper;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function execute(
        Observer $observer
    ) {
        if ($this->configHelper->isEnabled()) {
            $quoteItem = $observer->getQuoteItem();
            $product = $quoteItem->getProduct();
            $productId = $product->getId();
            if ($this->_cookieManager->getCookie("product_subscribed_" . $productId)) {
                $this->_cookieManager->deleteCookie(
                    "product_subscribed_" . $productId,
                    $this->cookieMetadataFactory
                        ->createCookieMetadata()
                        ->setPath($this->sessionManager->getCookiePath())
                        ->setDomain($this->sessionManager->getCookieDomain())
                );
            }
        }
    }
}
