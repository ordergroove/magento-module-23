<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Test\Unit\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Helper\PurchasePost\DataMapper\DataMap;
use Ordergroove\Subscription\Helper\PurchasePost\SendPurchasePostData;
use Ordergroove\Subscription\Observer\Sales\SendPurchasePost;
use PHPUnit\Framework\TestCase;

/**
 * Class SendPurchasePostTest
 * @package Ordergroove\Subscription\Test\Unit\Observer\Sales
 */
class SendPurchasePostTest extends Testcase
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
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var SendPurchasePost
     */
    protected $sendPurchasePost;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Observer
     */
    protected $observer;

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->dataMap = $this->getMockBuilder(DataMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sendPurchasePostData = $this->getMockBuilder(SendPurchasePostData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->setMethods(['create','load'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sendPurchasePost = $this->objectManager->getObject(
            SendPurchasePost::class,
            [
                'dataMap' => $this->dataMap,
                'sendPurchasePostData' => $this->sendPurchasePostData,
                'orderFactory' => $this->orderFactory,
                'configHelper' => $this->configHelper
            ]
        );
    }


    /**
     * @throws NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function testExecuteValid()
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->setMethods(array_merge(get_class_methods(Observer::class), ['getOrder','getId']))
            ->disableOriginalConstructor()
            ->getMock();

        $order = $this->getMockBuilder(Order::class)
            ->setMethods(array_merge(get_class_methods(Observer::class), ['getStore','getWebsiteId']))
            ->disableOriginalConstructor()
            ->getMock();

        $orderId = 200;

        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(true);
        $observer->expects($this->once())->method('getEvent')->willReturnSelf();
        $observer->expects($this->once())->method('getOrder')->willReturnSelf();
        $observer->expects($this->once())->method('getId')->willReturn($orderId);
        $this->orderFactory->expects($this->once())->method('create')->willReturnSelf();
        $this->orderFactory->expects($this->once())->method('load')->with($orderId)->willReturn($order);

        $this->dataMap->expects($this->once())->method('mapData')->with($order)->willReturn(['array']);
        $this->sendPurchasePostData->expects($this->once())->method('sendPurchasePostToOrdergroove')->with([
            'array'
        ])->willReturnSelf();
        $order->expects($this->once())->method('getStore')->willReturnSelf();
        $order->expects($this->once())->method('getWebsiteId')->willReturn('1');
        $this->sendPurchasePost->execute($observer);
    }

    /**
     * @throws NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function testExecuteModuleDisabled()
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->setMethods(array_merge(get_class_methods(Observer::class), ['getOrder','getId']))
            ->disableOriginalConstructor()
            ->getMock();

        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(false);
        $observer->expects($this->never())->method('getEvent');
        $observer->expects($this->never())->method('getOrder');
        $observer->expects($this->never())->method('getId');
        $this->orderFactory->expects($this->never())->method('create');
        $this->orderFactory->expects($this->never())->method('load');

        $this->dataMap->expects($this->never())->method('mapData');
        $this->sendPurchasePostData->expects($this->never())->method('sendPurchasePostToOrdergroove');

        $this->sendPurchasePost->execute($observer);
    }


    /**
     * @throws NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function testExecuteInValid()
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->setMethods(array_merge(get_class_methods(Observer::class), ['getOrder','getId']))
            ->disableOriginalConstructor()
            ->getMock();

        $order = $this->getMockBuilder(Order::class)
            ->setMethods(array_merge(get_class_methods(Observer::class), ['getStore','getWebsiteId']))
            ->disableOriginalConstructor()
            ->getMock();

        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(true);
        $observer->expects($this->once())->method('getEvent')->willReturnSelf();
        $observer->expects($this->once())->method('getOrder')->willReturnSelf();
        $observer->expects($this->once())->method('getId')->willReturn(0);
        $this->orderFactory->expects($this->never())->method('create')->willReturnSelf();
        $this->orderFactory->expects($this->never())->method('load')->with(null)->willReturn($order);

        $this->dataMap->expects($this->never())->method('mapData')->with($order)->willReturn(['array']);
        $this->sendPurchasePostData->expects($this->never())->method('sendPurchasePostToOrdergroove')->with([
            'array'
        ])->willReturnSelf();
        $order->expects($this->never())->method('getStore')->willReturnSelf();
        $order->expects($this->never())->method('getWebsiteId')->willReturn('1');
        $this->sendPurchasePost->execute($observer);
    }
}
