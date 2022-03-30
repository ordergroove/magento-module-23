<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Observer\CustomerUpdate;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ordergroove\Subscription\Logger\CustomerUpdates\Error\Logger as CustomerErrorLogger;
use Ordergroove\Subscription\Logger\CustomerUpdates\Info\Logger as CustomerInfoLogger;
use Ordergroove\Subscription\Model\Customer\UpdateDataApi;
use Ordergroove\Subscription\Model\Logging\OrdergrooveLoggingFactory;

/**
 * Class ChangeInEmail
 * @package Ordergroove\Subscription\Observer\CustomerUpdate
 */
class ChangeInEmail implements ObserverInterface
{
    /**
     * @var CustomerErrorLogger
     */
    protected $customerErrorLogger;

    /**
     * @var CustomerInfoLogger
     */
    protected $customerInfoLogger;

    /**
     * @var UpdateDataApi
     */
    protected $updateDataApiHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var OrdergrooveLoggingFactory
     */
    protected $ordergrooveLoggingFactory;

    /**
     * ChangeInEmail constructor.
     * @param CustomerErrorLogger $customerErrorLogger
     * @param CustomerInfoLogger $customerInfoLogger
     * @param UpdateDataApi $updateDataApiHelper
     * @param Session $customerSession
     * @param OrdergrooveLoggingFactory $ordergrooveLoggingFactory
     */
    public function __construct(
        CustomerErrorLogger $customerErrorLogger,
        CustomerInfoLogger $customerInfoLogger,
        UpdateDataApi $updateDataApiHelper,
        Session $customerSession,
        OrdergrooveLoggingFactory $ordergrooveLoggingFactory
    ) {
        $this->customerErrorLogger = $customerErrorLogger;
        $this->customerInfoLogger = $customerInfoLogger;
        $this->updateDataApiHelper = $updateDataApiHelper;
        $this->customerSession = $customerSession;
        $this->ordergrooveLoggingFactory = $ordergrooveLoggingFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $customer = $this->customerSession->getCustomer();
        $customerId = $customer->getId();
        $customerEmail = $customer->getEmail();
        $updatedCustomerEmail = $observer->getCustomerDataObject()->getEmail();
        if ($updatedCustomerEmail === $customerEmail) {
            $this->customerInfoLogger->info('No changes has been made to the email ' . $customerEmail);
            return;
        }

        // Call the Customer update API
        try {
            $this->updateDataApiHelper->executeUpdateRequest($updatedCustomerEmail);
        } catch (\Exception $e) {
            $this->customerErrorLogger->error('Error message', ['exception' => $e]);
            $ordergrooveLogging = $this->ordergrooveLoggingFactory->create();
            $ordergrooveLogging->addData([
                "log_date" => time(),
                "file_path" => $e->getFile() . " on Line " . $e->getLine(),
                "error_message" => $e->getMessage()
            ])->save();
        }
    }
}
