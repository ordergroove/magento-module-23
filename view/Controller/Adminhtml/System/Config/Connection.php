<?php

namespace Ordergroove\Subscription\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Connection extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var Sftp
     */
    private $sftp;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Connection constructor.
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param Sftp $sftp
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $jsonFactory,
        Sftp $sftp,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->sftp = $sftp;
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        //Validate credentials exist:
        $params = $this->getRequest()->getParams();

        $host = $params["host"];
        $port = $params["port"];
        $username = $params["username"];
        $password = $params["password"];

        $result = $this->jsonFactory->create();
        $result = $result->setHttpResponseCode(200);

        if ($host == "" ||
            $port == "" ||
            $username == "" ||
            $password == ""
        ) {
            return $result->setData(['success' => false]);
        }

        if (preg_match('/^[\*]*$/', $password)) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $password = $this->scopeConfig->getValue(
                'ordergroove_subscription/sftp/password',
                ScopeInterface::SCOPE_WEBSITES,
                $websiteId
            );
        }

        try {
            $this->sftp->open([
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'password' => $password
            ]);
        } catch (\Exception $e) {
            return $result->setData(['success' => false]);
        }

        return $result->setData(['success' => true]);
    }
}
