<?php
declare(strict_types=1);

namespace Ordergroove\Subscription\Model\OrderAttribute;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Attribute
 * @package Ordergroove\Subscription\Model\OrderAttribute
 */
class Attribute extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'ordergroove_order_type';

    protected $_cacheTag = 'ordergroove_order_type';

    protected $_eventPrefix = 'ordergroove_order_type';

    /**
     * Default Constructor
     */
    protected function _construct()
    {
        $this->_init('Ordergroove\Subscription\Model\OrderAttribute\ResourceModel\Attribute');
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
