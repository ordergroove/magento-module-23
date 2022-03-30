<?php

namespace Ordergroove\Subscription\Test\Unit\Model\Signature;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Model\Signature\Signature;
use PHPUnit\Framework\TestCase;

class SignatureTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHelper;

    /**
     * @var Encryptor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $encryptor;

    /**
     * @var Signature
     */
    private $signature;

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

        $this->encryptor = $this->getMockBuilder(Encryptor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->signature = $this->objectManager->getObject(
            Signature::class,
            [
                'configHelper' => $this->configHelper,
                'encryptor' => $this->encryptor
            ]
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testCreateSignature()
    {
        $hashKey = "123";
        $field = "111";
        $timestamp = 141;
        $this->configHelper->expects($this->once())->method("getHashKey")->willReturn($hashKey);
        $this->encryptor->expects($this->once())->method("validateKey")->with($hashKey)->willReturn(true);
        $this->encryptor->expects($this->once())->method("setNewKey")->with($hashKey);
        $this->encryptor->expects($this->once())->method("hash")->with($field . "|" . $timestamp)->willReturn(1234);

        $this->assertEquals(["signature" => "EjQ=", "timestamp" => 141, "field" => $field], $this->signature->createSignature($field, "", $timestamp));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testCreateSignatureBadHashKey()
    {
        $hashKey = "123";
        $field = "111";
        $timestamp = 141;
        $this->configHelper->expects($this->once())->method("getHashKey")->willReturn($hashKey);
        $this->encryptor->expects($this->once())->method("validateKey")->with($hashKey)->willThrowException(new \Exception("test"));
        $this->encryptor->expects($this->never())->method("setNewKey");
        $this->encryptor->expects($this->never())->method("hash");
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Error with Ordergroove Hash Key: test");
        $this->signature->createSignature($field, "", $timestamp);
    }
}
