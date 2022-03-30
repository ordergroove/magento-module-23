<?php

namespace Ordergroove\Subscription\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Helper\UrlBuilderHelper;
use PHPUnit\Framework\TestCase;

class UrlBuilderHelperTest extends TestCase
{

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UrlBuilderHelper
     */
    private $urlBuilderHelper;

    /**
     * @var ConfigHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHelper;

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilderHelper = $this->objectManager->getObject(
            UrlBuilderHelper::class,
            [
                'configHelper' => $this->configHelper
            ]
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetUrlHeadingWebsitePassed()
    {
        $this->configHelper->expects($this->once())->method("getStaging")->with("1")->willReturn(true);
        $this->assertEquals("https://staging.", $this->urlBuilderHelper->getUrlHeading("1"));
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetUrlHeading()
    {
        $this->configHelper->expects($this->once())->method("getStaging")->with("")->willReturn(true);
        $this->assertEquals("https://staging.", $this->urlBuilderHelper->getUrlHeading());
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetUrlHeadingNotStaging()
    {
        $this->configHelper->expects($this->once())->method("getStaging")->with("")->willReturn(false);
        $this->assertEquals("https://", $this->urlBuilderHelper->getUrlHeading());
    }
}
