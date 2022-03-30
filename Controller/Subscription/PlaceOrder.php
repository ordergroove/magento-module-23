<?php
declare(strict_types=1);

namespace Ordergroove\Subscription\Controller\Subscription;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Ordergroove\Subscription\Exception\RecurringOrderException;
use Ordergroove\Subscription\Helper\RecurringOrderHelper\CreateRecurringOrderHelper;
use Ordergroove\Subscription\Logger\RecurringOrder\Error\Logger as ErrorLogger;
use Ordergroove\Subscription\Model\Authentication\ValidateAuthorization;
use Ordergroove\Subscription\Model\RecurringOrder\CreateRecurringOrder;
use Ordergroove\Subscription\Model\Request\ValidateRequest;
use Magento\Framework\Json\Helper\Data;
use Ordergroove\Subscription\Model\Logging\OrdergrooveLoggingFactory;

/**
 * Class PlaceOrder
 * @package Ordergroove\Subscription\Controller\Subscription
 */
class PlaceOrder extends Action implements CsrfAwareActionInterface
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var ErrorLogger
     */
    protected $errorLogger;

    /**
     * @var ConvertArray
     */
    protected $convertArray;

    /**
     * @var CreateRecurringOrder
     */
    protected $createRecurringOrder;

    /**
     * @var CreateRecurringOrderHelper
     */
    protected $createRecurringOrderHelper;

    /**
     * @var ValidateAuthorization
     */
    protected $validateAuthorizationHelper;

    /**
     * @var ValidateRequest
     */
    protected $validateRequest;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var OrdergrooveLoggingFactory
     */
    protected $ordergrooveLoggingFactory;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param CreateRecurringOrder $createRecurringOrder
     * @param ErrorLogger $errorLogger
     * @param ConvertArray $convertArray
     * @param CreateRecurringOrderHelper $createRecurringOrderHelper
     * @param ValidateAuthorization $validateAuthorizationHelper
     * @param ValidateRequest $validateRequest
     * @param Data $jsonHelper
     * @param OrdergrooveLoggingFactory $ordergrooveLoggingFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        CreateRecurringOrder $createRecurringOrder,
        ErrorLogger $errorLogger,
        ConvertArray $convertArray,
        CreateRecurringOrderHelper $createRecurringOrderHelper,
        ValidateAuthorization $validateAuthorizationHelper,
        ValidateRequest $validateRequest,
        Data $jsonHelper,
        OrdergrooveLoggingFactory $ordergrooveLoggingFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->createRecurringOrder = $createRecurringOrder;
        $this->errorLogger = $errorLogger;
        $this->convertArray = $convertArray;
        $this->createRecurringOrderHelper = $createRecurringOrderHelper;
        $this->validateAuthorizationHelper = $validateAuthorizationHelper;
        $this->validateRequest = $validateRequest;
        $this->jsonHelper = $jsonHelper;
        $this->ordergrooveLoggingFactory = $ordergrooveLoggingFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Raw|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $results = $this->resultRawFactory->create();
        $request = $this->getRequest();
        try {
            $checkRequestData = $this->validateRequest->checkPostRequestData($request);
            if (isset($checkRequestData['errorMsg'])) {
                throw new Exception(__($checkRequestData['errorMsg']));
            }

            $authorization = $request->getServerValue('HTTP_AUTHORIZATION');
            $decodeAuthorization = $this->jsonHelper->jsonDecode($authorization);
            $sigField = $decodeAuthorization['sig_field'];
            $sig = $decodeAuthorization['sig'];
            $timeStamp = $decodeAuthorization['ts'];

            $validateAuthentication = $this->validateAuthorizationHelper->validateAuthentication($sigField, $sig, $timeStamp);
            if (!$validateAuthentication) {
                throw new Exception(__("Authentication failed. Unable to validate HMAC signature"));
            }

            $content = $request->getContent();
            $orderData = simplexml_load_string($content, null, LIBXML_NOCDATA);

            $orderDataFromXmlToArray = $this->createRecurringOrderHelper->parseXmlToArray($orderData->asXML());
            $response = $this->createRecurringOrder->placeRecurringOrder($orderDataFromXmlToArray);

            if (isset($response['orderId'])) {
                $result = ['code' => 'SUCCESS', 'orderId' => $response['orderId']];
                $xml = $this->convertArray->assocToXml($result, 'order');
            } else {
                throw new Exception(__("orderId not set in order placement response"));
            }
        } catch (\Exception $exception) {
            $this->errorLogger->error($exception->getMessage());

            $errorCode = '020';
            if ($exception instanceof RecurringOrderException) {
                $errorCode = $exception->getCode();
            }
            $result = [
                'code' => 'ERROR',
                'errorCode' => $errorCode,
                'errorMsg' => $exception->getMessage()
            ];

            $ordergrooveLogging = $this->ordergrooveLoggingFactory->create();
            $ordergrooveLogging->addData([
                "log_date" => time(),
                "file_path" => $exception->getFile() . " on Line " . $exception->getLine(),
                "error_message" => $exception->getMessage()
            ])->save();

            $xml = $this->convertArray->assocToXml($result, 'order');
        }

        // Return XML data
        $results->setHeader('Content-Type', 'text/xml');
        $results->setContents($xml->asXML());
        return $results;
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
