<?php

namespace Ordergroove\Subscription\Helper;

use Magento\Catalog\Model\Product;

/**
 * Class ProductPageHelper
 * @package Ordergroove\Subscription\Helper
 */
class ProductPageHelper
{
    /**
     * @param $product
     * @return array|false
     */
    public function getDefaultSelections(Product $product)
    {
        $optionsCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
        $defaultSelectedOptions = [];
        foreach ($optionsCollection as $option) {
            $selections = $product->getTypeInstance(true)
                ->getSelectionsCollection(
                    $option->getOptionId(),
                    $product
                );
            foreach ($selections as $selection) {
                if ($selection->getIsDefault()) {
                    $defaultSelectedOptions[$option->getOptionId()] = [
                        'product_id' => $selection->getProductId(),
                        'qty' => $selection->getSelectionQty()
                    ];
                }
            }
        }
        return $defaultSelectedOptions;
    }

    /**
     * @param $product
     * @return array
     */
    public function mapProductComponents($product)
    {
        $optionsCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
        $productIds = [];
        foreach ($optionsCollection as $option) {
            $selections = $product->getTypeInstance(true)
                ->getSelectionsCollection(
                    $option->getOptionId(),
                    $product
                );
            foreach ($selections as $selection) {
                $productIds[$selection->getSelectionId()] = $selection->getProductId();
            }
        }

        return $productIds;
    }
}
