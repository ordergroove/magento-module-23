<?php

namespace Ordergroove\Subscription\Test\Unit\Controller\Msi;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ordergroove\Subscription\Controller\Msi\Index;

/**
 * IndexTest Class
 */
class IndexTest extends TestCase
{

    /**
     * @var Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Page
     */
    protected $resultPage;

    /**
     * @var Index
     */
    protected $index;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactory;

    /**
     * setup mocks
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->response = $this->createMock(Http::class);

        $context = $this->createMock(Context::class);
        $context->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $context->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->index = $objectManagerHelper->getObject(
            Index::class,
            [
                'context' => $context,
                'resultPageFactory' => $this->resultPageFactory
            ]
        );
    }

    public function testExecute()
    {
        $title = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $title->expects($this->once())
            ->method('set')
            ->with('Ordergroove Subscriptions');

        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->once())
            ->method('getTitle')
            ->willReturn($title);

        $page = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $page->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);

        $this->resultPageFactory->expects($this->once())
            ->method('create')
            ->willReturn($page);
        $result = $this->index->execute();
        $this->assertInstanceOf(Page::class, $result);
    }
}
