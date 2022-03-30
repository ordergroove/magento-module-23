<?php
/*
 * Copyright (c) 2020. All rights reserved.
 */

namespace Ordergroove\Subscription\Test\Unit\Model\Cookie;

use Magento\Customer\Model\Session;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Ordergroove\Subscription\Model\Signature\Signature;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ordergroove\Subscription\Model\Cookie\OgAuthCookie;

/**
 * Class OgAuthCookieTest
 * @package Ordergroove\Subscription\Test\Unit\Model\Cookie
 */
class OgAuthCookieTest extends TestCase
{
    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;
    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;
    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;
    /**
     * @var CookieMetadata
     */
    protected $cookieMetadata;
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Signature
     */
    protected $signature;
    /**
     * @var OgAuthCookie
     */
    protected $ogAuthCookie;

    /**
     * @var PublicCookieMetadata|MockObject
     */
    protected $publicCookieMetaData;

    /**
     * @return void
     * @throws \Exception
     */
    public function testCreateCookie()
    {
        $customerId = 2;
        $timestamp = time();
        $metaData = [
            'duration' => 7200,
            'path' => 'customer/account',
            'domain' => 'http://test.com/'
        ];
        $publicCookieMetaData = $this->getMockBuilder(PublicCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cookieMetaData = $this->getMockBuilder(CookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $store = $this->getMockBuilder(Store::class)->disableOriginalConstructor()->getMock();
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $store->expects($this->once())->method('getWebsiteId')->willReturn('1');

        $signatureArray = [
            'signature' => "EjQ=",
            'timestamp' => $timestamp,
            'field' => 2,
        ];
        $this->signature->expects($this->once())->method('createSignature')->willReturn($signatureArray);
        $this->cookieMetadataFactory->expects($this->once())->method('createPublicCookieMetadata')->willReturn($publicCookieMetaData);
        $publicCookieMetaData->expects($this->once())->method('setDuration')->with($metaData['duration'])->willReturnSelf();
        $this->sessionManager->expects($this->once())->method('getCookiePath')->willReturn($metaData['path']);
        $publicCookieMetaData->expects($this->once())->method('setPath')->with($metaData['path'])->willReturnSelf();
        $this->sessionManager->expects($this->once())->method('getCookieDomain')->willReturn($metaData['domain']);
        $publicCookieMetaData->expects($this->once())->method('setDomain')->with($metaData['domain'])->willReturnSelf();
        $this->cookieManager->expects($this->once())->method('setPublicCookie')->with('og_auth',$customerId . "|" . $timestamp . "|" . $signatureArray['signature'], $publicCookieMetaData);
        $this->ogAuthCookie->create(2);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testDeleteCookie()
    {
        $cookieName = "og_auth";
        $cookieMetaData = $this->getMockBuilder(CookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metaData = [
            'duration' => 1000,
            'path' => 'customer',
            'domain' => 'http://test.com/'
        ];

        $this->cookieMetadataFactory->expects($this->once())->method('createCookieMetadata')->willReturn($cookieMetaData);
        $this->sessionManager->expects($this->once())->method('getCookiePath')->willReturn($metaData['path']);
        $cookieMetaData->expects($this->once())->method('setPath')->with($metaData['path'])->willReturnSelf();
        $this->sessionManager->expects($this->once())->method('getCookieDomain')->willReturn($metaData['domain']);
        $cookieMetaData->expects($this->once())->method('setDomain')->with($metaData['domain'])->willReturnSelf();
        $this->cookieManager->expects($this->once())->method('deleteCookie')->with($cookieName, $cookieMetaData);
        $this->assertEquals(true, $this->ogAuthCookie->delete($cookieName));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testSet()
    {
        $cookieValue = '2|141|alsaukhqi98238723@7yihdio82o3';
        $metaData = [
            'duration' => 1000,
            'path' => 'customer/account',
            'domain' => 'http://test.com/'
        ];

        $publicCookieMetaData = $this->getMockBuilder(PublicCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieMetadataFactory->expects($this->once())->method('createPublicCookieMetadata')->willReturn($publicCookieMetaData);
        $publicCookieMetaData->expects($this->once())->method('setDuration')->with($metaData['duration'])->willReturnSelf();
        $this->sessionManager->expects($this->once())->method('getCookiePath')->willReturn($metaData['path']);
        $publicCookieMetaData->expects($this->once())->method('setPath')->with($metaData['path'])->willReturnSelf();
        $this->sessionManager->expects($this->once())->method('getCookieDomain')->willReturn($metaData['domain']);
        $publicCookieMetaData->expects($this->once())->method('setDomain')->with($metaData['domain'])->willReturnSelf();
        $this->cookieManager->expects($this->once())->method('setPublicCookie')->with('og_auth',$cookieValue, $publicCookieMetaData);
        $this->ogAuthCookie->set($cookieValue, $metaData['duration']);
    }

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->signature = $this->getMockBuilder(Signature::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieMetadataFactory = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieManager = $this->getMockBuilder(CookieManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionManager = $this->getMockBuilder(SessionManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->ogAuthCookie = $this->objectManager->getObject(
            OgAuthCookie::class,
            [
                'storeManager' => $this->storeManager,
                'signature' => $this->signature,
                'cookieMetadataFactory' => $this->cookieMetadataFactory,
                'sessionManager' => $this->sessionManager,
                'cookieManager' => $this->cookieManager
            ]
        );
    }
}
