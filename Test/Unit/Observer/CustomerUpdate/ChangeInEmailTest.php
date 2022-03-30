<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Test\Unit\Observer\CustomerUpdate;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Ordergroove\Subscription\Model\Customer\UpdateDataApi;
use Ordergroove\Subscription\Observer\CustomerUpdate\ChangeInEmail;

/**
 * Class ChangeInEmailTest
 * @package Ordergroove\Subscription\Test\Unit\Observer\CustomerUpdate
 */
class ChangeInEmailTest extends Testcase
{
    /**
     * @var UpdateDataApi
     */
    protected $updateDataApiHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ChangeInEmail
     */
    protected $changeInEmail;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Observer
     */
    protected $observer;

    /**
     * @return void
     * @throws \Exception
     */
    public function testExecuteEmailChange()
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession->expects($this->once())->method('getCustomer')->willReturn($customer);
        $customerId = 2;
        $customerEmail = 'test@test.com';

        $this->updateDataApiHelper = $this->getMockBuilder(UpdateDataApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $observer = $this->getMockBuilder(Observer::class)
            ->setMethods(array_merge(get_class_methods(Observer::class), ['getEmail']))
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->once())->method('getEvent')->willReturnSelf();
        $observer->expects($this->once())->method('getEmail')->willReturn('test@changed.com');
        $this->updateDataApiHelper->expects($this->never())->method('executeUpdateRequest')->with('test@changed.com');
        $this->changeInEmail->execute($observer);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testExecuteEmailNoChange()
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession->expects($this->once())->method('getCustomer')->willReturn($customer);
        $customerId = 2;
        $customerEmail = 'test@test.com';

        $this->updateDataApiHelper = $this->getMockBuilder(UpdateDataApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $observer = $this->getMockBuilder(Observer::class)
            ->setMethods(array_merge(get_class_methods(Observer::class), ['getEmail']))

            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->once())->method('getEvent')->willReturnSelf();
        $observer->expects($this->once())->method('getEmail')->willReturn('test@changed.com');
        $this->updateDataApiHelper->expects($this->never())->method('executeUpdateRequest')->with('test@changed.com');
        $this->changeInEmail->execute($observer);
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

        $this->updateDataApiHelper = $this->getMockBuilder(UpdateDataApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->changeInEmail = $this->objectManager->getObject(
            ChangeInEmail::class,
            [
                'updateApiHelper' => $this->updateDataApiHelper,
                'customerSession' => $this->customerSession
            ]
        );
    }
}
