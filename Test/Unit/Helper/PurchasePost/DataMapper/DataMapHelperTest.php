<?php

namespace Ordergroove\Subscription\Test\Unit\Helper\PurchasePost\DataMapper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Helper\PurchasePost\DataMapper\DataMapHelper;
use PHPUnit\Framework\TestCase;

class DataMapHelperTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DataMapHelper
     */
    private $testClass;

    /**
     * @var ConfigHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHelper;

    public function testGetExpiration()
    {
        $payment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payment->expects($this->once())->method('getCcExpMonth')->willReturn("04");
        $payment->expects($this->once())->method('getCcExpYear')->willReturn('2021');
        $this->configHelper->expects($this->once())->method('getHashKey')->willReturn('12312312312312312312312312312312');
        $this->testClass->getExpiration($payment, "1");
    }

    public function testGetProducts()
    {
        $order = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $items = [
            $this->getMockBuilder(OrderItemInterface::class)
                ->disableOriginalConstructor()
                ->getMock(),
            $this->getMockBuilder(OrderItemInterface::class)
                ->disableOriginalConstructor()
                ->getMock()
        ];

        $order->expects($this->once())->method('getItems')->willReturn($items);

        $items[0]->expects($this->exactly(2))->method('getProductType')->willReturn('simple');
        $items[0]->expects($this->exactly(2))->method('getProductType')->willReturn('configurable');
        $items[0]->expects($this->once())->method('getProductId')->willReturn('1');
        $items[0]->expects($this->once())->method('getParentItem')->willReturn(null);
        $items[0]->expects($this->exactly(2))->method('getSku')->willReturn('123');
        $items[0]->expects($this->exactly(1))->method('getQtyOrdered')->willReturn(1);
        $items[0]->expects($this->exactly(1))->method('getBaseOriginalPrice')->willReturn(12);
        $items[0]->expects($this->exactly(2))->method('getBasePrice')->willReturn(12);

        $this->assertEquals([
            [
                'product' => '1',
                'sku' => '123',
                'purchase_info' => [
                    'quantity' => 1,
                    'price' => 12,
                    'discounted_price' => 0,
                    'total' => 12
                ]
            ]
        ], $this->testClass->getProducts($order));
    }

    public function testGetProductsSecond()
    {
        $order = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $items = [
            $this->getMockBuilder(OrderItemInterface::class)
                ->disableOriginalConstructor()
                ->getMock(),
            $this->getMockBuilder(OrderItemInterface::class)
                ->disableOriginalConstructor()
                ->getMock()
        ];

        $order->expects($this->once())->method('getItems')->willReturn($items);

        $items[0]->expects($this->exactly(2))->method('getProductType')->willReturn('simple');
        $items[0]->expects($this->exactly(2))->method('getProductType')->willReturn('configurable');
        $items[0]->expects($this->exactly(2))->method('getProductId')->willReturn('1');
        $items[0]->expects($this->once())->method('getSku')->willReturn(null);
        $items[0]->expects($this->exactly(1))->method('getQtyOrdered')->willReturn(1);
        $items[0]->expects($this->exactly(1))->method('getBaseOriginalPrice')->willReturn(12);
        $items[0]->expects($this->exactly(2))->method('getBasePrice')->willReturn(12);

        $this->assertEquals([
            [
                'product' => '1',
                'sku' => '1',
                'purchase_info' => [
                    'quantity' => 1,
                    'price' => 12,
                    'discounted_price' => '0',
                    'total' => 12
                ]
            ]
        ], $this->testClass->getProducts($order));
    }

    public function testGetProductsBundle()
    {
        $order = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $items = [
            $this->getMockBuilder(OrderItemInterface::class)
                ->disableOriginalConstructor()
                ->getMock(),
            $this->getMockBuilder(OrderItemInterface::class)
                ->disableOriginalConstructor()
                ->getMock()
        ];

        $order->expects($this->once())->method('getItems')->willReturn($items);

        $items[0]->expects($this->exactly(2))->method('getProductType')->willReturn('bundle');
        $items[0]->expects($this->exactly(2))->method('getProductId')->willReturn('1');
        $items[0]->expects($this->once())->method('getSku')->willReturn(null);
        $items[0]->expects($this->exactly(2))->method('getQtyOrdered')->willReturn(1);
        $items[0]->expects($this->exactly(1))->method('getBaseOriginalPrice')->willReturn(12);
        $items[0]->expects($this->exactly(3))->method('getBasePrice')->willReturn(12);

        $this->assertEquals([
            [
                'product' => '1',
                'sku' => '1',
                'purchase_info' => [
                    'quantity' => 1,
                    'price' => 12,
                    'discounted_price' => '0',
                    'total' => 12
                ]
            ]
        ], $this->testClass->getProducts($order));
    }

    public function testGetAddressMappedData()
    {
        $address = $this->getMockBuilder(OrderAddressInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(OrderAddressInterface::class), ['getData', 'explodeStreetAddress']))
            ->getMock();

        $address->expects($this->once())->method('explodeStreetAddress')->willReturnSelf();
        $address->expects($this->once())->method('getLastName')->willReturn('Floppson');
        $address->expects($this->once())->method('getFirstName')->willReturn('Floopy');
        $address->expects($this->once())->method('getCity')->willReturn('New York');
        $address->expects($this->once())->method('getPostCode')->willReturn('10080');
        $address->expects($this->once())->method('getRegionCode')->willReturn('NY');
        $address->expects($this->once())->method('getCountryId')->willReturn('US');
        $address->expects($this->once())->method('getTelephone')->willReturn('1');
        $address->expects($this->exactly(2))->method('getData')->willReturn('123');

        $this->assertEquals([
            'last_name' => 'Floppson',
            'first_name' => 'Floopy',
            'city' => 'New York',
            'zip_postal_code' => '10080',
            'country_code' => 'US',
            'state_province_code' => 'NY',
            'phone' => '1',
            'address' => '123',
            'address2' => '123'
        ], $this->testClass->getAddressMappedData($address));
    }

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testClass = $this->objectManager->getObject(
            DataMapHelper::class,
            [
                'configHelper' => $this->configHelper
            ]
        );
    }
}
