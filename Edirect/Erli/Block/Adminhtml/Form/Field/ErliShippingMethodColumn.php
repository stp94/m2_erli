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
use Edirect\Erli\Model\ResourceModel\ErliShippingMethod\CollectionFactory;

/**
 * Erli Shipping Method Mapping
 * Class ErliShippingMethodColumn
 */
class ErliShippingMethodColumn extends Select
{

    /**
     * @var CollectionFactory
     */
    private $shippingMethodCollection;

    /**
     * ErliShippingMethodColumn constructor.
     * @param Context $context
     * @param CollectionFactory $shippingMethodCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $shippingMethodCollection,
        array $data = []
    ) {
        $this->shippingMethodCollection = $shippingMethodCollection;
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
        $this->setExtraParams('style="pointer-events:none;"');

        return parent::_toHtml();
    }

    /**
     * @return array
     */
    private function getSourceOptions(): array
    {
        $shippingMethodsArray = [];
        $shippingMethodsArray[] = ['label' => '', 'value' => ''];
        $shippingMethods = $this->shippingMethodCollection->create();

        foreach ($shippingMethods as $method) {
            $shippingMethodsArray[] = ['label' => $method->getMethodName(), 'value' => $method->getMethodId()];
        }

        return $shippingMethodsArray;
    }
}
