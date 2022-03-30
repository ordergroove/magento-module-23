<?php

namespace Ordergroove\Subscription\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

class Disable extends Field
{
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setDisabled('disabled');
        $element->setData('readonly', 1);
        return $element->getElementHtml();
    }
}
