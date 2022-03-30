<?php

namespace Ordergroove\Subscription\Model\Logging\ResourceModel\OrdergrooveLogging;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Ordergroove\Subscription\Model\Logging\ResourceModel\OrdergrooveLogging
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     * @var string
     */
    protected $_eventPrefix = 'ordergroove_logging_collection';
    /**
     * @var string
     */
    protected $_eventObject = 'ordergroove_logging_collection';

    /**
     * Default constructor
     */
    protected function _construct()
    {
        $this->_init('Ordergroove\Subscription\Model\Logging\OrdergrooveLogging', 'Ordergroove\Subscription\Model\Logging\ResourceModel\OrdergrooveLogging');
    }
}
