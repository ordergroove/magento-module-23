<?php

namespace Ordergroove\Subscription\ViewModel\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ordergroove\Subscription\Helper\ProductPageHelper;

/**
 * Class GetProductInformation
 * @package Ordergroove\Subscription\ViewModel\Product
 */
class GetProductInformation implements ArgumentInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var ProductPageHelper
     */
    protected $productPageHelper;

    /**
     * Constructor
     *
     * @param ProductPageHelper $productPageHelper
     * @param Registry $registry
     */
    public function __construct(
        ProductPageHelper $productPageHelper,
        Registry $registry
    )
    {
        $this->registry = $registry;
        $this->productPageHelper = $productPageHelper;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (is_null($this->product)) {
            $this->product = $this->registry->registry('product');
        }

        return $this->product;
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product->getId();
    }

    /**
     * @return false|string
     */
    public function getBundleDefaultProductComponents()
    {
        if ($this->product->getTypeId() !== 'bundle') {
            return false;
        }
        $singleID = [];
        $multipleID = [];
        $defaultSelections = $this->productPageHelper->getDefaultSelections($this->product);
        foreach ($defaultSelections as $defaultSelection) {
            $id = $defaultSelection['product_id'];
            for ($count = 0; $count < $defaultSelection['qty']; $count++) {
                if ($defaultSelection['qty'] > 1) {
                    $multipleID[] = (string) $id;
                } else {
                    $singleID[] = (string) $id;
                }
            }
        }

        return json_encode(array_merge($multipleID, $singleID));
    }

    /**
     * @return bool
     */
    public function isProductTypeBundle()
    {
        if ($this->product->getTypeId() === 'bundle') {
            return true;
        }
        return false;
    }

    /**
     * @param $product
     * @return false|string
     */
    public function getUpdatedProductIDMap($product)
    {
        $mapSelectionData = $this->productPageHelper->mapProductComponents($product);
        return json_encode($mapSelectionData);
    }
}
