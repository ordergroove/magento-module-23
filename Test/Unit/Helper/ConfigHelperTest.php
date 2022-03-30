<?php

namespace Ordergroove\Subscription\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ordergroove\Subscription\Helper\ConfigHelper;
use PHPUnit\Framework\TestCase;

class ConfigHelperTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(get_class_methods(StoreManagerInterface::class))
            ->getMock();

        $this->configHelper = $this->objectManager->getObject(
            ConfigHelper::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'storeManager' => $this->storeManager
            ]
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetPublicId()
    {
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(get_class_methods(StoreInterface::class))
            ->getMock();
        $this->storeManager->expects($this->once())->method("getStore")->willReturn($store);
        $store->expects($this->once())->method("getWebsiteId")->willReturn("1");
        $this->scopeConfig->expects($this->once())->method("getValue")
            ->with('ordergroove_subscription/general/public_id', 'websites', "1")->willReturn(true);
        $this->configHelper->getPublicId();
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetPublicIdWebsitePassed()
    {
        $this->storeManager->expects($this->never())->method("getStore");
        $this->scopeConfig->expects($this->once())->method("getValue")
            ->with('ordergroove_subscription/general/public_id', 'websites', "1")->willReturn(true);
        $this->configHelper->getPublicId("1");
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetHashKey()
    {
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(get_class_methods(StoreInterface::class))
            ->getMock();
        $this->storeManager->expects($this->once())->method("getStore")->willReturn($store);
        $store->expects($this->once())->method("getWebsiteId")->willReturn("1");
        $this->scopeConfig->expects($this->once())->method("getValue")
            ->with('ordergroove_subscription/general/hash_key', 'websites', "1")->willReturn(true);
        $this->configHelper->getHashKey();
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetHashKeyWebsitePassed()
    {
        $this->storeManager->expects($this->never())->method("getStore");
        $this->scopeConfig->expects($this->once())->method("getValue")
            ->with('ordergroove_subscription/general/hash_key', 'websites', "1")->willReturn(true);
        $this->configHelper->getHashKey("1");
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetStaging()
    {
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(get_class_methods(StoreInterface::class))
            ->getMock();
        $this->storeManager->expects($this->once())->method("getStore")->willReturn($store);
        $store->expects($this->once())->method("getWebsiteId")->willReturn("1");
        $this->scopeConfig->expects($this->once())->method("isSetFlag")
            ->with('ordergroove_subscription/general/staging', 'websites', "1")->willReturn(true);
        $this->configHelper->getStaging();
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetStagingWebsitePassed()
    {
        $this->storeManager->expects($this->never())->method("getStore");
        $this->scopeConfig->expects($this->once())->method("isSetFlag")
            ->with('ordergroove_subscription/general/staging', 'websites', "1")->willReturn(true);
        $this->configHelper->getStaging("1");
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testIsEnabled()
    {
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(get_class_methods(StoreInterface::class))
            ->getMock();
        $this->storeManager->expects($this->once())->method("getStore")->willReturn($store);
        $store->expects($this->once())->method("getWebsiteId")->willReturn("1");
        $this->scopeConfig->expects($this->once())->method("isSetFlag")
            ->with('ordergroove_subscription/general/enable', 'websites', "1")->willReturn(true);
        $this->configHelper->isEnabled();
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testIsEnabledWebsitePassed()
    {
        $this->storeManager->expects($this->never())->method("getStore");
        $this->scopeConfig->expects($this->once())->method("isSetFlag")
            ->with('ordergroove_subscription/general/enable', 'websites', "1")->willReturn(true);
        $this->configHelper->isEnabled("1");
    }
}
