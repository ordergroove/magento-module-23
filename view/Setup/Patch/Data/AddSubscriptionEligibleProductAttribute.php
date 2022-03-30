<?php
declare(strict_types=1);

namespace Ordergroove\Subscription\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class AddSubscriptionEligibleProductAttribute
 * @package Ordergroove\Subscription\Setup\Patch\Data
 */
class AddSubscriptionEligibleProductAttribute implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * Product Attribute
     */
    const IOI_ELIGIBLE = 'ioi_eligible';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Action
     */
    private $productAction;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param CollectionFactory $collectionFactory
     * @param Action $productAction
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        CollectionFactory $collectionFactory,
        Action $productAction
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->collectionFactory = $collectionFactory;
        $this->productAction = $productAction;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            self::IOI_ELIGIBLE,
            [
                'type' => 'int',
                'label' => 'IOI Eligible',
                'input' => 'boolean',
                'source' => Boolean::class,
                'frontend' => '',
                'required' => true,
                'backend' => '',
                'sort_order' => '30',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'default' => '1',
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => true,
                'comparable' => false,
                'used_for_promo_rules' => true,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable',
                'group' => 'General',
                'used_in_product_listing' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'option' => ['values' => [""]],
            ]
        );

        $this->updateAllProducts();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, self::IOI_ELIGIBLE);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Update attribute value for all products once it is installed
     */
    private function updateAllProducts()
    {
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('entity_id')
            ->load();

        $productIds = [];
        foreach ($collection as $product) {
            $productIds[] = $product->getId();
        }
        $this->productAction->updateAttributes($productIds, [self::IOI_ELIGIBLE => 1], 0);
    }
}
