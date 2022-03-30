<?php

namespace Ordergroove\Subscription\Test\Unit\Model\Authentication;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Model\Authentication\ValidateAuthorization;
use Ordergroove\Subscription\Model\Signature\Signature;
use PHPUnit\Framework\TestCase;

/**
 * Class ValidateAuthorizationTest
 * @package Ordergroove\Subscription\Test\Unit\Model\Authentication
 */
class ValidateAuthorizationTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Signature
     */
    protected $signature;

    /**
     * @var ValidateAuthorization
     */
    protected $validateAuthorization;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->signature = $this->getMockBuilder(Signature::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validateAuthorization = $this->objectManager->getObject(
            ValidateAuthorization::class,
            [
                'signature' => $this->signature
            ]
        );
    }

    /**
     * @throws NoSuchEntityException
     */
    public function testInValidateAuthentication()
    {
        $sigField = 2;
        $timeStamp = 1605230097;
        $sig = "rkNV10GD6ifUSUWa30Z9UE1Pt2lh14iXNSgroZCWbVA=";

        $createdSignature = [
            'signature' => 'rkNV10GD6ifUSUWa30Z9UE1Pt2lh14iXNSgroZCWbVA=12',
            'timestamp' => '1605230097',
            'field' => '2',
        ];
        $this->signature->expects($this->once())->method('createSignature')
            ->with($sigField, '', $timeStamp)
            ->willReturn($createdSignature);
        $this->assertNotSame($createdSignature['signature'], $sig);
        $this->assertEquals(false, $this->validateAuthorization->validateAuthentication($sigField, $sig, $timeStamp));
    }

    /**
     * @throws NoSuchEntityException
     */
    public function testValidateAuthentication()
    {
        $sigField = 2;
        $timeStamp = 1605230097;
        $sig = "rkNV10GD6ifUSUWa30Z9UE1Pt2lh14iXNSgroZCWbVA=";

        $createdSignature = [
            'signature' => 'rkNV10GD6ifUSUWa30Z9UE1Pt2lh14iXNSgroZCWbVA=',
            'timestamp' => '1605230097',
            'field' => '2',
        ];
        $this->signature->expects($this->once())->method('createSignature')
            ->with($sigField, '', $timeStamp)
            ->willReturn($createdSignature);
        $this->assertSame($createdSignature['signature'], $sig);
        $this->assertEquals(true, $this->validateAuthorization->validateAuthentication($sigField, $sig, $timeStamp));
    }
}
