<?php

namespace Ordergroove\Subscription\Test\Unit\Helper\PurchasePost;

use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Helper\PurchasePost\SendPurchasePostData;
use Ordergroove\Subscription\Logger\PurchasePost\Error\Logger as ErrorLogger;
use Ordergroove\Subscription\Logger\PurchasePost\Info\Logger as InfoLogger;
use Ordergroove\Subscription\Model\Config\UrlBuilder;
use Ordergroove\Subscription\Model\Signature\Signature;
use PHPUnit\Framework\TestCase;

/**
 * Class SendPurchasePostDataTest
 * @package Ordergroove\Subscription\Test\Unit\Helper\PurchasePost
 */
class SendPurchasePostDataTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var SendPurchasePostData|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sendPurchasePostData;
    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;
    /**
     * @var UrlBuilder
     */
    private $urlBuilder;
    /**
     * @var Signature
     */
    private $signature;
    /**
     * @var ConfigHelper
     */
    private $configHelper;
    /**
     * @var ErrorLogger
     */
    private $errorLogger;
    /**
     * @var InfoLogger
     */
    private $infoLogger;

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->httpClientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->setMethods(['create', 'setUri', 'setMethod', 'setHeaders', 'setRawdata', 'request', 'getMessage'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->signature = $this->getMockBuilder(Signature::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->errorLogger = $this->getMockBuilder(ErrorLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->infoLogger = $this->getMockBuilder(InfoLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sendPurchasePostData = $this->objectManager->getObject(
            SendPurchasePostData::class,
            [
                'httpClientFactory' => $this->httpClientFactory,
                'urlBuilder' => $this->urlBuilder,
                'signature' => $this->signature,
                'configHelper' => $this->configHelper,
                'errorLogger' => $this->errorLogger,
                'infoLogger' => $this->infoLogger
            ]
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function testSendPurchasePostToOrdergroove()
    {
        $datamap = [
            'user' => [
                'user_id' => '5'
            ],
            'array' => [
                'otherdata' => 'something'
            ]
        ];
        $websiteId = 1;
        $url = 'https://testurl.com';
        $sigData = [
            'signature' => 'hhasksjjj@yyao=',
            'timestamp' => 12133213444,
            'field' => 2,
        ];
        $postHeader = [
            'public_id' => 'auisuikhwlasoadjdfngiiioooo',
            'ts' => 12133213444,
            'sig_field' => 2,
            'sig' => 'hhasksjjj@yyao='
        ];
        $sendPostdata = 'create_request=' . json_encode($datamap);
        $this->httpClientFactory->expects($this->once())->method('create')->willReturnSelf();
        $this->urlBuilder->expects($this->once())->method('getPurchasePostUrl')->with($websiteId)->willReturn($url);
        $this->httpClientFactory->expects($this->once())->method('setUri')->with($url)->willReturnSelf();
        $this->httpClientFactory->expects($this->once())->method('setMethod')->willReturnSelf();
        $this->httpClientFactory->expects($this->exactly(3))->method('setHeaders')->willReturnSelf();
        $this->configHelper->expects($this->once())->method('getPublicId')->with($websiteId)->willReturn($postHeader['public_id']);
        $this->signature->expects($this->once())->method('createSignature')->with(5, 1)->willReturn($sigData);
        $this->httpClientFactory->expects($this->once())->method('setRawData')->with($sendPostdata)->willReturnSelf();
        $this->httpClientFactory->expects($this->once())->method('request')->willReturnSelf();
        $this->httpClientFactory->expects($this->once())->method('getMessage')->willReturnSelf();
        $this->sendPurchasePostData->sendPurchasePostToOrdergroove($datamap, $websiteId);
    }
}
