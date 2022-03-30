<?php

namespace Ordergroove\Subscription\Model\OrderAttribute\ResourceModel\Attribute;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Ordergroove\Subscription\Model\OrderAttribute\ResourceModel\Attribute
 */
class Collection extends AbstractCollection
{
    /**
     * Default Constructor
     */
    protected function _construct()
    {
        $this->_init('Ordergroove\Subscription\Model\OrderAttribute\Attribute', 'Ordergroove\Subscription\Model\OrderAttribute\ResourceModel\Attribute');
    }
}
