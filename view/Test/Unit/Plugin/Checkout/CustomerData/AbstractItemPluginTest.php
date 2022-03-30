<?php

namespace Ordergroove\Subscription\Test\Unit\Plugin\Checkout\CustomerData;

use Magento\Catalog\Model\Product;
use Magento\Checkout\CustomerData\AbstractItem;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Plugin\Checkout\CustomerData\AbstractItemPlugin;
use PHPUnit\Framework\TestCase;

class AbstractItemPluginTest extends TestCase
{

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHelper;

    /**
     * @var AbstractItemPlugin
     */
    private $abstractItemPlugin;

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(get_class_methods(ConfigHelper::class))
            ->getMock();

        $this->abstractItemPlugin = $this->objectManager->getObject(
            AbstractItemPlugin::class,
            [
                'configHelper' => $this->configHelper
            ]
        );
    }

    public function testAfterGetItemData()
    {
        $this->configHelper->expects($this->once())->method("isEnabled")->willReturn(true);
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(Product::class), ["getProductId"]))
            ->getMock();
        $product->expects($this->once())->method('getProductId')->willReturn("105");

        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $item->expects($this->once())->method('getProductType')->willReturn("configurable");
        $item->expects($this->once())->method('getOptionByCode')->with("simple_product")->willReturn($product);

        $abstractItem = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(["simple_product_id" => "105"], $this->abstractItemPlugin->afterGetItemData($abstractItem, [], $item));
    }

    public function testAfterGetItemDataNotConfigurable()
    {
        $this->configHelper->expects($this->once())->method("isEnabled")->willReturn(true);

        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $item->expects($this->once())->method('getProductType')->willReturn("simple");

        $abstractItem = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals([], $this->abstractItemPlugin->afterGetItemData($abstractItem, [], $item));
    }

    public function testAfterGetItemDataNotEnabled()
    {
        $this->configHelper->expects($this->once())->method("isEnabled")->willReturn(false);

        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $item->expects($this->never())->method('getProductType');

        $abstractItem = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals([], $this->abstractItemPlugin->afterGetItemData($abstractItem, [], $item));
    }
}
