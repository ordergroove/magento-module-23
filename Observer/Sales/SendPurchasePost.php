<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Helper\PurchasePost\DataMapper\DataMap;
use Ordergroove\Subscription\Helper\PurchasePost\SendPurchasePostData;

/**
 * Class SendPurchasePost
 * @package Ordergroove\Subscription\Observer\Sales
 */
class SendPurchasePost implements ObserverInterface
{
    /**
     * @var DataMap
     */
    protected $dataMap;

    /**
     * @var SendPurchasePostData
     */
    protected $sendPurchasePostData;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * SendPurchasePost constructor.
     * @param DataMap $dataMap
     * @param SendPurchasePostData $sendPurchasePostData
     * @param OrderFactory $orderFactory
     * @param ConfigHelper $configHelper
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        DataMap $dataMap,
        SendPurchasePostData $sendPurchasePostData,
        OrderFactory $orderFactory,
        ConfigHelper $configHelper,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    ) {
        $this->dataMap = $dataMap;
        $this->sendPurchasePostData = $sendPurchasePostData;
        $this->orderFactory = $orderFactory;
        $this->configHelper = $configHelper;
        $this->_cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function execute(
        Observer $observer
    ) {
        if (!$this->configHelper->isEnabled()) {
            return;
        }
        $orderData = $observer->getEvent()->getOrder();
        $orderId = $orderData->getId();
        if (!$orderId) {
            return $this;
        }

        $order = $this->orderFactory->create()->load($orderId);

        $allItems = $order->getItems();
        foreach ($allItems as $item) {
            $productId = $item->getProductId();
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

        $dataMap = $this->dataMap->mapData($order);
        $this->sendPurchasePostData->sendPurchasePostToOrdergroove($dataMap, $order->getStore()->getWebsiteId());
    }
}
