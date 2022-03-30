<?php
declare(strict_types=1);

namespace Ordergroove\Subscription\Model\RecurringOrder;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Webapi\Exception;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order;
use Magento\Store\Model\Store;
use Ordergroove\Subscription\Helper\RecurringOrderHelper\BraintreeSearchHelper;
use Ordergroove\Subscription\Helper\RecurringOrderHelper\BundleRecurringOrderHelper;
use Ordergroove\Subscription\Helper\RecurringOrderHelper\CreateRecurringOrderHelper;
use Ordergroove\Subscription\Logger\RecurringOrder\Error\Logger as ErrorLogger;
use Ordergroove\Subscription\Logger\RecurringOrder\Info\Logger as InfoLogger;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class CreateRecurringOrder
 * @package Ordergroove\Subscription\Model\RecurringOrder
 */
class CreateRecurringOrder
{
    /**
     * @var Product
     */
    protected $product;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var ErrorLogger
     */
    protected $errorLogger;

    /**
     * @var InfoLogger
     */
    protected $infoLogger;

    /**
     * @var CreateRecurringOrderHelper
     */
    protected $createRecurringOrderHelper;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var BundleRecurringOrderHelper
     */
    protected $bundleRecurringOrderHelper;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var BraintreeSearchHelper
     */
    protected $braintreeSearchHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DeltaPriceRound
     */
    protected $deltaPriceRound;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;


    /**
     * CreateRecurringOrder constructor.
     * @param Product $product
     * @param QuoteFactory $quoteFactory
     * @param QuoteManagement $quoteManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Store $store
     * @param ErrorLogger $errorLogger
     * @param InfoLogger $infoLogger
     * @param CreateRecurringOrderHelper $createRecurringOrderHelper
     * @param QuoteRepository $quoteRepository
     * @param BundleRecurringOrderHelper $bundleRecurringOrderHelper
     * @param Cart $cart
     * @param BraintreeSearchHelper $braintreeSearchHelper
     * @param ProductRepositoryInterface $productRepository
     * @param DeltaPriceRound $deltaPriceRound
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Product $product,
        QuoteFactory $quoteFactory,
        QuoteManagement $quoteManagement,
        CustomerRepositoryInterface $customerRepository,
        Store $store,
        ErrorLogger $errorLogger,
        InfoLogger $infoLogger,
        CreateRecurringOrderHelper $createRecurringOrderHelper,
        QuoteRepository $quoteRepository,
        BundleRecurringOrderHelper $bundleRecurringOrderHelper,
        Cart $cart,
        BraintreeSearchHelper $braintreeSearchHelper,
        ProductRepositoryInterface $productRepository,
        DeltaPriceRound $deltaPriceRound,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->product = $product;
        $this->quoteFactory = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->customerRepository = $customerRepository;
        $this->store = $store;
        $this->errorLogger = $errorLogger;
        $this->infoLogger = $infoLogger;
        $this->createRecurringOrderHelper = $createRecurringOrderHelper;
        $this->quoteRepository = $quoteRepository;
        $this->bundleRecurringOrderHelper = $bundleRecurringOrderHelper;
        $this->cart = $cart;
        $this->braintreeSearchHelper = $braintreeSearchHelper;
        $this->productRepository = $productRepository;
        $this->deltaPriceRound = $deltaPriceRound;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws CommandException
     * @throws \Exception
     */
    public function placeRecurringOrder(array $data)
    {
        try {
            // check if product exists
            $orderOgId = $data['head']['orderOgId'];
            $orderTokenId = $data['head']['orderTokenId'];
            $parseTokenData = $this->createRecurringOrderHelper->parseTokenData($orderTokenId);
            $customerEmail = $data['customer']['customerEmail'];
            $customerInfo = $this->createRecurringOrderHelper->checkCustomerData($customerEmail, $parseTokenData['website_id']);

            $ccData = [
                'orderCcType' => $data['head']['orderCcType'],
                'expirationDate' => $data['head']['orderCcExpire'],
                'orderTokenId' => $parseTokenData['token'],
                'customerEmail' => $data['customer']['customerEmail'],
                'websiteId' => $parseTokenData['website_id']
            ];

            $validateTokenData = $this->createRecurringOrderHelper->validateToken($parseTokenData, $customerInfo['customerId']);
            $billingAddress = $this->braintreeSearchHelper->checkCreditCardValidity($ccData);

            $customer = $this->customerRepository->getById($customerInfo['customerId']);

            // Build order data
            $orderData = [
                'email' => $data['customer']['customerEmail'],
                'shipping_address' => [
                    'firstname' => $data['customer']['customerFirstName'],
                    'lastname' => $data['customer']['customerLastName'],
                    'street' => $data['customer']['customerShippingAddress'],
                    'city' => $data['customer']['customerShippingCity'],
                    'country_id' => $data['customer']['customerShippingCountry'],
                    'region' => $data['customer']['customerShippingState'],
                    'postcode' => $data['customer']['customerShippingZip'],
                    'telephone' => $data['customer']['customerShippingPhone']
                ],
                'billing_address' => [
                    'firstname' => $billingAddress['firstName'],
                    'lastname' => $billingAddress['lastName'],
                    'street' => $billingAddress['streetAddress'],
                    'city' => $billingAddress['locality'],
                    'country_id' => $billingAddress['countryCodeAlpha2'],
                    'region' => $data['customer']['customerShippingState'],
                    'postcode' => $billingAddress['postalCode'],
                    'telephone' => $data['customer']['customerShippingPhone']
                ],
                'shipping' => $data['head']['orderShipping'],
                'orderSubtotalDiscount' => $data['head']['orderSubtotalDiscount']
            ];

            $quote = $this->quoteFactory->create();
            $quote->assignCustomer($customer);

            if (!isset($data['items']['item'][0])) {
                $data['items'][0] = $data['items']['item'];
                unset($data['items']['item']);
                $data['items']['item'][0] = $data['items'][0];
                unset($data['items'][0]);
            }

            $items = $data['items']['item'];

            foreach ($items as $item) {
                $productId = $item['product_id'];
                if (isset($item['components'])) {
                    $paramsObject = $this->addBundleProductToCart($productId, $item);
                } else {
                    $paramsObject = $this->addSimpleAndConfigurableToCart($productId, $item);
                }
                $product = $this->productRepository->getById($productId, false, null, true);
                $quote->addProduct($product, new DataObject($paramsObject));
            }

            $this->cart->save();
            // Set Addresses to quote
            $quote->getBillingAddress()->addData($orderData['billing_address']);
            $quote->getShippingAddress()->addData($orderData['shipping_address']);

            // Collect shipping rates, set Shipping & Payment Method
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setIsOrdergrooveShipping(true);
            $shippingAddress->setOrdergrooveShippingAmount(floatval($orderData['shipping']));

            // Add Ordergroove custom discount amount
            $shippingAddress->setIsOrdergrooveDiscount(true);
            $shippingAddress->setOrdergrooveCustomOrderDiscount(floatval($orderData['orderSubtotalDiscount']));

            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod('flatrate_flatrate');
            $quote->setPaymentMethod($validateTokenData['method']);
            $quote->setInventoryProcessed(false);
            $quote->setIsMultiShipping(0);
            $this->quoteRepository->save($quote);

            // Distribute orderSubtotalDiscount amongst line items
            $orderSubtotalDiscount = $orderData['orderSubtotalDiscount'];
            $quoteItems = $quote->getAllItems();
            $countOfItems = count($quoteItems);

            foreach ($quoteItems as $quoteItem) {
                if ($countOfItems > 1) {
                    $ratio = $quoteItem->getPrice() / $quote->getBaseSubtotal();
                    $quoteItemDiscount = $orderData['orderSubtotalDiscount'] * $ratio;
                    $quoteItemDiscount = $this->priceCurrency->convert($quoteItemDiscount);
                    $quoteItemFormattedDiscount = $this->deltaPriceRound->round($quoteItemDiscount, 'regular');
                    $quoteItem->setDiscountAmount(($quoteItem->getDiscountAmount() + $quoteItemFormattedDiscount) * $quoteItem->getQty());
                    $quoteItem->setBaseDiscountAmount(($quoteItem->getBaseDiscountAmount() + $quoteItemFormattedDiscount) * $quoteItem->getQty())->save();
                } else {
                    $quoteItem->setDiscountAmount(($quoteItem->getDiscountAmount() + $orderSubtotalDiscount) * $quoteItem->getQty());
                    $quoteItem->setBaseDiscountAmount(($quoteItem->getBaseDiscountAmount() + $orderSubtotalDiscount) * $quoteItem->getQty())->save();
                }
            }

            $extraPaymentData = $this->createRecurringOrderHelper->createPaymentMethodNonce($validateTokenData, $customerInfo['customerId']);
            $quote->getPayment()->importData($extraPaymentData);
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);
            return $this->createRecurringOrderHelper->afterPlaceOrder($order, $data);
        } catch (\Exception $e) {
            $this->errorLogger->error("Error on OG order " . $orderOgId . " " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $productId
     * @param $item
     * @return array
     */
    public function addBundleProductToCart($productId, $item)
    {
        $bundleProductIds = $this->bundleRecurringOrderHelper->bundleProductsInRecurringOrder($item);
        $countOfEachBundleProduct = array_count_values($bundleProductIds);
        $getBundleOptions = $this->bundleRecurringOrderHelper->getBundleOptions($productId, $bundleProductIds);
        $getBundleOptionsQty = $this->bundleRecurringOrderHelper->getBundleOptionsQtyFromOG($productId, $countOfEachBundleProduct);

        return [
            'bundle_option' => $getBundleOptions,
            'bundle_option_qty' => $getBundleOptionsQty,
            'qty' => intval($item['qty']),
            'custom_price' => $item['finalPrice']
        ];
    }

    /**
     * @param $productId
     * @param $item
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function addSimpleAndConfigurableToCart($productId, $item)
    {
        $quantity = $item['qty'];
        $this->createRecurringOrderHelper->getStockStatus($productId);
        $this->createRecurringOrderHelper->getStockQty($productId, $quantity);
        return [
            'qty' => $item['qty'],
            'custom_price' => $item['finalPrice']
        ];
    }
}
