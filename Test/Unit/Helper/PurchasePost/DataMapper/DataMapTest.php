<?php

namespace Ordergroove\Subscription\Test\Unit\Helper\PurchasePost\DataMapper;

use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\Store;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Helper\PurchasePost\DataMapper\DataMap;
use Ordergroove\Subscription\Helper\PurchasePost\DataMapper\DataMapHelper;
use Ordergroove\Subscription\Model\Config\TokenBuilder;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataMapTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DataMap
     */
    private $testClass;
    /**
     * @var TokenBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenBuilder;
    /**
     * @var DataMapHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataMapHelper;
    /**
     * @var ConfigHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHelper;


    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->tokenBuilder = $this->getMockBuilder(TokenBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataMapHelper = $this->getMockBuilder(DataMapHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testClass = $this->objectManager->getObject(
            DataMap::class,
            [
                'tokenBuilder' => $this->tokenBuilder,
                'dataMapHelper' => $this->dataMapHelper,
                'configHelper' => $this->configHelper
            ]
        );
    }

    public function testMapData()
    {

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $address = $this->getMockBuilder(OrderAddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenBuilder->expects($this->once())->method('buildToken')->willReturn('123');
        $order->expects($this->once())->method('getStore')->willReturn($store);
        $store->expects($this->once())->method('getWebsiteId')->willReturn('1');
        $this->configHelper->expects($this->once())->method('getPublicId')->with('1')->willReturn('12345');

        $order->expects($this->once())->method('getId')->willReturn('1');
        $order->expects($this->once())->method('getCustomerId')->willReturn('1');
        $order->expects($this->once())->method('getCustomerFirstName')->willReturn('Floopy');
        $order->expects($this->once())->method('getCustomerLastName')->willReturn('Floppson');
        $order->expects($this->once())->method('getCustomerEmail')->willReturn('test@test.com');
        $order->expects($this->once())->method('getShippingAddress')->willReturn('shipping');
        $order->expects($this->exactly(2))->method('getBillingAddress')->willReturn($address);
        $address->expects($this->once())->method('getTelephone')->willReturn('12345');

        $this->dataMapHelper->expects($this->exactly(2))->method('getAddressMappedData')->willReturn('123');

        $order->expects($this->exactly(2))->method('getPayment')->willReturn($payment);
        $payment->expects($this->once())->method('getMethod')->willReturn('braintree');
        $payment->expects($this->once())->method('getAdditionalInformation')->willReturn([
            'og_optins' => '123'
        ]);

        $this->dataMapHelper->expects($this->once())->method('getExpiration')->willReturn('123');

        $payment->expects($this->once())->method('getCcType')->willReturn('VI');

        $this->dataMapHelper->expects($this->once())->method('getProducts')->willReturn(['products']);
        $this->assertEquals([
            'products' => ['products'],
            'merchant_id' => '12345',
            'merchant_order_id' => '1',
            'user' => [
                'user_id' => '1',
                'first_name' => 'Floopy',
                'last_name' => 'Floppson',
                'email' => 'test%40test.com',
                'shipping_address' => '123',
                'billing_address' => '123',
                'phone_number' => '12345'
            ],
            'og_cart_tracking' => false,
            'tracking' => 123,
            'payment' => [
                'token_id' => '123',
                'cc_exp_date' => '123',
                'cc_type' => 1
            ]
        ], $this->testClass->mapData($order));

    }
}
