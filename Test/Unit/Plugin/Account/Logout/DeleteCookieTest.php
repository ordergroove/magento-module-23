<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Test\Unit\Plugin\Account\Logout;

use Magento\Customer\Controller\Account\Logout;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Helper\ConfigHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ordergroove\Subscription\Model\Cookie\OgAuthCookie;
use Ordergroove\Subscription\Plugin\Account\Logout\DeleteCookie;

/**
 * Class DeleteCookieTest
 * @package Ordergroove\Subscription\Test\Unit\Plugin\Account\Logout
 */
class DeleteCookieTest extends TestCase
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
     * @var DeleteCookie
     */
    protected $deleteCookie;

    /**
     * @var Logout
     */
    protected $logout;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

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

        $this->logout = $this->getMockBuilder(Logout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->deleteCookie = $this->objectManager->getObject(
            DeleteCookie::class,
            [
                'customerSession' => $this->customerSession,
                'cookie' => $this->cookie,
                'logout' => $this->logout,
                'configHelper' => $this->configHelper
            ]
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testBeforeExecuteLoggedIn()
    {
        $cookieName = 'og_auth';
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->cookie->expects($this->once())->method('delete')->willReturn(true);
        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->assertEquals($this->logout, $this->deleteCookie->beforeExecute($this->logout));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testBeforeExecuteNotLoggedIn()
    {
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->cookie->expects($this->once())->method('delete')->willReturn(false);
        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->assertEquals($this->logout, $this->deleteCookie->beforeExecute($this->logout));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testBeforeExecuteModuleDisabled()
    {
        $this->customerSession->expects($this->never())->method('isLoggedIn');
        $this->cookie->expects($this->never())->method('delete');
        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->assertEquals($this->logout, $this->deleteCookie->beforeExecute($this->logout));
    }
}
