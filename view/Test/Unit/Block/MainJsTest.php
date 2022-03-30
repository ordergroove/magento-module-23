<?php

namespace Ordergroove\Subscription\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Block\MainJs;
use Ordergroove\Subscription\Model\Config\UrlBuilder;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Element\Template\Context;

class MainJsTest extends TestCase
{

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var MainJs
     */
    private $mainJs;

    /**
     * @var Context
     */
    private $context;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->urlBuilder = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mainJs = $this->objectManager->getObject(
            MainJs::class,
            [
                'context' => $this->context,
                'urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetMainJsUrl()
    {
        $this->urlBuilder->expects($this->once())->method("getPublicIdUrl")->willReturn("https://test.com");
        $this->assertEquals("https://test.com", $this->mainJs->getMainJsUrl());
    }
}
