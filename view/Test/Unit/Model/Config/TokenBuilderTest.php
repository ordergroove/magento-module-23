<?php

namespace Ordergroove\Subscription\Test\Unit\Model\Config;

use Magento\Sales\Model\Order;
use Magento\Store\Model\Store;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Ordergroove\Subscription\Model\Config\TokenBuilder;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TokenBuilderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TokenBuilder
     */
    private $testClass;

    /**
     * @var PaymentTokenManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenManagement;

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->paymentTokenManagement = $this->getMockBuilder(PaymentTokenManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testClass = $this->objectManager->getObject(
            TokenBuilder::class,
            ['paymentTokenManagement' => $this->paymentTokenManagement]
        );
    }

    public function testBuildToken()
    {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $payment = $this->getMockBuilder(Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tokenInterface = $this->getMockBuilder(PaymentTokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order->expects($this->exactly(2))->method('getPayment')->willReturn($payment);
        $payment->expects($this->once())->method('getEntityId')->willReturn("4");
        $payment->expects($this->once())->method('getMethod')->willReturn('testgateway');
        $order->expects($this->once())->method('getStore')->willReturn($store);
        $store->expects($this->once())->method('getWebsiteId')->willReturn("1");
        $this->paymentTokenManagement->expects($this->once())
            ->method('getByPaymentId')
            ->with('4')
            ->willReturn($tokenInterface);
        $tokenInterface->expects($this->once())->method('getGatewayToken')->willReturn('123');
        $this->assertEquals("1::123::testgateway", $this->testClass->buildToken($order));
    }

    public function testBuildTokenNoToken()
    {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payment = $this->getMockBuilder(Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order->expects($this->once())->method('getPayment')->willReturn($payment);
        $payment->expects($this->once())->method('getEntityId')->willReturn("4");
        $this->paymentTokenManagement->expects($this->once())
            ->method('getByPaymentId')
            ->with('4')
            ->willReturn(null);
        $this->assertEquals("", $this->testClass->buildToken($order));
    }

    public function testSplitToken()
    {
        $this->assertEquals([
            'website_id' => 1,
            'method' => 'testgateway',
            'token' => '123'
        ], $this->testClass->splitToken("1::123::testgateway"));
    }
}
