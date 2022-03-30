<?php

namespace Ordergroove\Subscription\Logger\CustomerUpdates\Error;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::ERROR;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/ordergroove/customer_updates/customer_updates_errors.log';
}
