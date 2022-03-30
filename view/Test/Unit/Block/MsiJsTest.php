<?php

namespace Ordergroove\Subscription\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Ordergroove\Subscription\Model\Config\UrlBuilder;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as PHPUnit_Framework_MockObject_MockObjectAlias;
use Ordergroove\Subscription\Block\MsiJs;

/**
 * Class MsiJsTest
 * @package Ordergroove\Subscription\Test\Unit\Block
 */
class MsiJsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var UrlBuilder|PHPUnit_Framework_MockObject_MockObjectAlias
     */
    private $urlBuilder;

    /**
     * @var MsiJs
     */
    private $msiJs;

    /**
     * @var Context
     */
    protected $context;


    /**
     * setup mocks
     *
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

        $this->msiJs = $this->objectManager->getObject(
            MsiJs::class,
            [
                'context' => $this->context,
                'urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetMsiJsUrl()
    {
        $this->urlBuilder->expects($this->once())->method("getPublicIdUrl")->willReturn("https://test.com");
        $this->assertEquals("https://test.com", $this->msiJs->getMsiJsUrl());
    }
}

