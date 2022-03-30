<?php

namespace Ordergroove\Subscription\Test\Unit\Helper\ProductSync;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Helper\ProductSync\SftpHelper;
use Ordergroove\Subscription\Logger\ProductSync\Error\Logger as ErrorLogger;
use Ordergroove\Subscription\Logger\ProductSync\Info\Logger as InfoLogger;

use PHPUnit\Framework\TestCase;

class SftpHelperTest extends TestCase
{
    /**
     * @var SftpHelper
     */
    protected $sftpHelper;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var Sftp|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sftp;

    /**
     * @var ErrorLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $errorLogger;

    /**
     * @var InfoLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $infoLogger;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sftp = $this->getMockBuilder(Sftp::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->errorLogger = $this->getMockBuilder(ErrorLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->infoLogger = $this->getMockBuilder(InfoLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sftpHelper = $this->objectManager->getObject(
            SftpHelper::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'sftp' => $this->sftp,
                'errorLogger' => $this->errorLogger,
                'infoLogger' => $this->infoLogger
            ]
        );
    }

    protected function tearDown()
    {
    }

    public function testGetSftpFilename()
    {
        $this->assertEquals("/incoming/123.Products.xml", $this->sftpHelper->getSftpFilename("123"));
    }

    public function testEmptyUsername()
    {
        $this->scopeConfig->expects($this->exactly(5))->method("getValue")
            ->willReturnOnConsecutiveCalls("1", "2", "", "4", "5");
        $this->sftpHelper->sendProductFeed(new \SimpleXMLElement("<test/>"), "123");
    }

    public function testEmptyPassword()
    {
        $this->scopeConfig->expects($this->exactly(5))->method("getValue")
            ->willReturnOnConsecutiveCalls("1", "2", "3", "", "5");
        $this->sftpHelper->sendProductFeed(new \SimpleXMLElement("<test/>"), "123");
    }

    public function testSftpError()
    {
        $this->scopeConfig->expects($this->exactly(5))->method("getValue")
            ->willReturnOnConsecutiveCalls("1", "2", "3", "4", "5");
        $this->sftp->expects($this->once())->method("open")->willThrowException(new \Exception());
        $this->errorLogger->expects($this->once())->method("error");
        $this->sftpHelper->sendProductFeed(new \SimpleXMLElement("<test/>"), "123");
    }

    public function testSftpSuccess()
    {
        $this->scopeConfig->expects($this->exactly(5))->method("getValue")
            ->willReturnOnConsecutiveCalls("1", "2", "3", "4", "5");
        $this->sftp->expects($this->once())->method("open");
        $this->sftp->expects($this->once())->method("write")
            ->with("/incoming/5.Products.xml", "<?xml version=\"1.0\"?>\n<test/>\n");
        $this->infoLogger->expects($this->once())->method("info");
        $this->sftpHelper->sendProductFeed(new \SimpleXMLElement("<test/>"), "123");
    }
}
