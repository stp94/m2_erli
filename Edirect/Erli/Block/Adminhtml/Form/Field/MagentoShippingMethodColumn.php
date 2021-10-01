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

declare(strict_types=1);

namespace Edirect\Erli\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Template\Context;
use Magento\Shipping\Model\Config\Source\Allmethods;

/**
 * Magento Shipping Method Mapping
 * Class MagentoShippingMethodColumn
 */
class MagentoShippingMethodColumn extends Select
{

    /**
     * @var Allmethods
     */
    private $shippingAllMethods;

    /**
     * MagentoShippingMethodColumn constructor.
     * @param Context $context
     * @param Allmethods $shippingAllMethods
     * @param array $data
     */
    public function __construct(
        Context $context,
        Allmethods $shippingAllMethods,
        array $data = []
    ) {
        $this->shippingAllMethods = $shippingAllMethods;
        parent::__construct($context, $data);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        
        return parent::_toHtml();
    }

    /**
     * @return array
     */
    private function getSourceOptions(): array
    {
        $shippingMethodsArray = [];
        $shippingMethods = $this->shippingAllMethods->toOptionArray(true);

        foreach ($shippingMethods as $code => $shipping) {
            $shippingMethodsArray[] = ['label' => $shipping['label'], 'value' => $code];
        }

        return $shippingMethodsArray;
    }
}
