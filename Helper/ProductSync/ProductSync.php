<?php

namespace Ordergroove\Subscription\Helper\ProductSync;

use Ordergroove\Subscription\Logger\ProductSync\Info\Logger;

class ProductSync
{

    /**
     * @var XmlHelper
     */
    private $xmlHelper;
    /**
     * @var SftpHelper
     */
    private $sftpHelper;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * ProductSync constructor.
     * @param XmlHelper $xmlHelper
     * @param SftpHelper $sftpHelper
     * @param Logger $logger
     */
    public function __construct(
        XmlHelper $xmlHelper,
        SftpHelper $sftpHelper,
        Logger $logger
    ) {
        $this->xmlHelper = $xmlHelper;
        $this->sftpHelper = $sftpHelper;
        $this->logger = $logger;
    }

    /**
     * Syncs products and logs status for all or specified websites
     * @param array $websiteIds <p>
     * If specified, the list of websites to be synced.
     * Leave blank to sync all websites.
     * </p>
     */
    public function processProductSync($websiteIds = null)
    {
        if ($websiteIds == null) {
            $websiteIds = $this->xmlHelper->getWebsiteIds();
        }

        $this->logger->info("Product Sync Started");

        foreach ($websiteIds as $websiteId) {
            $xml = $this->xmlHelper->createWebsiteProductsXml($websiteId);
            $this->sftpHelper->sendProductFeed($xml, $websiteId);
        }
    }
}
