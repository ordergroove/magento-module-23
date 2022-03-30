<?php
declare(strict_types=1);

namespace Ordergroove\Subscription\Helper\RecurringOrderHelper;

use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Sales\Model\Order;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Ordergroove\Subscription\Exception\RecurringOrderException;
use Ordergroove\Subscription\Logger\RecurringOrder\Error\Logger as ErrorLogger;
use Ordergroove\Subscription\Logger\RecurringOrder\Info\Logger as InfoLogger;
use Ordergroove\Subscription\Model\Config\TokenBuilder;
use Ordergroove\Subscription\Model\OrderAttribute\AttributeFactory;

/**
 * Class CreateRecurringOrderHelper
 * @package Ordergroove\Subscription\Helper\RecurringOrderHelper
 */
class CreateRecurringOrderHelper
{
    /**
     * @var StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * @var ErrorLogger
     */
    protected $errorLogger;

    /**
     * @var InfoLogger
     */
    protected $infoLogger;

    /**
     * @var TokenBuilder
     */
    protected $tokenHelper;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var PaymentTokenManagementInterface
     */
    protected $paymentTokenManagement;

    /**
     * @var GetPaymentNonceCommand
     */
    protected $paymentNonce;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var ConvertArray
     */
    protected $convertArray;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * CreateRecurringOrderHelper constructor.
     * @param StockItemRepository $stockItemRepository
     * @param ErrorLogger $errorLogger
     * @param InfoLogger $infoLogger
     * @param TokenBuilder $tokenHelper
     * @param CustomerFactory $customerFactory
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param GetPaymentNonceCommand $paymentNonce
     * @param Data $jsonHelper
     * @param ConvertArray $convertArray
     */
    public function __construct(
        StockItemRepository $stockItemRepository,
        ErrorLogger $errorLogger,
        InfoLogger $infoLogger,
        TokenBuilder $tokenHelper,
        CustomerFactory $customerFactory,
        PaymentTokenManagementInterface $paymentTokenManagement,
        GetPaymentNonceCommand $paymentNonce,
        Data $jsonHelper,
        ConvertArray $convertArray,
        AttributeFactory $attributeFactory
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->errorLogger = $errorLogger;
        $this->infoLogger = $infoLogger;
        $this->tokenHelper = $tokenHelper;
        $this->customerFactory = $customerFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->paymentNonce = $paymentNonce;
        $this->jsonHelper = $jsonHelper;
        $this->convertArray = $convertArray;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @param $productId
     * @throws NoSuchEntityException
     * @throws RecurringOrderException
     */
    public function getStockStatus($productId)
    {
        $productStatus = $this->stockItemRepository->get($productId)->getIsInStock();
        if (!$productStatus) {
            $this->errorLogger->error("Requested product ID $productId is currently not available");
            throw new RecurringOrderException(__("Requested product ID $productId is currently not available"), null, "999");
        }
    }

    /**
     * @param $productId
     * @param $qty
     * @throws NoSuchEntityException
     * @throws RecurringOrderException
     */
    public function getStockQty($productId, $qty)
    {
        $productQty = $this->stockItemRepository->get($productId)->getQty();
        if ($qty > $productQty) {
            throw new RecurringOrderException(__("Requested product ID = $productId with quantity = $qty is currently not available"), null, "999");
        }
    }

    /**
     * @param $orderTokenId
     * @return array
     * @throws RecurringOrderException
     */
    public function parseTokenData($orderTokenId)
    {
        $splitToken = $this->tokenHelper->splitToken($orderTokenId);
        if (empty($splitToken) || !$splitToken['token']) {
            $this->errorLogger->error("We cannot parse the token $orderTokenId because it is either empty or not in a correct format");
            throw new RecurringOrderException(__("We cannot parse the token because it is either empty or not in a correct format."), null, "999");
        }
        return $splitToken;
    }

    /**
     * @param $email
     * @param $websiteId
     * @return array
     * @throws LocalizedException
     * @throws RecurringOrderException
     */
    public function checkCustomerData($email, $websiteId)
    {
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customerId = $customer->loadByEmail($email)->getEntityId();

        if (!$customerId) {
            $this->errorLogger->error("Customer with email = $email Does not exist");
            throw new RecurringOrderException(__("Customer with email = $email Does not exist"), null, "999");
        }

        return ['customerId' => $customerId];
    }

    /**
     * @param $tokenData
     * @param $customerId
     * @return array
     * @throws RecurringOrderException
     */
    public function validateToken($tokenData, $customerId)
    {
        $token = $tokenData['token'];
        $paymentMethod = $tokenData['method'];
        $cardList = $this->paymentTokenManagement->getByGatewayToken($token, $paymentMethod, $customerId);

        if (empty($cardList)) {
            throw new RecurringOrderException(__("No default card on file"), null, "170");
        }

        if (!$cardList['is_active']) {
            $this->errorLogger->error("The given card is not active in customer account");
            throw new RecurringOrderException(__("The given card is not active in customer account"), null, "020");
        }

        if (!($cardList['gateway_token'] === $token)) {
            $this->errorLogger->error("Token $token does not match with store customer payment details.");
            throw new RecurringOrderException(__("Token does not match with store customer payment details."), null, "999");
        }

        return [
            'token' => $token,
            'public_hash' => $cardList['public_hash'],
            'method' => $cardList['payment_method_code']
        ];
    }

    /**
     * @param $tokenData
     * @param $customerId
     * @return array
     * @throws CommandException
     * @throws LocalizedException
     * @throws RecurringOrderException
     */
    public function createPaymentMethodNonce($tokenData, $customerId)
    {
        // select first card from saved cards for the customer
        $public_hash = $tokenData['public_hash'];
        $paymentMethod = $tokenData['method'];

        $getPaymentMethodNonce = $this->paymentNonce->execute([
            'public_hash' => $public_hash,
            'customer_id' => $customerId
        ]);

        // Above method returns object, This returns array interpretation
        $paymentMethodNonce = $getPaymentMethodNonce->get();

        if (!$paymentMethodNonce) {
            $this->errorLogger->error("There is an issue with provided Payment Information.");
            throw new RecurringOrderException(__("There is an issue with provided Payment Information."), null, "020");
        }

        return [
            'method' => $paymentMethod,
            'public_hash' => $public_hash,
            'payment_method_nonce' => $paymentMethodNonce['paymentMethodNonce']
        ];
    }

    /**
     * @param Order $order
     * @param $data
     * @return array
     * @throws RecurringOrderException
     */
    public function afterPlaceOrder(Order $order, $data)
    {
        $order->setEmailSent(1);
        if (!$order->getEntityId()) {
            $this->errorLogger->error("There is an issue with provided Payment Information.");
            throw new RecurringOrderException(__("Technical error occured, Please try again."), null, "020");
        }

        $items = $data['items']['item'];
        $orderItemDiscount = 0.00;
        $orderItems = [];
        foreach ($items as $item) {
            foreach ($order->getAllVisibleItems() as $orderItem) {
                if ($item['product_id'] === $orderItem['product_id']) {
                    $orderItem->setOriginalPrice((float)($item['finalPrice'] + $item['discount']));
                    $orderItem->setOrdergrooveDiscountValue($orderItem->getDiscountAmount() + ($item['discount'] * $orderItem->getQtyOrdered()));
                    $orderItems [] = $orderItem;
                    $orderItemDiscount += $item['discount'] * $orderItem->getQtyOrdered();
                }
            }
        }
        $orderDiscount = $order->getDiscountAmount();
        $order->addCommentToStatusHistory('Total Ordergroove Discount For Recurring Order is $' . number_format(($orderDiscount + $orderItemDiscount), 2));
        $order->setItems($orderItems);

        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes->setOrdergrooveOrderType("Ordergroove Subscription");
        $order->setExtensionAttributes($extensionAttributes);
        $order->save();

        $ordergrooveOrderType = $this->attributeFactory->create();
        $collection = $ordergrooveOrderType->getCollection()->addFieldToFilter('order_id', ['eq' => $order->getEntityId()])->getFirstItem();

        $collectionRecord = $ordergrooveOrderType->load($collection->getEntityId());
        if ($collectionRecord) {
            $ordergrooveOrderType->setOrdergrooveOrderType($extensionAttributes->getOrdergrooveOrderType());
            $ordergrooveOrderType->save();
        }

        $increment_id = $order->getIncrementId();
        $customerEmail = $order->getCustomerEmail();
        $this->infoLogger->info("Subscription Order ID $increment_id belongs to $customerEmail has been placed successfully");
        return ['orderId' => $increment_id];
    }

    /**
     * @param $postContent
     * @return mixed
     */
    public function parseXmlToArray($postContent)
    {
        $orderDataFromXml = (array)simplexml_load_string($postContent);
        $orderDataFromXmlToJson = $this->jsonHelper->jsonEncode($orderDataFromXml);
        return $this->jsonHelper->jsonDecode($orderDataFromXmlToJson);
    }
}
