<?php

namespace Ordergroove\Subscription\Test\Unit\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use Ordergroove\Subscription\Helper\ProductSync\XmlHelper;
use PHPUnit\Framework\TestCase;

class XmlHelperTest extends TestCase
{
    /**
     * @var XmlHelper
     */
    protected $xmlHelper;
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var StoreRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeRepository;
    /**
     * @var ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productFactory;
    /**
     * @var Iterator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iterator;
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;
    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var Product\Image\UrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageHelper;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeRepository = $this->getMockBuilder(StoreRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();

        $this->productFactory = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->iterator = $this->getMockBuilder(Iterator::class)
            ->disableOriginalConstructor()
            ->setMethods(['walk'])
            ->getMock();

        $this->eavConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(get_class_methods(StoreManagerInterface::class))
            ->getMock();

        $this->imageHelper = $this->getMockBuilder(Product\Image\UrlBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'isSetFlag'])
            ->getMock();

        $this->xmlHelper = $this->objectManager->getObject(
            XmlHelper::class,
            [
                'storeRepository' => $this->storeRepository,
                'productFactory' => $this->productFactory,
                'iterator' => $this->iterator,
                'eavConfig' => $this->eavConfig,
                'storeManager' => $this->storeManager,
                'imageHelper' => $this->imageHelper,
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }

    protected function tearDown()
    {
    }

    public function testGetWebsiteIds()
    {
        $store1 = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store2 = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeRepository->expects($this->once())->method('getList')->willReturn([$store1, $store2]);
        $store1->expects($this->once())->method('getWebsiteId')->willReturn('0');
        $store2->expects($this->once())->method('getWebsiteId')->willReturn('1');
        $this->scopeConfig->expects($this->exactly(2))->method('getValue')->willReturn("1");

        $this->assertEquals(['0','1'], $this->xmlHelper->getWebsiteIds());
    }

    public function testCreateWebsiteProductsXml()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMethods = array_merge(
            get_class_methods(AbstractCollection::class),
            ['getEntity','addAttributeToFilter','joinField','joinTable', 'addAttributeToSelect', 'addWebsiteFilter']
        );
        $collection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->setMethods($collectionMethods)
            ->getMock();

        $entity = $this->getMockBuilder(Entity::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attribute = $this->getMockBuilder(Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productFactory->expects($this->once())->method('create')->willReturn($product);
        $product->expects($this->once())->method('getCollection')->willReturn($collection);
        $collection->expects($this->once())->method('getEntity')->willReturn($entity);
        $entity->expects($this->once())->method('getType')->willReturn('4');
        $this->eavConfig->expects($this->any())->method('getAttribute')->willReturn($attribute);
        $attribute->expects($this->any())->method('getId')->willReturn('1');

        $collection->expects($this->any())->method('addAttributeToFilter')->willReturnSelf();
        $collection->expects($this->any())->method('addWebsiteFilter')->willReturnSelf();
        $collection->expects($this->any())->method('setFlag')->willReturnSelf();
        $collection->expects($this->any())->method('joinField')->willReturnSelf();
        $collection->expects($this->any())->method('joinTable')->willReturnSelf();
        $collection->expects($this->any())->method('addAttributeToSelect')->willReturnSelf();
        $this->iterator
            ->expects($this->once())
            ->method('walk')
            ->with($collection->getSelect(), [[$this->xmlHelper, 'processProductIntoXml']]);
        $testXml = new \SimpleXMLElement('<products/>');

        //the iterator->walk function does not update the $xml object
        //so it will end up being just <products/> in the end.
        $this->assertEquals($testXml, $this->xmlHelper->createWebsiteProductsXml('0'));
    }

    public function testGenerateProductUrlFromId()
    {
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(StoreInterface::class), ['getBaseUrl']))
            ->getMock();

        $this->storeManager->expects($this->exactly(2))->method('getStore')->willReturn($store);
        $store->expects($this->exactly(2))->method('getBaseUrl')->willReturn('test.test/');

        $this->assertEquals('test.test/catalog/product/view/id/1', $this->xmlHelper->generateProductUrlFromId('1'));
        $this->assertEquals('test.test/catalog/product/view/id/2', $this->xmlHelper->generateProductUrlFromId('2'));
    }

    public function testGenerateImageUrlFromRelativePath()
    {
        $this->imageHelper->expects(($this->once()))->method('getUrl')->with('shirt', 'frontend')
            ->willReturn(('http://test.test/shirt'));
        $this->assertEquals('https://test.test/shirt', $this->xmlHelper->generateImageUrlFromRelativePath('shirt'));
    }
}
