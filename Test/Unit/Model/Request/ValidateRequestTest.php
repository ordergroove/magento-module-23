<?php

namespace Ordergroove\Subscription\Test\Unit\Model\Request;

use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Model\Request\ValidateRequest;
use PHPUnit\Framework\TestCase;

/**
 * Class ValidateRequestTest
 * @package Ordergroove\Subscription\Test\Unit\Model\Request
 */
class ValidateRequestTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ValidateRequest
     */
    protected $validateRequest;

    /**
     * @var Http
     */
    protected $request;

    /**
     * Set Up
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->request = $this->getMockBuilder(Http::class)
            ->setMethods(['isPost', 'getServerValue', 'getHeader', 'getContent'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->validateRequest = $this->objectManager->getObject(
            ValidateRequest::class,
            [
                'request' => $this->request
            ]
        );
    }

    public function testIfPostRequestData()
    {
        $this->request->expects($this->once())->method('isPost')->willReturn(false);
        $this->assertEquals(['errorMsg' => 'Wrong method request, Please try again'], $this->validateRequest->checkPostRequestData($this->request));
        $this->request->expects($this->never())->method('getServerValue');
        $this->request->expects($this->never())->method('getHeader');
        $this->request->expects($this->never())->method('getContent');
    }

    public function testIfServerValueIsNotAvailable()
    {
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        $this->request->expects($this->once())->method('getServerValue')->willReturn(false);
        $this->assertEquals(['errorMsg' => 'Authentication failed. Authorization Header is missing'], $this->validateRequest->checkPostRequestData($this->request));
        $this->request->expects($this->never())->method('getHeader');
        $this->request->expects($this->never())->method('getContent');
    }

    public function testIfHeaderIsNoXml()
    {
        $contentType = 'application/xml';
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        $this->request->expects($this->once())->method('getServerValue')->willReturn(true);
        $this->request->expects($this->once())->method('getHeader')->with('Content-Type')->willReturn($contentType);
        $this->assertSame('application/xml', $contentType);
        $this->assertNotEquals(['errorMsg' => 'Invalid content type has been received'], $this->validateRequest->checkPostRequestData($this->request));
        $this->request->expects($this->never())->method('getContent');
    }

    public function testIfContentHasNoData()
    {
        $contentType = 'application/xml';
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        $this->request->expects($this->once())->method('getServerValue')->willReturn(true);
        $this->request->expects($this->once())->method('getHeader')->with('Content-Type')->willReturn($contentType);
        $this->assertSame('application/xml', $contentType);
        $this->request->expects($this->once())->method('getContent')->willReturn([]);
        $this->assertEquals(['errorMsg' => 'No data to process'], $this->validateRequest->checkPostRequestData($this->request));
    }

    public function testIfNoPostRequestData()
    {
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        $this->request->expects($this->once())->method('getServerValue')->willReturn(true);
        $this->request->expects($this->once())->method('getHeader')->willReturn(true);
        $this->request->expects($this->once())->method('getContent')->willReturn(true);
        $this->assertEquals(true, $this->validateRequest->checkPostRequestData($this->request));
    }

    public function testIfServerValueIsAvailable()
    {
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        $this->request->expects($this->once())->method('getServerValue')->willReturn(true);
        $this->assertNotEquals(true, $this->validateRequest->checkPostRequestData($this->request));
    }

    public function testIfHeaderIsXml()
    {
        $contentType = 'application/xml';
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        $this->request->expects($this->once())->method('getServerValue')->willReturn(true);
        $this->request->expects($this->once())->method('getHeader')->with('Content-Type')->willReturn($contentType);
        $this->assertNotSame('application/json', $contentType);
        $this->assertNotEquals(true, $this->validateRequest->checkPostRequestData($this->request));
    }

    public function testIfContentHasData()
    {
        $contentType = 'application/xml';
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        $this->request->expects($this->once())->method('getServerValue')->willReturn(true);
        $this->request->expects($this->once())->method('getHeader')->with('Content-Type')->willReturn($contentType);
        $this->assertSame('application/xml', $contentType);
        $this->request->expects($this->once())->method('getContent')->willReturn(['someData' => 'Some Message']);
        $this->assertEquals(true, $this->validateRequest->checkPostRequestData($this->request));
    }
}

