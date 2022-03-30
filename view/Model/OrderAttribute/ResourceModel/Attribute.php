<?php

namespace Ordergroove\Subscription\Model\OrderAttribute\ResourceModel;

/**
 * Class Attribute
 * @package Ordergroove\Subscription\Model\OrderAttribute\ResourceModel
 */
class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Default Constructor
     */
    protected function _construct()
    {
        $this->_init('ordergroove_order_type', 'entity_id');
    }
}
