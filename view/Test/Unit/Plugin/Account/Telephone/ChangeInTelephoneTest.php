<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Test\Unit\Plugin\Account\Telephone;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Controller\Address\FormPost;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Model\Customer\UpdateDataApi;
use Ordergroove\Subscription\Plugin\Account\Telephone\ChangeInTelephone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ChangeInTelephoneTest
 * @package Ordergroove\Subscription\Test\Unit\Plugin\Account\Telephone
 */
class ChangeInTelephoneTest extends TestCase
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
     * @var Request
     */
    protected $request;

    /**
     * @var AddressRepositoryInterface;
     */
    protected $addressRepository;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ChangeInTelephone
     */
    protected $changeInTelephone;

    /**
     * @var Customer|MockObject
     */
    protected $customer;

    /**
     * @var
     */
    protected $subjectMock;

    /**
     * @var
     */
    protected $objectMock;

    /**
     * @var
     */
    protected $addressRepositoryv1;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @return void
     * @throws \Exception
     */
    public function testAroundExecute()
    {
        $telephone = '1287199101';
        $customerEmail = 'test@test.com';

        $customer = $this->getMockBuilder(Customer::class)
            ->setMethods(['getEmail','getDefaultBillingAddress'])
            ->disableOriginalConstructor()
            ->getMock();

        $address = $this->getMockBuilder(Address::class)
            ->setMethods(['getTelephone'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession->expects($this->exactly(2))->method('getCustomer')->willReturn($customer);
        $customer->expects($this->once())->method('getDefaultBillingAddress')->willReturn($address);
        $address->expects($this->once())->method('getTelephone')->willReturn($telephone);

        $callbackMock = $this->getMockBuilder(FormPost::class)
            ->setMethods(['__invoke','execute'])
            ->disableOriginalConstructor()
            ->getMock();

        $proceed = $this->getMockBuilder('DummyClass')
            ->setMethods(['__invoke'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(true);
        $updatedAddressTelephone = '1287199195';
        $this->request->expects($this->once())->method('getParam')->willReturn(2);

        $address2 = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressRepository->expects($this->once())->method('getById')->with(2)->willReturn($address2);
        $address2->expects($this->once())->method('getTelephone')->willReturn($updatedAddressTelephone);
        $this->updateDataApiHelper->expects($this->once())->method('executeUpdateRequest')->willReturn("Hi");
        $proceed->expects($this->once())->method('__invoke');
        $this->changeInTelephone->aroundExecute($callbackMock, $proceed);
    }

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->setMethods(['getCustomer','getEmail','getDefaultBillingAddress'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRepository = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->setMethods(array_merge(get_class_methods(AddressRepositoryInterface::class), ['getTelephone']))
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRepositoryv1 = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->setMethods(array_merge(get_class_methods(AddressRepositoryInterface::class), ['getTelephone']))
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(FormPost::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectMock = $this->getMockBuilder(FormPost::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->updateDataApiHelper = $this->getMockBuilder(UpdateDataApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->changeInTelephone = $this->objectManager->getObject(
            ChangeInTelephone::class,
            [
                "updateDataApiHelper" => $this->updateDataApiHelper,
                "customerSession" => $this->customerSession,
                "request" => $this->request,
                "addressRepository" => $this->addressRepository,
                "configHelper" => $this->configHelper
            ]
        );
    }
}
