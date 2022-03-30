<?php

namespace Ordergroove\Subscription\Test\Unit\Model\Config\Source;

use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ordergroove\Subscription\Model\Config\Source\SyncSelect;
use PHPUnit\Framework\TestCase;

class SyncSelectTest extends TestCase
{
    /**
     * @var SyncSelect
     */
    protected $model;

    /**
     * @var Timezone|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $timeZoneInterface;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->timeZoneInterface = $this->getMockBuilder(Timezone::class)
            ->disableOriginalConstructor()
            ->setMethods(["date"])
            ->getMock();

        $this->model = $objectManager->getObject(
            'Ordergroove\Subscription\Model\Config\Source\SyncSelect',
            [
                'timezoneInterface' => $this->timeZoneInterface
            ]
        );
    }

    protected function tearDown()
    {
    }

    public function testToOptionArrayEarlierThanEST()
    {
        $this->timeZoneInterface->expects($this->once())->method('date')->willReturn(
            new \DateTime('now', new \DateTimeZone('US/Pacific'))
        );
        $this->assertEquals(
            [
            ['value' => '0 21 * * *', 'label' => __('0:00')],
            ['value' => '0 22 * * *', 'label' => __('1:00')],
            ['value' => '0 23 * * *', 'label' => __('2:00')],
            ['value' => '0 0 * * *', 'label' => __('3:00')],
            ['value' => '0 1 * * *', 'label' => __('4:00')],
            ['value' => '0 2 * * *', 'label' => __('5:00')],
            ['value' => '0 3 * * *', 'label' => __('6:00')],
            ['value' => '0 4 * * *', 'label' => __('7:00')],
            ['value' => '0 5 * * *', 'label' => __('8:00')],
            ['value' => '0 6 * * *', 'label' => __('9:00')],
            ['value' => '0 7 * * *', 'label' => __('10:00')],
            ['value' => '0 8 * * *', 'label' => __('11:00')],
            ['value' => '0 9 * * *', 'label' => __('12:00')],
            ['value' => '0 10 * * *', 'label' => __('13:00')],
            ['value' => '0 11 * * *', 'label' => __('14:00')],
            ['value' => '0 12 * * *', 'label' => __('15:00')],
            ['value' => '0 13 * * *', 'label' => __('16:00')],
            ['value' => '0 14 * * *', 'label' => __('17:00')],
            ['value' => '0 15 * * *', 'label' => __('18:00')],
            ['value' => '0 16 * * *', 'label' => __('19:00')],
            ['value' => '0 17 * * *', 'label' => __('20:00')],
            ['value' => '0 18 * * *', 'label' => __('21:00')],
            ['value' => '0 19 * * *', 'label' => __('22:00')],
            ['value' => '0 20 * * *', 'label' => __('23:00')],

            ],
            $this->model->toOptionArray()
        );
    }

    public function testToOptionArrayLaterThanEST()
    {
        $this->timeZoneInterface->expects($this->once())->method('date')->willReturn(
            new \DateTime('now', new \DateTimeZone('UTC'))
        );
        $this->assertEquals(
            [
                ['value' => '0 5 * * *', 'label' => __('0:00')],
                ['value' => '0 6 * * *', 'label' => __('1:00')],
                ['value' => '0 7 * * *', 'label' => __('2:00')],
                ['value' => '0 8 * * *', 'label' => __('3:00')],
                ['value' => '0 9 * * *', 'label' => __('4:00')],
                ['value' => '0 10 * * *', 'label' => __('5:00')],
                ['value' => '0 11 * * *', 'label' => __('6:00')],
                ['value' => '0 12 * * *', 'label' => __('7:00')],
                ['value' => '0 13 * * *', 'label' => __('8:00')],
                ['value' => '0 14 * * *', 'label' => __('9:00')],
                ['value' => '0 15 * * *', 'label' => __('10:00')],
                ['value' => '0 16 * * *', 'label' => __('11:00')],
                ['value' => '0 17 * * *', 'label' => __('12:00')],
                ['value' => '0 18 * * *', 'label' => __('13:00')],
                ['value' => '0 19 * * *', 'label' => __('14:00')],
                ['value' => '0 20 * * *', 'label' => __('15:00')],
                ['value' => '0 21 * * *', 'label' => __('16:00')],
                ['value' => '0 22 * * *', 'label' => __('17:00')],
                ['value' => '0 23 * * *', 'label' => __('18:00')],
                ['value' => '0 0 * * *', 'label' => __('19:00')],
                ['value' => '0 1 * * *', 'label' => __('20:00')],
                ['value' => '0 2 * * *', 'label' => __('21:00')],
                ['value' => '0 3 * * *', 'label' => __('22:00')],
                ['value' => '0 4 * * *', 'label' => __('23:00')],

            ],
            $this->model->toOptionArray()
        );
    }
}
