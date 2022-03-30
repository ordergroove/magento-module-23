<?php

namespace Ordergroove\Subscription\Helper\RecurringOrderHelper;

use Magento\Bundle\Model\OptionRepository;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Ordergroove\Subscription\Exception\RecurringOrderException;

/**
 * Class BundleRecurringOrderHelper
 * @package Ordergroove\Subscription\Helper\RecurringOrderHelper
 */
class BundleRecurringOrderHelper
{
    /**
     * @var OptionRepository
     */
    protected $optionRepository;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CreateRecurringOrderHelper
     */
    protected $createRecurringOrderHelper;

    /**
     * BundleRecurringOrderHelper constructor.
     * @param OptionRepository $optionRepository
     * @param ProductFactory $productFactory
     * @param CreateRecurringOrderHelper $createRecurringOrderHelper
     */
    public function __construct(
        OptionRepository $optionRepository,
        ProductFactory $productFactory,
        CreateRecurringOrderHelper $createRecurringOrderHelper
    )
    {
        $this->optionRepository = $optionRepository;
        $this->productFactory = $productFactory;
        $this->createRecurringOrderHelper = $createRecurringOrderHelper;
    }

    /**
     * @param array $data
     * @return array
     */
    public function bundleProductsInRecurringOrder(array $data)
    {
        $bundleProductIds = [];
        foreach ($data['components'] as $component) {
            foreach ($component as $productItem) {
                $bundleProductIds[] = $productItem['product_id'];
            }
        }
        return $bundleProductIds;
    }

    /**
     * @param $productId
     * @param array $bundleProductIds
     * @return array|false
     * @throws NoSuchEntityException
     * @throws RecurringOrderException
     */
    public function getBundleOptions($productId, array $bundleProductIds)
    {
        $product = $this->productFactory->create()->load($productId);
        $bundleProductIdSet = [];
        if ($product->getTypeId() !== 'bundle') {
            return false;
        }

        foreach ($bundleProductIds as $bundleProductId) {
            $this->createRecurringOrderHelper->getStockStatus($bundleProductId);
        }

        $selectionCollection = $product->getTypeInstance()
            ->getSelectionsCollection(
                $product->getTypeInstance()->getOptionsIds($product),
                $product
            );
        foreach ($selectionCollection as $selection) {
            foreach ($bundleProductIds as $bundleProductId) {
                if ($selection->getEntityId() === $bundleProductId) {
                    $bundleProductIdSet[$selection->getOptionId()][] = $selection->getSelectionId();
                }
            }
        }
        $bundleProductIdReturnSet = [];
        foreach ($bundleProductIdSet as $optionId => $optionIdSelections) {
            $bundleProductIdReturnSet[$optionId] = array_unique($optionIdSelections);
        }

        $finalBundleProductIdReturnSet = [];
        foreach ($bundleProductIdReturnSet as $key => $value) {
            foreach ($value as $optionItem) {
                $finalBundleProductIdReturnSet[$key][] = $optionItem;
            }
        }

        return $finalBundleProductIdReturnSet;
    }

    /**
     * @param $productId
     * @param array $countOfEachBundleProduct
     * @return array|false
     * @throws NoSuchEntityException
     * @throws RecurringOrderException
     */
    public function getBundleOptionsQtyFromOG($productId, array $countOfEachBundleProduct)
    {
        $product = $this->productFactory->create()->load($productId);
        $bundleProductQtySet = [];
        if (!($product->getTypeId() === 'bundle')) {
            return false;
        }
        $selectionCollection = $product->getTypeInstance()
            ->getSelectionsCollection(
                $product->getTypeInstance()->getOptionsIds($product),
                $product
            );

        foreach ($selectionCollection as $selection) {
            foreach ($countOfEachBundleProduct as $key => $value) {
                // $key = Product ID
                // $value = Requested quantity
                $this->createRecurringOrderHelper->getStockQty($key, $value);
                if ((int)$selection->getEntityId() === $key) {
                    $bundleProductQtySet[$selection->getOptionId()][] = $value;
                }
            }
        }

        $finalBundleProductQtyReturnSet = [];
        foreach ($bundleProductQtySet as $key => $value) {
            if (count($value) > 1) {
                $finalBundleProductQtyReturnSet[$key][] = $value;
            } else {
                $finalBundleProductQtyReturnSet[$key] = $value[0];
            }
        }

        return $finalBundleProductQtyReturnSet;
    }
}
