<?php

namespace Ordergroove\Subscription\Setup;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(Config $config, DateTime $dateTime)
    {
        $this->config = $config;
        $this->dateTime = $dateTime;
    }

    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->config->saveConfig("ordergroove_subscription/sftp/host", "feeds.ordergroove.com", "default", "0");
        $this->config->saveConfig("ordergroove_subscription/sftp/port", 22, "default", "0");
    }
}
