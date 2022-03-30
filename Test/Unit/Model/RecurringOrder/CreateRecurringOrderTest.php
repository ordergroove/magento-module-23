<?php

namespace Ordergroove\Subscription\Test\Unit\Model\RecurringOrder;

use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Exception;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use Ordergroove\Subscription\Helper\RecurringOrderHelper\CreateRecurringOrderHelper;
use Ordergroove\Subscription\Model\RecurringOrder\CreateRecurringOrder;


/**
 * Class CreateRecurringOrderTest
 * @package Ordergroove\Subscription\Test\Unit\Model\RecurringOrder
 */
class CreateRecurringOrderTest extends TestCase
{
    /**
     * @var ObjectManager|string
     */
    protected $objectManager = '';

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var CreateRecurringOrderHelper
     */
    protected $createRecurringOrderHelper;

    /**
     * @var CreateRecurringOrder
     */
    protected $createRecurringOrder;

    /**
     * @var QuoteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepository;

    /**
     * setup mocks
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteFactory = $this->getMockBuilder(QuoteFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteManagement = $this->getMockBuilder(QuoteManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->createRecurringOrderHelper = $this->getMockBuilder(CreateRecurringOrderHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepository = $this->getMockBuilder(QuoteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->createRecurringOrder = $this->objectManager->getObject(
            CreateRecurringOrder::class,
            [
                'product' => $this->product,
                'quoteFactory' => $this->quoteFactory,
                'quoteManagement' => $this->quoteManagement,
                'customerRepository' => $this->customerRepository,
                'store' => $this->store,
                'createRecurringOrderHelper' => $this->createRecurringOrderHelper,
                'quoteRepository' => $this->quoteRepository
            ]
        );
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws CommandException
     */
    public function testPlaceOrder()
    {
        $productId = 15;
        $quantity = 2;
        $splitToken = [
            'website_id' => 1,
            'method' => 'braintree',
            'token' => 'jsikud782l'
        ];
        $returnValidationData = [
            'token' => 'jsikud782l',
            'public_hash' => '893yihau7q31829182jjdsdaisj=',
            'method' => 'braintree'
        ];
        $returnValue = [
            'method' => 'braintree',
            'public_hash' => '893yihau7q31829182jjdsdaisj=',
            'payment_method_nonce' => 'asniausuq@hqi7we2932unksfnduhwseh'
        ];
        $data = $this->dataProvider();
        $shippingAddress = [
            'firstname' => $data['customer']['customerFirstName'],
            'lastname' => $data['customer']['customerLastName'],
            'street' => $data['customer']['customerShippingAddress'],
            'city' => $data['customer']['customerShippingCity'],
            'country_id' => $data['customer']['customerShippingCountry'],
            'region' => $data['customer']['customerShippingState'],
            'postcode' => $data['customer']['customerShippingZip'],
            'telephone' => $data['customer']['customerShippingPhone']
        ];
        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(Quote::class)
            ->setMethods(array_merge(get_class_methods(Quote::class), ['setCollectShippingRates','collectShippingRates','setShippingMethod','setPaymentMethod','setInventoryProcessed','importData']))
            ->disableOriginalConstructor()
            ->getMock();
        $this->createRecurringOrderHelper->expects($this->once())->method('getStockStatus')->willReturnSelf();
        $this->createRecurringOrderHelper->expects($this->once())->method('getStockQty');
        $orderTokenId = '1::braintree::jsikud782l';
        $this->createRecurringOrderHelper->expects($this->once())->method('parseTokenData')->with($orderTokenId)->willReturn($splitToken);
        $customerEmail = 'test@test.com';
        $this->createRecurringOrderHelper->expects($this->once())->method('checkCustomerData')->with($customerEmail, 1)->willReturn(['customerId' => 2]);
        $this->createRecurringOrderHelper->expects($this->once())->method('validateToken')->with($splitToken, 2)->willReturn($returnValidationData);
        $this->customerRepository->expects($this->once())->method('getById')->with(2)->willReturn($customer);
        $this->quoteFactory->expects($this->once())->method('create')->willReturn($quote);
        $quote->expects($this->once())->method('setStore')->with($this->store)->willReturnSelf();
        $quote->expects($this->any())->method('assignCustomer')->with($customer)->willReturnSelf();
        $this->product->expects($this->once())->method('load')->with($productId)->willReturn($product);
        $product->expects($this->once())->method('setPrice')->with('15.99');
        $quote->expects($this->once())->method('addProduct')->with($product, $quantity)->willReturnSelf();
        $quote->expects($this->once())->method('getBillingAddress')->willReturnSelf();
        $quote->expects($this->exactly(2))->method('addData')->with($shippingAddress)->willReturnSelf();
        $quote->expects($this->exactly(2))->method('getShippingAddress')->willReturnSelf();
        $quote->expects($this->exactly(2))->method('addData')->with($shippingAddress)->willReturnSelf();
        $quote->expects($this->once())->method('setCollectShippingRates')->with(true)->willReturnSelf();
        $quote->expects($this->once())->method('collectShippingRates')->willReturnSelf();
        $quote->expects($this->once())->method('setShippingMethod')->with('flatrate_flatrate')->willReturnSelf();
        $quote->expects($this->once())->method('setPaymentMethod')->with('braintree')->willReturnSelf();
        $quote->expects($this->once())->method('setInventoryProcessed')->with(false)->willReturnSelf();
        $this->createRecurringOrderHelper->expects($this->once())->method('createPaymentMethodNonce')->with($returnValidationData, 2)->willReturn($returnValue);
        $quote->expects($this->once())->method('getPayment')->willReturnSelf();
        $quote->expects($this->once())->method('importData')->with($returnValue)->willReturnSelf();
        $quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteManagement->expects($this->once())->method('submit')->with($quote)->willReturn($order);
        $this->createRecurringOrderHelper->expects($this->once())->method('afterPlaceOrder')->with($order);

        $this->quoteRepository->expects($this->exactly(2))->method('save');
        $this->createRecurringOrder->placeRecurringOrder($data);

    }


    public function dataProvider()
    {
        return [
            'head' => [
                'orderOgId' => '{{ogId}}',
                'orderOgDate' => '2019-09-18',
                'orderSourcePartnerId' => '155',
                'orderSourcePartnerPublicId' => 'dd80d6a4107a11ea8bacbc764e10b970',
                'orderSourcePartnerName' => 'Website',
                'orderSubtotalValue' => '140',
                'orderShipping' => '5.0',
                'orderTotalValue' => '153.43',
                'orderTokenId' => '1::braintree::jsikud782l',
                'orderCcType' => 'Mastercard',
            ],
            'customer' => [
                'customerOgId' => '3950333',
                'customerPartnerId' => '2835374276743',
                'customerName' => 'Retro Man',
                'customerFirstName' => 'Retro',
                'customerLastName' => 'Man',
                'customerEmail' => 'test@test.com',
                'customerShippingFirstName' => 'Floopy',
                'customerShippingLastName' => 'Floppson',
                'customerShippingAddress' => '770 Floop Street',
                'customerShippingAddress1' => '770 Floop Street',
                'customerShippingAddress2' => '',
                'customerShippingCity' => 'New York',
                'customerShippingState' => 'NY',
                'customerShippingZip' => '10004',
                'customerShippingPhone' => '(612) 719-1855',
                'customerShippingFax' => '',
                'customerShippingCompany' => '',
                'customerShippingCountry' => 'US',
            ],
            'items' => [
                'item' => [
                    'qty' => '2',
                    'sku' => '24-WB04',
                    'name' => 'Push It Messenger Bag',
                    'product_id' => '15',
                    'discount' => '0',
                    'finalPrice' => '15.99',
                    'price' => '10.00',
                ],
            ],
        ];
    }
}
