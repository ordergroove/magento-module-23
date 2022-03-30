<?php
declare(strict_types=1);

namespace Ordergroove\Subscription\Test\Unit\Controller\Subscription;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Controller\Subscription\PlaceOrder;
use Ordergroove\Subscription\Exception\RecurringOrderException;
use Ordergroove\Subscription\Helper\RecurringOrderHelper\CreateRecurringOrderHelper;
use Ordergroove\Subscription\Logger\RecurringOrder\Error\Logger as ErrorLogger;
use Ordergroove\Subscription\Model\Authentication\ValidateAuthorization;
use Ordergroove\Subscription\Model\RecurringOrder\CreateRecurringOrder;
use Ordergroove\Subscription\Model\Request\ValidateRequest;
use PHPUnit\Framework\TestCase;

/**
 * Class PlaceOrderTest
 * @package Ordergroove\Subscription\Test\Unit\Controller\Subscription
 */
class PlaceOrderTest extends TestCase
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var ErrorLogger
     */
    protected $errorLogger;

    /**
     * @var ConvertArray
     */
    protected $convertArray;

    /**
     * @var CreateRecurringOrder
     */
    protected $createRecurringOrder;

    /**
     * @var CreateRecurringOrderHelper
     */
    protected $createRecurringOrderHelper;

    /**
     * @var ValidateAuthorization
     */
    protected $validateAuthorizationHelper;

    /**
     * @var ValidateRequest
     */
    protected $validateRequest;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PlaceOrder
     */
    protected $placeOrder;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->context = $this->createMock(Context::class);
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Http::class)
            ->setMethods(['getServerValue', 'getContent'])
            ->disableOriginalConstructor()->getmock();
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->resultRawFactory = $this->getMockBuilder(RawFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->createRecurringOrder = $this->getMockBuilder(CreateRecurringOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->errorLogger = $this->getMockBuilder(ErrorLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->convertArray = $this->getMockBuilder(ConvertArray::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->createRecurringOrderHelper = $this->getMockBuilder(CreateRecurringOrderHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validateAuthorizationHelper = $this->getMockBuilder(ValidateAuthorization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validateRequest = $this->getMockBuilder(ValidateRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->placeOrder = $objectManager->getObject(
            PlaceOrder::class,
            [
                'context' => $this->context,
                'resultRawFactory' => $this->resultRawFactory,
                'createRecurringOrder' => $this->createRecurringOrder,
                'errorLogger' => $this->errorLogger,
                'convertArray' => $this->convertArray,
                'createRecurringOrderHelper' => $this->createRecurringOrderHelper,
                'validateAuthorizationHelper' => $this->validateAuthorizationHelper,
                'validateRequest' => $this->validateRequest,
                'jsonHelper' => $this->jsonHelper
            ]
        );
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteSuccess()
    {
        $data = $this->dataProvider();
        $decoded = [
            'public_id' => 'acded2f6db3d11ea988dbc764e10b970',
            'sig' => 'rkNV10GD6ifUSUWa30Z9UE1Pt2lh14iXNSgroZCWbVA=',
            'ts' => '1605230097',
            'sig_field' => '2',
        ];
        $resultRawFactory = $this->getMockBuilder(RawFactory::class)
            ->setMethods(['setHeader', 'setContents'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRawFactory->expects($this->once())->method('create')->willReturn($resultRawFactory);
        $this->validateRequest->expects($this->once())->method('checkPostRequestData')->with($this->request)->willReturn(true);
        $this->request->expects($this->once())->method('getServerValue')->willReturn($data['authorization']);
        $this->jsonHelper->expects($this->once())->method('jsonDecode')->with($data['authorization'])->willReturn($decoded);
        $this->validateAuthorizationHelper->expects($this->once())->method('validateAuthentication')->with(
            '2',
            'rkNV10GD6ifUSUWa30Z9UE1Pt2lh14iXNSgroZCWbVA=',
            '1605230097'
        )->willReturn(true);
        $this->request->expects($this->once())->method('getContent')->willReturn($data['inputXml']);
        $orderDataFromXml = simplexml_load_string($data['inputXml'], null, LIBXML_NOCDATA);
        $this->createRecurringOrderHelper->expects($this->once())->method('parseXmlToArray')->with($orderDataFromXml->asXML())->willReturn($data['content']);
        $this->createRecurringOrder->expects($this->once())->method('placeRecurringOrder')->with($data['content'])->willReturn($data['result']);
        $this->convertArray->expects($this->once())->method('assocToXml')->with($data['result'], 'order')->willReturn($data['xmlObjectSuccess']);
        $resultRawFactory->expects($this->once())->method('setHeader')->with('Content-Type', 'text/xml')->willReturnSelf();
        $resultRawFactory->expects($this->once())->method('setContents')->with($data['xmlObjectSuccess']->asXml())->willReturnSelf();
        $this->placeOrder->execute();
    }

    public function dataProvider()
    {
        $xmlResponseSuccess = "<?xml version='1.0' encoding='utf-8' standalone='yes'?>
<order>
    <code>SUCCESS</code>
	<orderId>121999990101</orderId>
</order>";
        $xmlResponseError = "<?xml version='1.0' encoding='utf-8' standalone='yes'?>
<order>
    <code>ERROR</code>
    <errorCode>020</errorCode>
	 <errorMsg>Some Error</errorMsg>
</order>";
        return [
            'inputXml' => "<?xml version='1.0' encoding='utf-8'?>
<order>
    <head>
        <orderOgId>11111</orderOgId>
        <orderOgDate><![CDATA[2020-12-17]]></orderOgDate>
    </head>
    <customer>
        <customerOgId>2</customerOgId>
        <customerPartnerId><![CDATA[7]]></customerPartnerId>
    </customer>
    <items>
        <item>
            <qty>1</qty>
            <sku><![CDATA[WJ01-L-Blue]]></sku>
            <name><![CDATA[Stellar Solar Jacket-S-Blue]]></name>
        </item>
    </items>
</order>",
            'content' => [
                'order' => [
                    'head' => [
                        'orderOgId' => '11111',
                        'orderOgDate' => '2020-12-17'
                    ],
                    'customer' => [
                        'customerOgId' => '2',
                        'customerPartnerId' => '7'
                    ],
                    'items' => [
                        'item' => [
                            'qty' => '1',
                            'sku' => 'WJ01-L-Blue',
                            'name' => 'Stellar Solar Jacket-S-Blue'
                        ],
                    ],
                ],
            ],
            'response' => [
                'orderId' => '121999990101'
            ],
            'result' => ['code' => 'SUCCESS', 'orderId' => '121999990101'],
            'errorResult' => ['code' => 'ERROR',
                'errorCode' => '020',
                'errorMsg' => 'Some error message'],
            'xmlResponseSuccess' => $xmlResponseSuccess,
            'xmlObjectSuccess' => simplexml_load_string($xmlResponseSuccess),
            'xmlResponseError' => $xmlResponseError,
            'xmlObjectError' => simplexml_load_string($xmlResponseError),
            'authorization' => '{"public_id":"acded2f6db3d11ea988dbc764e10b970","sig":"rkNV10GD6ifUSUWa30Z9UE1Pt2lh14iXNSgroZCWbVA=","ts":"1605230097","sig_field":"2"}'
        ];
    }

    public function testExecuteGenericException()
    {
        $data = $this->dataProvider();
        $decoded = [
            'public_id' => 'acded2f6db3d11ea988dbc764e10b970',
            'sig' => 'rkNV10GD6ifUSUWa30Z9UE1Pt2lh14iXNSgroZCWbVA=',
            'ts' => '1605230097',
            'sig_field' => '2',
        ];
        $resultRawFactory = $this->getMockBuilder(RawFactory::class)
            ->setMethods(['setHeader', 'setContents'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRawFactory->expects($this->once())->method('create')->willReturn($resultRawFactory);
        $this->validateRequest->expects($this->once())->method('checkPostRequestData')->with($this->request)->willReturn(true);
        $this->request->expects($this->once())->method('getServerValue')->willReturn($data['authorization']);
        $this->jsonHelper->expects($this->once())->method('jsonDecode')->with($data['authorization'])->willReturn($decoded);
        $this->validateAuthorizationHelper->expects($this->once())->method('validateAuthentication')->with(
            '2',
            'rkNV10GD6ifUSUWa30Z9UE1Pt2lh14iXNSgroZCWbVA=',
            '1605230097'
        )->willReturn(true);
        $this->request->expects($this->once())->method('getContent')->willReturn($data['inputXml']);
        $orderDataFromXml = simplexml_load_string($data['inputXml'], null, LIBXML_NOCDATA);
        $this->createRecurringOrderHelper->expects($this->once())->method('parseXmlToArray')->with($orderDataFromXml->asXML())->willReturn($data['content']);
        $this->createRecurringOrder->expects($this->once())->method('placeRecurringOrder')->with($data['content'])->willReturn($data['result']);
        $this->convertArray->expects($this->once())->method('assocToXml')->with($data['result'], 'order')->willReturn($data['xmlObjectError']);
        $resultRawFactory->expects($this->once())->method('setHeader')->with('Content-Type', 'text/xml')->willReturnSelf();
        $resultRawFactory->expects($this->once())->method('setContents')->with($data['xmlObjectError']->asXml())->willReturnSelf();
        $this->placeOrder->execute();
    }

    public function testExecuteRecurringOrderException()
    {
        $data = $this->dataProvider();
        $decoded = [
            'public_id' => 'acded2f6db3d11ea988dbc764e10b970',
            'sig' => 'rkNV10GD6ifUSUWa30Z9UE1Pt2lh14iXNSgroZCWbVA=',
            'ts' => '1605230097',
            'sig_field' => '2',
        ];
        $resultRawFactory = $this->getMockBuilder(RawFactory::class)
            ->setMethods(['setHeader', 'setContents'])
            ->disableOriginalConstructor()
            ->getMock();
        $exception = new RecurringOrderException(__("This is a test"), new \Exception("Hello"), "123");
        $this->resultRawFactory->expects($this->once())->method('create')->willReturn($resultRawFactory);
        $this->validateRequest->expects($this->once())->method('checkPostRequestData')->with($this->request)->willThrowException($exception);
        $this->errorLogger->expects($this->once())->method("error");
        $this->convertArray->expects($this->once())->method('assocToXml')->with([
            'code' => 'ERROR',
            'errorCode' => 123,
            'errorMsg' => 'This is a test'
        ], 'order')->willReturn($data['xmlObjectError']);
        $resultRawFactory->expects($this->once())->method('setHeader')->with('Content-Type', 'text/xml')->willReturnSelf();
        $resultRawFactory->expects($this->once())->method('setContents')->with($data['xmlObjectError']->asXml())->willReturnSelf();
        $this->placeOrder->execute();
    }
}
