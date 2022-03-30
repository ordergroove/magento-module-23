<?php


namespace Ordergroove\Subscription\Test\Unit\Model\Config;


use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Helper\UrlBuilderHelper;
use Ordergroove\Subscription\Model\Config\UrlBuilder;
use PHPUnit\Framework\TestCase;

class UrlBuilderTest extends TestCase
{

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var UrlBuilderHelper|\PHPUnit_Framework_MockObject_MockObject
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

        $this->urlBuilderHelper = $this->getMockBuilder(UrlBuilderHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->objectManager->getObject(
            UrlBuilder::class,
            [
                'configHelper' => $this->configHelper,
                'urlBuilderHelper' => $this->urlBuilderHelper
            ]
        );
    }

    /**
     * testGetPublicUrl
     * @return void
     */
    public function testGetPublicIdUrl()
    {
        $this->configHelper->expects($this->once())->method("getPublicId")->with("1")->willReturn("123");
        $this->urlBuilderHelper->expects($this->once())->method("getUrlHeading")->with("1")->willReturn("https://");
        $this->assertEquals("https://static.ordergroove.com/123/main.js", $this->urlBuilder->getPublicIdUrl("1", "/main.js"));
    }

    /**
     * testGetPurchasePostUrl
     * @return void
     */
    public function testGetPurchasePostUrl()
    {
        $this->urlBuilderHelper->expects($this->once())->method("getUrlHeading")->with("1")->willReturn("https://");
        $this->assertEquals("https://sc.ordergroove.com/subscription/create", $this->urlBuilder->getPurchasePostUrl("1"));
    }
  
    /**
     * testGetCustomerUpdateUrl
     * @return void
     */
    public function testGetCustomerUpdateUrl()
    {
        $this->urlBuilderHelper->expects($this->exactly(2))->method("getUrlHeading")->with("1")->willReturn("https://");
        $this->assertEquals("https://restapi.ordergroove.com/customers/123/set_contact_details", $this->urlBuilder->getCustomerUpdateUrl("123", "1"));
        $this->assertEquals("https://restapi.ordergroove.com/customers/12345/set_contact_details", $this->urlBuilder->getCustomerUpdateUrl("12345", "1"));

    }
}
