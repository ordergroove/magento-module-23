<?php

namespace Ordergroove\Subscription\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\InfoInterface;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Observer\DataAssignObserver;
use PHPUnit\Framework\TestCase;

class DataAssignObserverTest extends TestCase
{
    /**
     * @var DataAssignObserver
     */
    private $dataAssignObserver;

    /**
     * @var ConfigHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHelper;

    /**
     * @var ObjectManager
     */
    private $objectManager;
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataAssignObserver = $this->objectManager->getObject(
            DataAssignObserver::class,
            [
                'configHelper' => $this->configHelper
            ]
        );
    }

    public function testExecute()
    {

        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(true);

        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event2 = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataObject = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentModel = $this->getMockBuilder(InfoInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->exactly(2))->method('getEvent')->willReturnOnConsecutiveCalls($event, $event2);
        $event->expects($this->once())->method('getDataByKey')->with('data')->willReturn($dataObject);
        $event2->expects($this->once())->method('getDataByKey')->with('payment_model')->willReturn($paymentModel);
        $dataObject->expects($this->once())->method('getData')->with('additional_data')->willReturn(["test"]);
        $paymentModel->expects($this->once())->method('setAdditionalInformation')->with(["test"]);
        $this->dataAssignObserver->execute($observer);
    }

    public function testExecuteNotArray()
    {

        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(true);

        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataObject = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->once())->method('getEvent')->willReturn($event);
        $event->expects($this->once())->method('getDataByKey')->with('data')->willReturn($dataObject);
        $dataObject->expects($this->once())->method('getData')->with('additional_data')->willReturn("test");
        $this->dataAssignObserver->execute($observer);
    }

    public function testExecuteNotEnabled()
    {

        $this->configHelper->expects($this->once())->method('isEnabled')->willReturn(false);

        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->never())->method('getEvent');
        $this->dataAssignObserver->execute($observer);
    }
}
