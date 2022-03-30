<?php

namespace Ordergroove\Subscription\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class UrlType implements OptionSourceInterface
{
    /**
     * Value which equal Enable for Enabledisable dropdown.
     */
    const ENABLE_VALUE = 1;

    /**
     * Value which equal Disable for Enabledisable dropdown.
     */
    const DISABLE_VALUE = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ENABLE_VALUE, 'label' => __('Development')],
            ['value' => self::DISABLE_VALUE, 'label' => __('Production')],
        ];
    }
}
