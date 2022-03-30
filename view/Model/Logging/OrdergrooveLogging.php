<?php
declare(strict_types=1);

namespace Ordergroove\Subscription\Model\Logging;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class OrdergrooveLogging
 * @package Ordergroove\Subscription\Model\Logging
 */
class OrdergrooveLogging extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'ordergroove_logging';

    protected $_cacheTag = 'ordergroove_logging';

    protected $_eventPrefix = 'ordergroove_logging';

    /**
     * Default Constructor
     */
    protected function _construct()
    {
        $this->_init('Ordergroove\Subscription\Model\Logging\ResourceModel\OrdergrooveLogging');
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
