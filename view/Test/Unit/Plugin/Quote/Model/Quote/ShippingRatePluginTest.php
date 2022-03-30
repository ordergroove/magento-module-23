<?php

namespace Ordergroove\Subscription\Test\Unit\Plugin\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Address;
use Ordergroove\Subscription\Plugin\Quote\Model\Quote\ShippingRatePlugin;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ShippingRatePluginTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ShippingRatePlugin
     */
    private $testClass;

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->testClass = $this->objectManager->getObject(ShippingRatePlugin::class);
    }

    public function testBeforeAddShippingRate()
    {
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsOrdergrooveShipping', 'getOrdergrooveShippingAmount'])
            ->getMock();
        $address->expects($this->once())->method('getIsOrdergrooveShipping')->willReturn(true);
        $address->expects($this->once())->method('getOrdergrooveShippingAmount')->willReturn(2.0);

        $rate = $this->getMockBuilder(Address\Rate::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPrice'])
            ->getMock();
        $rate->expects($this->once())->method('setPrice')->with(2.0);

        $this->testClass->beforeAddShippingRate($address, $rate);
    }

    public function testBeforeAddShippingRateNotOrdergroove()
    {
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsOrdergrooveShipping', 'getOrdergrooveShippingAmount'])
            ->getMock();
        $address->expects($this->once())->method('getIsOrdergrooveShipping')->willReturn(false);
        $address->expects($this->never())->method('getOrdergrooveShippingAmount');

        $rate = $this->getMockBuilder(Address\Rate::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPrice'])
            ->getMock();
        $rate->expects($this->never())->method('setPrice');

        $this->testClass->beforeAddShippingRate($address, $rate);
    }
}
