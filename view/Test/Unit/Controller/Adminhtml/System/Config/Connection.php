<?php

namespace Ordergroove\Subscription\Test\Unit\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class Connection extends TestCase
{
    /**
     * @var Connection
     */
    protected $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var ResponseHttp
     */
    protected $response;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $objectManagerMock;

    /**
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonFactory;

    /**
     * @var Sftp|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sftp;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $context = $this->createMock(Context::class);
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getmock();
        $this->response = $this->createMock(ResponseHttp::class);
        $context->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $context->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->jsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->sftp = $this->getMockBuilder(Sftp::class)
            ->disableOriginalConstructor()
            ->setMethods(['open'])
            ->getMock();

        $this->controller = $objectManager->getObject(
            \Ordergroove\Subscription\Controller\Adminhtml\System\Config\Connection::class,
            [
                'context' => $context,
                'jsonFactory' => $this->jsonFactory,
                'sftp' => $this->sftp,
                '_request' => $this->request
            ]
        );
    }

    protected function tearDown()
    {
    }

    public function testExecuteAllEmpty()
    {
        $jsonResult = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['success' => false];
        $this->request->expects($this->once())->method('getParams')->willReturn([
            'host' => '',
            'port' => '',
            'username' => '',
            'password' => ''
        ]);
        $jsonResult->expects($this->once())->method('setData')->with($data)->willReturnSelf();
        $jsonResult->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->jsonFactory->expects($this->once())->method('create')->willReturn($jsonResult);
        $this->assertEquals($jsonResult, $this->controller->execute());
    }

    public function testExecuteHostEmpty()
    {
        $jsonResult = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['success' => false];
        $this->request->expects($this->once())->method('getParams')->willReturn([
            'host' => '',
            'port' => '123',
            'username' => '123',
            'password' => '123'
        ]);
        $jsonResult->expects($this->once())->method('setData')->with($data)->willReturnSelf();
        $jsonResult->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->jsonFactory->expects($this->once())->method('create')->willReturn($jsonResult);
        $this->assertEquals($jsonResult, $this->controller->execute());
    }

    public function testExecutePortEmpty()
    {
        $jsonResult = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['success' => false];
        $this->request->expects($this->once())->method('getParams')->willReturn([
            'host' => '123',
            'port' => '',
            'username' => '123',
            'password' => '123'
        ]);
        $jsonResult->expects($this->once())->method('setData')->with($data)->willReturnSelf();
        $jsonResult->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->jsonFactory->expects($this->once())->method('create')->willReturn($jsonResult);
        $this->assertEquals($jsonResult, $this->controller->execute());
    }

    public function testExecuteUsernameEmpty()
    {
        $jsonResult = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['success' => false];
        $this->request->expects($this->once())->method('getParams')->willReturn([
            'host' => '123',
            'port' => '123',
            'username' => '',
            'password' => '123'
        ]);
        $jsonResult->expects($this->once())->method('setData')->with($data)->willReturnSelf();
        $jsonResult->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->jsonFactory->expects($this->once())->method('create')->willReturn($jsonResult);
        $this->assertEquals($jsonResult, $this->controller->execute());
    }

    public function testExecutePasswordEmpty()
    {
        $jsonResult = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['success' => false];
        $this->request->expects($this->once())->method('getParams')->willReturn([
            'host' => '123',
            'port' => '123',
            'username' => '123',
            'password' => ''
        ]);
        $jsonResult->expects($this->once())->method('setData')->with($data)->willReturnSelf();
        $jsonResult->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->jsonFactory->expects($this->once())->method('create')->willReturn($jsonResult);
        $this->assertEquals($jsonResult, $this->controller->execute());
    }

    public function testExecuteBadCredentials()
    {
        $jsonResult = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['success' => false];
        $sftpData = [
            'host' => 'host',
            'port' => 'port',
            'username' => 'Floopy Floppson',
            'password' => '123'
        ];
        $this->request->expects($this->once())->method('getParams')->willReturn($sftpData);
        $jsonResult->expects($this->once())->method('setData')->with($data)->willReturnSelf();
        $jsonResult->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->jsonFactory->expects($this->once())->method('create')->willReturn($jsonResult);
        $this->sftp->expects($this->once())->method('open')->with($sftpData)
            ->willThrowException(new \Exception("Test"));
        $this->assertEquals($jsonResult, $this->controller->execute());
    }

    public function testExecuteGoodCredentials()
    {
        $jsonResult = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['success' => true];
        $sftpData = [
            'host' => 'host',
            'port' => 'port',
            'username' => 'Floopy Floppson',
            'password' => '123'
        ];
        $this->request->expects($this->once())->method('getParams')->willReturn($sftpData);
        $jsonResult->expects($this->once())->method('setData')->with($data)->willReturnSelf();
        $jsonResult->expects($this->once())->method('setHttpResponseCode')->with(200)->willReturnSelf();
        $this->jsonFactory->expects($this->once())->method('create')->willReturn($jsonResult);
        $this->sftp->expects($this->once())->method('open')->with($sftpData)->willReturnSelf();
        $this->assertEquals($jsonResult, $this->controller->execute());
    }
}
