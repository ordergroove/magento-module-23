<?php

namespace Ordergroove\Subscription\Plugin\SalesRule\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Ordergroove\Subscription\Helper\ConfigHelper;

/**
 * Class RulesApplier
 * @package Ordergroove\Subscription\Plugin\SalesRule\Model
 */
class RulesApplier
{
    /**
     * @var CollectionFactory
     */
    protected $ruleCollection;

    /**
     * @var ShippingAssignmentInterface
     */
    protected $shippingAssignment;

    /**
     * @var CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * RulesApplier constructor.
     * @param CollectionFactory $rulesFactory
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param CookieManagerInterface $cookieManager
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        CollectionFactory $rulesFactory,
        ShippingAssignmentInterface $shippingAssignment,
        CookieManagerInterface $cookieManager,
        ConfigHelper $configHelper,
        Session $customerSession
    ) {
        $this->ruleCollection = $rulesFactory;
        $this->shippingAssignment = $shippingAssignment;
        $this->_cookieManager = $cookieManager;
        $this->configHelper = $configHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\SalesRule\Model\RulesApplier $subject
     * @param \Closure $proceed
     * @param $item
     * @param $rules
     * @param $skipValidation
     * @param $couponCode
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundApplyRules(
        \Magento\SalesRule\Model\RulesApplier $subject,
        \Closure $proceed,
        $item,
        $rules,
        $skipValidation,
        $couponCode
    ) {
        $address = $item->getAddress();
        if ($address->getIsOrdergrooveDiscount()) {
            $rules = $this->ruleCollection->create()->addFieldToFilter("rule_id", ["eq" => ""]);
            return $proceed($item, $rules, $skipValidation, $couponCode);
        }

        return $proceed($item, $rules, $skipValidation, $couponCode);
    }
}
