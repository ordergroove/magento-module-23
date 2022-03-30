<?php

namespace Ordergroove\Subscription\Controller\CheckSubscription;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Index
 * @package Ordergroove\Subscription\Controller\CheckSubscription
 */
class Index extends Action
{
    /**
     * Path for the error message from admin config
     */
    const ERROR_MESSAGE_PATH = 'ordergroove_subscription/error_message/message';

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Index constructor.
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $resultRedirectFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $redirectUrl = $this->resultRedirectFactory->create();
        $getErrorMessage = $this->scopeConfig->getValue(self::ERROR_MESSAGE_PATH, ScopeInterface::SCOPE_WEBSITE);
        $this->messageManager->addErrorMessage(__($getErrorMessage));
        $redirectUrl->setPath('customer/account/login');
        return $redirectUrl;
    }
}
