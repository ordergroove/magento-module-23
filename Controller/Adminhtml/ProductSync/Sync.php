<?php

namespace Ordergroove\Subscription\Controller\Adminhtml\ProductSync;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Cron\Model\Schedule;

class Sync extends Action
{

    const JOB_CODE = 'ordergroove_product_sync';

    /**
     * @var ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @param Context $context
     * @param ScheduleFactory $scheduleFactory
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        Context $context,
        ScheduleFactory $scheduleFactory,
        ResultFactory $resultFactory
    )
    {
        $this->scheduleFactory = $scheduleFactory;
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $timecreated   = strftime("%Y-%m-%d %H:%M:%S",  mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
        $timescheduled = strftime("%Y-%m-%d %H:%M:%S", mktime(date("H"), date("i") + 2, date("s"), date("m"), date("d"), date("Y")));
        try {
            $this->scheduleFactory->create()
                ->setJobCode(self::JOB_CODE)
                ->setStatus(Schedule::STATUS_PENDING)
                ->setCreatedAt($timecreated)
                ->setScheduledAt($timescheduled)->save();
                $this->messageManager->addSuccessMessage(__('Product sync scheduled to run at ' . $timescheduled));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Product sync scheduled failed, please try again'));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
