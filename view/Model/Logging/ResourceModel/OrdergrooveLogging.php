<?php

namespace Ordergroove\Subscription\Model\Logging\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class OrdergrooveLogging
 * @package Ordergroove\Subscription\Model\Logging\ResourceModel
 */
class OrdergrooveLogging extends AbstractDb
{
    /**
     * OrdergrooveLogging constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Default constructor
     */
    protected function _construct()
    {
        $this->_init('ordergroove_logging', 'entity_id');
    }
}
