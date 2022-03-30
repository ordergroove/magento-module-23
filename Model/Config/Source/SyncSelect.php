<?php

namespace Ordergroove\Subscription\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class SyncSelect implements OptionSourceInterface
{

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    public function __construct(TimezoneInterface $timezoneInterface)
    {
        $this->timezoneInterface = $timezoneInterface;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];

        $estTimeZone = new \DatetimeZone('US/Eastern');
        $serverTimeZone = $this->timezoneInterface->date();

        $estOffset = $estTimeZone->getOffset(new \DateTime('now'));
        $serverOffset = $serverTimeZone->getOffset();

        $offsetInSeconds = $estOffset - $serverOffset;
        $offsetInHours = (int)($offsetInSeconds / 3600);
        $twentyFourHourOffset = 24 - $offsetInHours;

        for ($i = 0; $i < 24; $i++) {
            $options[] = ['value' => '0 ' . abs($i + $twentyFourHourOffset) % 24 . ' * * *', 'label' => __($i . ':00')];
        }

        return $options;
    }
}
