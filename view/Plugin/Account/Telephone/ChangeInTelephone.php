<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ordergroove\Subscription\Plugin\Account\Telephone;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Controller\Address\FormPost;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Exception\LocalizedException;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Logger\CustomerUpdates\Error\Logger as CustomerErrorLogger;
use Ordergroove\Subscription\Logger\CustomerUpdates\Info\Logger as CustomerInfoLogger;
use Ordergroove\Subscription\Model\Customer\UpdateDataApi;
use Ordergroove\Subscription\Model\Logging\OrdergrooveLoggingFactory;

/**
 * Class ChangeInTelephone
 * @package Ordergroove\Subscription\Plugin\Account\Telephone
 */
class ChangeInTelephone
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
     * @var Request
     */
    protected $request;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

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
     * @param Request $request
     * @param AddressRepositoryInterface $addressRepository
     * @param ConfigHelper $configHelper
     * @param OrdergrooveLoggingFactory $ordergrooveLoggingFactory
     */
    public function __construct(
        CustomerErrorLogger $customerErrorLogger,
        CustomerInfoLogger $customerInfoLogger,
        UpdateDataApi $updateDataApiHelper,
        Session $customerSession,
        Request $request,
        AddressRepositoryInterface $addressRepository,
        ConfigHelper $configHelper,
        OrdergrooveLoggingFactory $ordergrooveLoggingFactory
    ) {
        $this->customerErrorLogger = $customerErrorLogger;
        $this->customerInfoLogger = $customerInfoLogger;
        $this->updateDataApiHelper = $updateDataApiHelper;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->addressRepository = $addressRepository;
        $this->configHelper = $configHelper;
        $this->ordergrooveLoggingFactory = $ordergrooveLoggingFactory;
    }

    /**
     * @param FormPost $subject
     * @param $proceed
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundExecute(
        FormPost $subject,
        $proceed
    ) {
        if (!$this->configHelper->isEnabled()) {
            return $proceed();
        }
        // Get Existing Telephone
        $addresses = $this->customerSession->getCustomer()->getAddresses();
        if (count($addresses) > 0) {
            $telephone = $this->customerSession->getCustomer()->getDefaultBillingAddress()->getTelephone();
            $customerEmail = $this->customerSession->getCustomer()->getEmail();
            $result = $proceed();

            $updatedTelephone = '';
            // Get Updated Telephone
            if ($addressId = $this->request->getParam('id')) {
                $updatedTelephone = $this->addressRepository->getById($addressId)->getTelephone();

                if ($updatedTelephone === $telephone) {
                    $this->customerInfoLogger->info('No changes has been made to the existing phone number ' . $telephone);
                    return $result;
                }
            }
            // Call the Customer update API
            try {
                $this->updateDataApiHelper->executeUpdateRequest($customerEmail);
            } catch (\Exception $e) {
                $this->customerErrorLogger->error('Error message', ['exception' => $e]);
                $ordergrooveLogging = $this->ordergrooveLoggingFactory->create();
                $ordergrooveLogging->addData([
                    "log_date" => time(),
                    "file_path" => $e->getFile() . " on Line " . $e->getLine(),
                    "error_message" => $e->getMessage()
                ])->save();
            }
            return $result;
        }
        return $proceed();
    }
}
