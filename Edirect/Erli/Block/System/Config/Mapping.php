<?php
/**
 * Created in PhpStorm.
 * by marcin.paul
 * on 07, styczeń, 10:54, 2021
 * @category Edirect
 * @package  Edirect_Module
 * @author   marcin.paul <marcin.paul@edirect24.pl>
 * @projekt   Erli
 * @copyright Copyright (C) 2020 marcin.paul
 **/

namespace Edirect\Erli\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Widget\Button;

/**
 * Block Class Mapping
 */
class Mapping extends Field
{

    /**
     * @var string
     */
    protected $_template = 'Edirect_Erli::system/config/mapping.phtml';

    /**
     * @param AbstractElement $element
     * @return mixed
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return mixed
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return mixed
     */
    public function getButtonUrl()
    {
        return $this->getUrl('erli/system_config/mapping');
    }

    /**
     * @return mixed
     */
    public function getButtonHtml()
    {
        $url = $this->getButtonUrl();
        $button = $this->getLayout()->createBlock(Button::class)
            ->setData(
                [
                    'id' => 'mapping_button_id',
                    'label' => __('Shipping Method Mapping'),
                    'onclick' => "setLocation('$url')"
                ]
            );

        return $button->toHtml();
    }
}
