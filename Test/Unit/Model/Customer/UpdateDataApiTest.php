<?php

namespace Ordergroove\Subscription\Test\Unit\Model\Customer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Logger\CustomerUpdates\Error\Logger as CustomerErrorLogger;
use Ordergroove\Subscription\Logger\CustomerUpdates\Info\Logger as CustomerInfoLogger;
use Ordergroove\Subscription\Model\Config\UrlBuilder;
use Ordergroove\Subscription\Model\Signature\Signature;
use Ordergroove\Subscription\Model\Customer\UpdateDataApi;
use PHPUnit\Framework\TestCase;


/**
 * Class UpdateDataApiTest
 * @package Ordergroove\Subscription\Test\Unit\Model\Customer
 */
class UpdateDataApiTest extends TestCase
{

    /**
     * @var ObjectManager|string
     */
    protected $objectManager = '';

    /**
     * @var Curl
     */
    protected $curl;

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
     * @var CustomerErrorLogger
     */
    protected $customerErrorLogger;

    /**
     * @var CustomerInfoLogger
     */
    protected $customerInfoLogger;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var Data
     */
    protected $jsonhelper;

    /**
     * @var UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var UpdateDataApi
     */
    protected $updateDataApi;

    /**
     * @var
     */
    protected $addressRepository;

    /**
     * setup mocks
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->curl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->customerErrorLogger = $this->getMockBuilder(CustomerErrorLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerInfoLogger = $this->getMockBuilder(CustomerInfoLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressRepository = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->setMethods(array_merge(get_class_methods(AddressRepositoryInterface::class), ['getTelephone']))
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonhelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateDataApi = $this->objectManager->getObject(
            UpdateDataApi::class,
            [
                'curl' => $this->curl,
                'configHelper' => $this->configHelper,
                'signatureHelper' => $this->signatureHelper,
                'customerRepository' => $this->customerRepository,
                'addressRepository' => $this->addressRepository,
                'jsonHelper' => $this->jsonhelper,
                'urlBuilder' => $this->urlBuilder,
                'customerErrorLogger' => $this->customerErrorLogger,
                'customerInfoLogger' => $this->customerInfoLogger
            ]
        );
    }

    public function testExecuteUpdateRequestSuccess()
    {
        $this->updateDataApi = $this->getMockBuilder(UpdateDataApi::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(false, $this->updateDataApi->executeUpdateRequest('test@test.com'));
    }

    public function testExecuteUpdateRequestNoSuccess()
    {
        $this->updateDataApi = $this->getMockBuilder(UpdateDataApi::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertNotEquals(true, $this->updateDataApi->executeUpdateRequest('test@test.com'));
    }
}
