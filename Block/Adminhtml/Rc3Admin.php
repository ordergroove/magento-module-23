<?php
declare(strict_types=1);

namespace Ordergroove\Subscription\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Ordergroove\Subscription\Helper\ConfigHelper;

/**
 * Class Rc3Admin
 * @package Ordergroove\Subscription\Block\Adminhtml
 */
class Rc3Admin extends Template
{
    const OG_RC3_ADMIN_STAGING_URL = "rc3.stg.ordergroove.com";
    const OG_RC3_ADMIN_PRODUCTION_URL = "rc3.ordergroove.com";

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfigHelper $configHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getRc3AdminUrl()
    {
        $url = "https://";
        if ($this->configHelper->getStaging($websiteId = "")) {
            $url .= self::OG_RC3_ADMIN_STAGING_URL;
        } else {
            $url .= self::OG_RC3_ADMIN_PRODUCTION_URL;
        }
        return $url;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'go_to_rc3',
                'label' => __('Go to Ordergroove RC3 Admin'),
                'onclick' => "window.open('" . $this->getRc3AdminUrl() . "')"
            ]
        );

        return $button->toHtml();
    }
}
