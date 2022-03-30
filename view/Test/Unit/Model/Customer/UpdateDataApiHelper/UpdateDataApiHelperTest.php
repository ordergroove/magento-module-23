<?php
namespace Swapnil\TestApi\Test\Model\Customer\UpdateDataApiHelper;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Model\Config\UrlBuilder;
use Ordergroove\Subscription\Model\Signature\Signature;
use PHPUnit\Framework\TestCase;
use Ordergroove\Subscription\Model\Customer\UpdateDataApiHelper\UpdateDataApiHelper;

/**
 * Class UpdateDataApiHelperTest
 * @package Swapnil\TestApi\Test\Model\Customer\UpdateDataApiHelper
 */
class UpdateDataApiHelperTest extends TestCase
{
    /**
     * @var ObjectManager|string
     */
    protected $objectManager = '';
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Signature
     */
    protected $signatureHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var UpdateDataApiHelper
     */
    protected $updateDataApiHelper;
    /**
     * setup mocks
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->signatureHelper = $this->getMockBuilder(Signature::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressFactory = $this->getMockBuilder(AddressFactory::class)
            ->setMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressRepository = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->setMethods(array_merge(get_class_methods(AddressRepositoryInterface::class), ['getTelephone']))
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateDataApiHelper = $this->objectManager->getObject(
            UpdateDataApiHelper::class,
            [
                'configHelper' => $this->configHelper,
                'signatureHelper' => $this->signatureHelper,
                'customerRepository' => $this->customerRepository,
                'addressFactory' => $this->addressFactory,
                'addressRepository' => $this->addressRepository,
                'urlBuilder' => $this->urlBuilder,
                'jsonHelper' => $this->jsonHelper
            ]
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testPrepareRequest()
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->setMethods(array_merge(get_class_methods(Customer::class), ['getDefaultShipping']))
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepository->expects($this->any())->method('get')->willReturn($customer);
        $shippingAddress = [
            'telephone' => '8918219298'
        ];
        $customer->expects($this->any())->method('getDefaultShipping')->willReturn(2);
        $this->addressRepository->expects($this->any())->method('getById')->with(2)->willReturn($this->addressRepository);
        $this->addressRepository->expects($this->once())->method('getTelephone')->willReturn($shippingAddress['telephone']);

        $customer->expects($this->once())->method('getId')->willReturn(2);
        $this->configHelper->expects($this->once())->method('getPublicId')->willReturn('h1iuh2878982i382saasssss=');
        $timeStamp = time();
        $signatureArray = [
            'signature' => "EjQ=",
            'timestamp' => $timeStamp,
            'field' => 2,
        ];
        $this->signatureHelper->expects($this->once())->method('createSignature')->willReturn($signatureArray);
        $buildAuthorizationArray = [
            'public_id' => 'h1iuh2878982i382saasssss=',
            'ts' => $timeStamp,
            'sig_field' => 2,
            'sig' => $signatureArray['signature']
        ];

        $buildRequestBodyArray = [
            'merchant' => 'h1iuh2878982i382saasssss=',
            'merchant_user_id' => 2,
            'email' => 'test@test.com',
            'phone_number' => '8918219298'
        ];

        $updateUrl = 'https://restapi.ordergroove.com/customers/2/set_contact_details';
        $this->urlBuilder->expects($this->once())->method('getCustomerUpdateUrl')->willReturn($updateUrl);
        $this->assertEquals([
            'customerUpdateUrl' => $updateUrl,
            'requestBody' => $buildRequestBodyArray,
            'authorizationHeader' => $buildAuthorizationArray
        ], $this->updateDataApiHelper->prepareRequest('test@test.com'));
    }
}

