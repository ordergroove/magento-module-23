<?php

namespace Ordergroove\Subscription\Test\Unit\Block\Catalog;

use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Ordergroove\Subscription\Block\Catalog\GetProductData;
use PHPUnit\Framework\TestCase;
use Ordergroove\Subscription\Helper\ProductPageHelper;

/**
 * Class GetProductDataTest
 * @package Ordergroove\Subscription\Test\Unit\Block\Catalog
 */
class GetProductDataTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GetProductData
     */
    private $testClass;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var ProductPageHelper
     */
    protected $productPageHelper;

    /**
     * setUp
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->registry = $this->createPartialMock(Registry::class, ['registry']);
        $this->productPageHelper = $this->getMockBuilder(ProductPageHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context = $this->createMock(Context::class);
        $this->testClass = $this->objectManager->getObject(
            GetProductData::class,
            [
                'context' => $context,
                'registry' => $this->registry,
                'productPageHelper' => $this->productPageHelper
            ]
        );
    }

    public function testGetProduct()
    {
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->registry->expects($this->once())
            ->method('registry')
            ->with(('product'))
            ->willReturn($product);
        $this->assertEquals($product, $this->testClass->getProduct());
    }

    public function testGetProductId()
    {
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->registry->expects($this->never())
            ->method('registry')
            ->with(('product'))
            ->willReturn($product);
        $product->expects($this->once())->method('getId')->willReturn(46);
        $this->assertEquals(46, $this->testClass->getProductId($product));
    }

    public function testIsProductTypeBundle()
    {
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->registry->expects($this->never())
            ->method('registry')
            ->with(('product'))
            ->willReturn($product);
        $product->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->assertEquals(true, $this->testClass->isProductTypeBundle($product));
    }

    public function testIsProductTypeNotBundle()
    {
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->registry->expects($this->never())
            ->method('registry')
            ->with(('product'))
            ->willReturn($product);
        $product->expects($this->once())->method('getTypeId')->willReturn(['simple','configurable']);
        $this->assertEquals(false, $this->testClass->isProductTypeBundle($product));
    }

    public function testGetBundleDefaultProductComponentsFalse()
    {
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->registry->expects($this->never())
            ->method('registry')
            ->with(('product'))
            ->willReturn($product);
        $product->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->assertEquals(false, $this->testClass->getBundleDefaultProductComponents($product));
    }

    public function testGetBundleDefaultProductComponentsTrue()
    {
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->registry->expects($this->never())
            ->method('registry')
            ->with(('product'))
            ->willReturn($product);

        $defaultSelections = [
            [
                "product_id" => 26,
                "qty" => 2.0000
            ],
            [
                "product_id" => 21,
                "qty" => 1.0000
            ],
            [
                "product_id" => 33,
                "qty" => 1.0000
            ],
            [
                "product_id" => 22,
                "qty" => 1.0000
            ]
        ];
        $output = '"26","26","21","33","22"';
        $product->expects($this->never())->method('getId')->willReturn(46);
        $product->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->productPageHelper->expects($this->once())->method('getDefaultSelections')->with($product)->willReturn($defaultSelections);

        foreach ($defaultSelections as $defaultSelection) {
            $this->assertArrayHasKey('product_id', $defaultSelection);
            $this->assertArrayHasKey('qty', $defaultSelection);
        }

        $this->assertEquals($output, $this->testClass->getBundleDefaultProductComponents($product));
    }
}
