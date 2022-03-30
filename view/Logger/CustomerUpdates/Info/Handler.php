<?php

namespace Ordergroove\Subscription\Logger\CustomerUpdates\Info;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/ordergroove/customer_updates/customer_updates_info.log';
}
