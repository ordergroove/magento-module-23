<?php

namespace Ordergroove\Subscription\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;

class CheckConnection extends Field
{
    /**
     * @var string
     */
    protected $_template = "Ordergroove_Subscription::system/config/check_connection.phtml";

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl("ordergroove_checkconnection/system_config/connection");
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'check_connection_button',
                'label' => __('Check Connectivity'),
            ]
        );

        return $button->toHtml();
    }
}
