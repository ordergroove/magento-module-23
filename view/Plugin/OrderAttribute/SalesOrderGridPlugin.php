<?php

namespace Ordergroove\Subscription\Plugin\OrderAttribute;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

/**
 * Class SalesOrderGridPlugin
 * @package Ordergroove\Subscription\Plugin\OrderAttribute
 */
class SalesOrderGridPlugin
{
    /**
     * @param Collection $subject
     * @return null
     * @throws LocalizedException
     */
    public function beforeLoad(Collection $subject)
    {
        if (!$subject->isLoaded()) {
            $primaryKey = $subject->getResource()->getIdFieldName();
            $tableName = $subject->getResource()->getTable('ordergroove_order_type');

            $subject->getSelect()->joinLeft(
                $tableName,
                $tableName . '.order_id = main_table.' . $primaryKey,
                $tableName . '.ordergroove_order_type'
            );
        }
        return null;
    }
}
