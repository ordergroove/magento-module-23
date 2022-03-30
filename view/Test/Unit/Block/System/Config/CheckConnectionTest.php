<?php

namespace Ordergroove\Subscription\Test\Unit\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Ordergroove\Subscription\Block\System\Config\CheckConnection;
use PHPUnit\Framework\TestCase;

class CheckConnectionTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $objectManagerMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var CheckConnection
     */
    protected $block;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $this->layout = $this->createMock(LayoutInterface::class);
        $context = $this->createMock(Context::class);
        $context->expects($this->once())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilder);

        $this->block = $objectManager->getObject(
            CheckConnection::class,
            [
                'context' => $context,
                '_layout' => $this->layout
            ]
        );
    }

    protected function tearDown()
    {
    }

    public function testGetAjaxUrl()
    {
        $this->urlBuilder->expects($this->once())->method('getUrl')
            ->with('ordergroove_checkconnection/system_config/connection')
            ->willReturn('ordergroove.test/ordergroove_checkconnection/system_config/connection');
        $this->assertEquals(
            "ordergroove.test/ordergroove_checkconnection/system_config/connection",
            $this->block->getAjaxUrl()
        );
    }

    public function testGetButtonHtml()
    {
        $button = $this->getMockBuilder(Button::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layout->expects($this->once())->method('createBlock')->with('Magento\Backend\Block\Widget\Button')
            ->willReturn($button);
        $button->expects($this->once())->method('setData')->with([
            'id' => 'check_connection_button',
            'label' => __('Check Connectivity'),
        ])->willReturnSelf();
        $button->expects($this->once())->method('toHtml')->willReturn('<div>Test</div>');
        $this->assertEquals('<div>Test</div>', $this->block->getButtonHtml());
    }
}
