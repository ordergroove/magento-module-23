<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Test\Unit\Plugin\Account\Login;

use Magento\Customer\Controller\Account\LoginPost;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Helper\ConfigHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ordergroove\Subscription\Model\Cookie\OgAuthCookie;
use Ordergroove\Subscription\Plugin\Account\Login\CreateCookie;

/**
 * Class CreateCookieTest
 * @package Ordergroove\Subscription\Test\Unit\Plugin\Account\Login
 */
class CreateCookieTest extends TestCase
{
    /**
     * @var OgAuthCookie
     */
    protected $cookie;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var object
     */
    protected $createCookie;

    /**
     * @var LoginPost|MockObject
     */
    protected $loginPost;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;


    /**
     * testAfterExecuteModuleDisabled
     */
    public function testAfterExecuteModuleDisabled()
    {
        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->customerSession->expects($this->never())->method('isLoggedIn');
        $this->customerSession->expects($this->never())->method('getCustomer');
        $this->cookie->expects($this->never())->method('delete');
        $this->cookie->expects($this->never())->method('create');
        $this->assertEquals(null, $this->createCookie->afterExecute($this->loginPost, null));
    }

    /**
     * testAfterExecuteModuleEnabled
     */
    public function testAfterExecuteModuleEnabled()
    {
        $cookieValue = '2|141|EjQ=';
        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->customerSession->expects($this->once())->method('getCustomer')->willReturnSelf();
        $this->customerSession->expects($this->once())->method('getId')->willReturn(2);
        $this->cookie->expects($this->once())->method('delete');
        $this->cookie->expects($this->once())->method('create');
        $this->assertEquals($cookieValue, $this->createCookie->afterExecute($this->loginPost, $cookieValue));
    }

    /**
     * testAfterExecuteCustomerNotLoggedIn
     */
    public function testAfterExecuteCustomerNotLoggedIn()
    {
        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->customerSession->expects($this->never())->method('getCustomer');
        $this->cookie->expects($this->never())->method('delete');
        $this->cookie->expects($this->never())->method('create');
        $this->assertEquals(null, $this->createCookie->afterExecute($this->loginPost, null));
    }

    /**
     * testAfterExecuteCustomerLoggedIn
     */
    public function testAfterExecuteCustomerLoggedIn()
    {
        $cookieValue = '2|141|EjQ=';
        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->customerSession->expects($this->once())->method('getCustomer')->willReturnSelf();
        $this->customerSession->expects($this->once())->method('getId')->willReturn(2);
        $this->cookie->expects($this->once())->method('delete');
        $this->cookie->expects($this->once())->method('create')->with(2)->willReturn($cookieValue);
        $this->assertEquals($cookieValue, $this->createCookie->afterExecute($this->loginPost, $cookieValue));
    }

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookie = $this->getMockBuilder(OgAuthCookie::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loginPost = $this->getMockBuilder(LoginPost::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->createCookie = $this->objectManager->getObject(
            CreateCookie::class,
            [
                'customerSession' => $this->customerSession,
                'cookie' => $this->cookie,
                'loginPost' => $this->loginPost,
                'configHelper' => $this->configHelper
            ]
        );
    }
}
