<?php

namespace Ordergroove\Subscription\Plugin\SalesRule\Model\Condition;

use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Rule\Model\Condition\Combine;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Ordergroove\Subscription\Helper\ConfigHelper;
use Ordergroove\Subscription\Setup\Patch\Data\AddSubscriptionEligibleProductAttribute;

/**
 * Class CombinePlugin
 * @package Ordergroove\Subscription\Plugin\SalesRule\Model\Condition
 */
class CombinePlugin
{
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
     * @var CollectionFactory
     */
    protected $ruleCollection;

    /**
     * CombinePlugin constructor.
     * @param CookieManagerInterface $cookieManager
     * @param ConfigHelper $configHelper
     * @param Session $customerSession
     * @param CollectionFactory $rulesFactory
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        ConfigHelper $configHelper,
        Session $customerSession,
        CollectionFactory $rulesFactory
    ) {
        $this->_cookieManager = $cookieManager;
        $this->configHelper = $configHelper;
        $this->customerSession = $customerSession;
        $this->ruleCollection = $rulesFactory;
    }

    /**
     * @param Combine $subject
     * @param $result
     * @param $model
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterValidate(
        Combine $subject,
        $result,
        $model
    ) {
        $rule = $subject->getRule();
        $currentRuleId = $rule->getId();
        $subscriptionRule = $this->ruleCollection->create()->addAttributeInConditionFilter(AddSubscriptionEligibleProductAttribute::IOI_ELIGIBLE)->getFirstItem();
        $subscriptionRuleId = $subscriptionRule->getId();
        if ($subscriptionRuleId == $currentRuleId) {
            if ($this->customerSession->isLoggedIn() && $this->configHelper->isEnabled()) {
                if ($model instanceof Item) {

                    // Read child product ID as Ordergroove does not send optin information for configurable products
                    if ($model->getProduct()->getTypeId() == 'configurable') {
                        foreach ($model->getChildren() as $child) {
                            $productId = $child->getProductId();
                            $result = $this->validateOGSubscriptions($model, $productId);
                        }
                    }

                    // Read Simple and Bundles product IDs
                    if ($model->getProduct()->getTypeId() == 'simple' || $model->getProduct()->getTypeId() == 'bundle') {
                        $productId = $model->getProduct()->getId();
                        $result = $this->validateOGSubscriptions($model, $productId);
                    }
                }
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param $model
     * @param $productId
     * @return bool
     */
    private function validateOGSubscriptions($model, $productId)
    {
        if ($this->_cookieManager->getCookie('product_subscribed_' . $productId) === 'true' &&
            (int)$model->getProduct()->getData(AddSubscriptionEligibleProductAttribute::IOI_ELIGIBLE)) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }
}
