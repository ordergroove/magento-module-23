<?php

namespace Ordergroove\Subscription\Test\Unit\Helper;

use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Helper\ProductSync\ProductSync;
use Ordergroove\Subscription\Helper\ProductSync\SftpHelper;
use Ordergroove\Subscription\Helper\ProductSync\XmlHelper;
use PHPUnit\Framework\TestCase;

class ProductSyncTest extends TestCase
{
    /**
     * @var ProductSync
     */
    protected $productSync;

    /**
     * @var XmlHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $xmlHelper;

    /**
     * @var SftpHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sftpHelper;
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->productSync = $this->objectManager->getObject(ProductSync::class);
        $this->xmlHelper = $this->getMockBuilder(XmlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sftpHelper = $this->getMockBuilder(SftpHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productSync = $this->objectManager->getObject(
            ProductSync::class,
            [
                'xmlHelper' => $this->xmlHelper,
                'sftpHelper' => $this->sftpHelper
            ]
        );
    }

    protected function tearDown()
    {
    }

    public function testProcessSftpSyncs()
    {
        $this->xmlHelper->expects($this->once())->method('getWebsiteIds')->willReturn(['0','1']);
        $this->xmlHelper->expects($this->exactly(2))
            ->method('createWebsiteProductsXml')->withConsecutive(['0'], ['1'])
            ->willReturnOnConsecutiveCalls('', '');
        $this->sftpHelper->expects($this->exactly(2))->method('sendProductFeed');
        $this->productSync->processProductSync();
    }
}
