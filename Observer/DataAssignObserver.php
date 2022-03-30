<?php

namespace Ordergroove\Subscription\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Ordergroove\Subscription\Helper\ConfigHelper;

class DataAssignObserver extends AbstractDataAssignObserver
{

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    public function __construct(ConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->configHelper->isEnabled()) {
            return;
        }

        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $paymentModel = $this->readPaymentModelArgument($observer);

        $paymentModel->setAdditionalInformation($additionalData);
    }
}
