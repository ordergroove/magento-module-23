<?php


namespace Ordergroove\Subscription\Test\Unit\Model\Config;


use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Model\Config\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

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

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider = $this->objectManager->getObject(
            ConfigProvider::class,
            [
                'checkoutSession' => $this->session,
                'configHelper' => $this->configHelper
            ]
        );
    }

    public function testGetConfig()
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $items = [
            $this->getMockBuilder(CartItemInterface::class)
                ->disableOriginalConstructor()
                ->setMethods(array_merge(get_class_methods(CartItemInterface::class), ["getProductId", "getOptionByCode"]))
                ->getMock(),

            $this->getMockBuilder(CartItemInterface::class)
                ->disableOriginalConstructor()
                ->setMethods(array_merge(get_class_methods(CartItemInterface::class), ["getProductId", "getOptionByCode"]))
                ->getMock()
        ];

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(Product::class), ["getProductId"]))
            ->getMock();

        $this->configHelper->expects($this->exactly(2))->method('isEnabled')->willReturn(true);
        $this->session->expects($this->once())->method('getQuote')->willReturn($quote);
        $quote->expects($this->once())->method('getItems')->willReturn($items);
        $items[0]->expects($this->once())->method('getOptionByCode')->with('simple_product')->willReturn(null);
        $items[0]->expects($this->once())->method('getProductId')->willReturn("123");
        $items[1]->expects($this->once())->method('getOptionByCode')->with('simple_product')->willReturn($product);
        $product->expects($this->once())->method('getProductId')->willReturn('101');

        $this->assertEquals(["ordergroove_product_ids" => ["123","101"],
            "isOrdergrooveModuleEnabled" => true], $this->configProvider->getConfig());
    }

    public function testGetConfigNotEnabled()
    {
        $this->configHelper->expects($this->exactly(2))->method('isEnabled')->willReturn(false);
        $this->assertEquals(["isOrdergrooveModuleEnabled" => false], $this->configProvider->getConfig());
    }
}

