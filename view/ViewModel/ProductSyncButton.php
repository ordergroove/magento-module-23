<?php

namespace Ordergroove\Subscription\ViewModel;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Url;

class ProductSyncButton extends Template implements ArgumentInterface
{

    const CONTROLLER_PATH = "ordergrooveadmin/productsync/sync";

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Data
     */
    protected $frontNameData;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @param Context $contxt
     * @param StoreManagerInterface $storeManager
     * @param Data $data
     * @param Url $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Data $frontNameData,
        Url $url,
        array $data = []
    )
    {
        $this->storeManager = $storeManager;
        $this->frontNameData = $frontNameData;
        $this->url = $url;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'run_product_sync',
                'label' => __('Run product sync now'),
                'onclick' => "location.href='" . $this->url->getUrl(self::CONTROLLER_PATH) . "'"
            ]
        );

        return $button->toHtml();
    }
}
