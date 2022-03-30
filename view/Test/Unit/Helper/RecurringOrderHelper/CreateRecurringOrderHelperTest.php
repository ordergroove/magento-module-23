<?php
declare(strict_types=1);

namespace Ordergroove\Subscription\Test\Unit\Helper\RecurringOrderHelper;

use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Exception;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Sales\Model\Order;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Ordergroove\Subscription\Model\Config\TokenBuilder;
use PHPUnit\Framework\TestCase;
use Ordergroove\Subscription\Helper\RecurringOrderHelper\CreateRecurringOrderHelper;

/**
 * Class CreateRecurringOrderHelperTest
 * @package Ordergroove\Subscription\Test\Unit\Helper\RecurringOrderHelper
 */
class CreateRecurringOrderHelperTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * @var CreateRecurringOrderHelper
     */
    protected $createRecurringOrderHelper;

    /**
     * @var TokenBuilder
     */
    protected $tokenHelper;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var PaymentTokenManagementInterface
     */
    protected $paymentTokenManagement;

    /**
     * @var GetPaymentNonceCommand
     */
    protected $paymentNonce;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * Constructor
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->stockItemRepository = $this->getMockBuilder(StockItemRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenHelper = $this->getMockBuilder(TokenBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerFactory = $this->getMockBuilder(CustomerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentTokenManagement = $this->getMockBuilder(PaymentTokenManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentNonce = $this->getMockBuilder(GetPaymentNonceCommand::class)
            ->setMethods(['execute','get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->createRecurringOrderHelper = $this->objectManager->getObject(
            CreateRecurringOrderHelper::class,
            [
                'stockItemRepository' => $this->stockItemRepository,
                'tokenHelper' => $this->tokenHelper,
                'customerFactory' => $this->customerFactory,
                'paymentTokenManagement' => $this->paymentTokenManagement,
                'paymentNonce' => $this->paymentNonce,
                'jsonHelper' => $this->jsonHelper
            ]
        );
    }

    /**
     * @throws Exception
     * @throws NoSuchEntityException
     */
    public function testGetGivenStockStatus()
    {
        $stockItemIsInStock = $this->getMockBuilder(StockItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemRepository->expects($this->once())->method('get')->with(14)->willReturn($stockItemIsInStock);
        $stockItemIsInStock->expects($this->once())->method('getIsInStock')->willReturn(true);
        $this->createRecurringOrderHelper->getStockStatus(14);
    }

    /**
     * @throws Exception
     * @throws NoSuchEntityException
     */
    public function testGetNoStockStatus()
    {
        $stockItemIsInStock = $this->getMockBuilder(StockItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemRepository->expects($this->once())->method('get')->with(14)->willReturn($stockItemIsInStock);
        $stockItemIsInStock->expects($this->once())->method('getIsInStock')->willReturn(false);
        $this->expectException(Exception::class);
        $this->createRecurringOrderHelper->getStockStatus(14);
    }

    /**
     * @throws Exception
     * @throws NoSuchEntityException
     */
    public function testGetValidStockQty()
    {
        $stockItemQty = $this->getMockBuilder(StockItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemRepository->expects($this->once())->method('get')->with(14)->willReturn($stockItemQty);
        $stockItemQty->expects($this->once())->method('getQty')->willReturn(100);
        $this->createRecurringOrderHelper->getStockQty(14, 2);
    }

    /**
     * @throws Exception
     * @throws NoSuchEntityException
     */
    public function testGetInvalidStockQty()
    {
        $stockItemQty = $this->getMockBuilder(StockItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemRepository->expects($this->once())->method('get')->with(14)->willReturn($stockItemQty);
        $stockItemQty->expects($this->once())->method('getQty')->willReturn(05);
        $this->expectException(Exception::class);
        $this->createRecurringOrderHelper->getStockQty(14, 10);
    }

    /**
     * @throws Exception
     */
    public function testParseTokenData()
    {
        $orderTokenId = '1::braintree::jsikud782l';
        $splitToken = [
            'website_id' => 1,
            'method' => 'braintree',
            'token' => 'jsikud782l'
        ];
        $this->tokenHelper->expects($this->once())->method('splitToken')->with($orderTokenId)->willReturn($splitToken);
        $this->assertEquals($splitToken, $this->createRecurringOrderHelper->parseTokenData($orderTokenId));
    }

    /**
     * @throws Exception
     */
    public function testParseNoTokenData()
    {
        $orderTokenId = '1::braintree::jsikud782l';
        $splitToken = [
            'website_id' => 1,
            'method' => 'braintree',
            'token' => ''
        ];
        $this->tokenHelper->expects($this->once())->method('splitToken')->with($orderTokenId)->willReturn($splitToken);
        $this->expectException(Exception::class);
        $this->assertEquals($splitToken, $this->createRecurringOrderHelper->parseTokenData($orderTokenId));
    }

    /**
     * @throws Exception
     */
    public function testParseNoReturnTokenData()
    {
        $orderTokenId = '1::braintree::jsikud782l';
        $splitToken = [];
        $this->tokenHelper->expects($this->once())->method('splitToken')->with($orderTokenId)->willReturn($splitToken);
        $this->expectException(Exception::class);
        $this->assertEquals($splitToken, $this->createRecurringOrderHelper->parseTokenData($orderTokenId));
    }

    /**
     * @throws Exception
     * @throws LocalizedException
     */
    public function testCheckCustomerExist()
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->setMethods(['setWebsiteId', 'loadByEmail', 'getEntityId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerFactory->expects($this->once())->method('create')->willReturn($customer);
        $customer->expects($this->once())->method('setWebsiteId')->with(1)->willReturnSelf();
        $customer->expects($this->once())->method('loadByEmail')->with('test@test.com')->willReturnSelf();
        $customer->expects($this->once())->method('getEntityId')->willReturn('2');
        $this->assertEquals(
            ['customerId' => 2],
            $this->createRecurringOrderHelper->checkCustomerData('test@test.com', 1)
        );
    }

    /**
     * @throws Exception
     * @throws LocalizedException
     */
    public function testCheckCustomerNotExist()
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->setMethods(['setWebsiteId', 'loadByEmail', 'getEntityId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerFactory->expects($this->once())->method('create')->willReturn($customer);
        $customer->expects($this->once())->method('setWebsiteId')->with(1)->willReturnSelf();
        $customer->expects($this->once())->method('loadByEmail')->with('test@test.com')->willReturnSelf();
        $customer->expects($this->once())->method('getEntityId')->willReturn('');
        $this->expectException(Exception::class);
        $this->assertEquals(
            ['customerId' => ''],
            $this->createRecurringOrderHelper->checkCustomerData('test@test.com', 1)
        );
    }

    /**
     * @throws Exception
     */
    public function testValidateToken()
    {
        $tokenData = [
            'website_id' => 1,
            'method' => 'braintree',
            'token' => 'jsikud782l'
        ];
        $customerId = 3;
        $returnArray = [
            'token' => 'jsikud782l',
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk=',
            'method' => 'braintree'
        ];
        $cardList = [
            'is_active' => 1,
            'payment_method_code' => 'braintree',
            'gateway_token' => 'jsikud782l',
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk='
        ];
        $this->paymentTokenManagement->expects($this->once())->method('getByGatewayToken')->with(
            $tokenData['token'],
            $tokenData['method'],
            $customerId
        )->willReturn($cardList);
        $this->assertEquals($returnArray, $this->createRecurringOrderHelper->validateToken($tokenData, 3));
    }

    /**
     * @throws Exception
     */
    public function testValidateNoActiveToken()
    {
        $tokenData = [
            'website_id' => 1,
            'method' => 'braintree',
            'token' => 'jsikud782l'
        ];
        $customerId = 3;
        $returnArray = [
            'token' => 'jsikud782l',
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk=',
            'method' => 'braintree'
        ];
        $cardList = [
            'is_active' => 0,
            'payment_method_code' => 'braintree',
            'gateway_token' => 'jsikud782l',
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk='
        ];
        $this->paymentTokenManagement->expects($this->once())->method('getByGatewayToken')->with(
            $tokenData['token'],
            $tokenData['method'],
            $customerId
        )->willReturn($cardList);
        $this->expectException(Exception::class);
        $this->assertEquals($returnArray, $this->createRecurringOrderHelper->validateToken($tokenData, 3));
    }

    /**
     * @throws Exception
     */
    public function testValidateNoGatewayToken()
    {
        $tokenData = [
            'website_id' => 1,
            'method' => 'braintree',
            'token' => 'jsikud782l'
        ];
        $customerId = 3;
        $returnArray = [
            'token' => 'jsikud782l',
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk=',
            'method' => 'braintree'
        ];
        $cardList = [
            'is_active' => 1,
            'payment_method_code' => 'braintree',
            'gateway_token' => '',
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk='
        ];
        $this->paymentTokenManagement->expects($this->once())->method('getByGatewayToken')->with(
            $tokenData['token'],
            $tokenData['method'],
            $customerId
        )->willReturn($cardList);
        $this->expectException(Exception::class);
        $this->assertEquals($returnArray, $this->createRecurringOrderHelper->validateToken($tokenData, 3));
    }

    /**
     * @throws Exception
     * @throws LocalizedException
     * @throws CommandException
     */
    public function testCreatePaymentMethodNonce()
    {
        $inputToken = [
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk=',
            'customer_id' => 2
        ];
        $token = [
            'method' => 'braintree',
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk=',
            'customer_id' => 2
        ];
        $paymentMethodNonce = [
            'paymentMethodNonce' => '7f5e6acb-049d-0284-1b66-192u8kjd8723io90'
        ];
        $returnValues = [
            'method' => 'braintree',
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk=',
            'payment_method_nonce' => $paymentMethodNonce['paymentMethodNonce']
        ];
        $this->paymentNonce->expects($this->once())->method('execute')->with($inputToken)->willReturnSelf();
        $this->paymentNonce->expects($this->once())->method('get')->willReturn($paymentMethodNonce);
        $this->assertEquals($returnValues, $this->createRecurringOrderHelper->createPaymentMethodNonce($token, 2));
    }

    /**
     * @throws Exception
     * @throws LocalizedException
     * @throws CommandException
     */
    public function testCreateNoPaymentMethodNonce()
    {
        $inputToken = [
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk=',
            'customer_id' => 2
        ];
        $token = [
            'method' => 'braintree',
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk=',
            'customer_id' => 2
        ];
        $paymentMethodNonce = [
            'paymentMethodNonce' => '7f5e6acb-049d-0284-1b66-192u8kjd8723io90'
        ];
        $returnValues = [
            'method' => 'braintree',
            'public_hash' => 'ajdnkabi2i988dhidakydgkuaguadjugiadk=',
            'payment_method_nonce' => $paymentMethodNonce['paymentMethodNonce']
        ];
        $this->paymentNonce->expects($this->once())->method('execute')->with($inputToken)->willReturnSelf();
        $this->paymentNonce->expects($this->once())->method('get')->willReturn('');
        $this->expectException(Exception::class);
        $this->assertEquals($returnValues, $this->createRecurringOrderHelper->createPaymentMethodNonce($token, 2));
    }

    /**
     * @throws Exception
     */
    public function testAfterPlaceOrder()
    {
        $order = $this->getMockBuilder(Order::class)
            ->setMethods(['setEmailSent', 'getEntityId', 'getIncrementId', 'getCustomerEmail'])
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->once())->method('setEmailSent')->willReturnSelf();
        $order->expects($this->once())->method('getEntityId')->willReturn(300);
        $order->expects($this->once())->method('getIncrementId')->willReturn('1928198182828');
        $order->expects($this->once())->method('getCustomerEmail')->willReturn('test@test.com');
        $this->assertEquals(['orderId' => '1928198182828'], $this->createRecurringOrderHelper->afterPlaceOrder($order));
    }

    /**
     * @throws Exception
     */
    public function testAfterPlaceOrderWithError()
    {
        $order = $this->getMockBuilder(Order::class)
            ->setMethods(['setEmailSent', 'getEntityId' ])
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->once())->method('setEmailSent')->willReturnSelf();
        $order->expects($this->once())->method('getEntityId')->willReturn('');
        $this->expectException(Exception::class);
        $this->createRecurringOrderHelper->afterPlaceOrder($order);
    }

    /**
     * Checks if XML is parsed correctly
     */
    public function testParseXmlArray()
    {
        $content = "<?xml version='1.0' encoding='utf-8'?>
<order>
    <head>
        <orderSourcePartnerId>155</orderSourcePartnerId>
    </head>
    <customer>
        <customerOgId>2</customerOgId>
        <customerPartnerId>2835374276743</customerPartnerId>
    </customer>
    <items>
        <item>
            <qty>1</qty>
            <discount>0</discount>
            <price>299.00</price>
        </item>
    </items>
</order>";

        $orderDataFromXml = (array) simplexml_load_string($content);
        $jsonEncoded = "{\"head\":{\"orderSourcePartnerId\":\"155\"},\"customer\":{\"customerOgId\":\"2\",\"customerPartnerId\":\"2835374276743\"},\"items\":{\"item\":{\"qty\":\"1\",\"discount\":\"0\",\"price\":\"299.00\"}}}";
        $jsonDecoded = [
            'head' => [
                'orderSourcePartnerId' => '155',
            ],
            'customer' => [
                'customerOgId' => '2',
                'customerPartnerId' => '2835374276743',
            ],
            'items' => [
                'item' => [
                    'qty' => '1',
                    'discount' => '0',
                    'price' => '299.00',
                ],
            ],
        ];
        $this->jsonHelper->expects($this->once())->method('jsonEncode')->with($orderDataFromXml)->willReturn($jsonEncoded);
        $this->jsonHelper->expects($this->once())->method('jsonDecode')->with($jsonEncoded)->willReturn($jsonDecoded);
        $this->assertEquals($jsonDecoded, $this->createRecurringOrderHelper->parseXmlToArray($content));
    }
}
