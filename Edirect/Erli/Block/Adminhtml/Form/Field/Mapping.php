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

namespace Edirect\Erli\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Edirect\Erli\Block\Adminhtml\Form\Field\MagentoShippingMethodColumn as MagentoShippingMethodColumn;
use Edirect\Erli\Block\Adminhtml\Form\Field\ErliShippingMethodColumn as ErliShippingMethodColumn;

/**
 * Shipping Method Mapping
 * Class Mapping
 */
class Mapping extends AbstractFieldArray
{

    /**
     * @var MagentoShippingMethodColumn
     */
    private $magentoShippingMethodRenderer;

    /**
     * @var ErliShippingMethodColumn
     */
    private $erliShippingMethodRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('erli_shipping_method', [
            'class' => 'required-entry validate-select',
            'label' => __('Erli Shipping Method'),
            'renderer' => $this->getErliShippingMethodRenderer()
        ]);
        $this->addColumn('magento_shipping_method', [
            'class' => 'required-entry validate-select',
            'label' => __('Magento Shipping Method'),
            'renderer' => $this->getMagentoShippingMethodRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $magentoShippingMethod = $row->getMagentoShippingMethod();
        $erliShippingMethod = $row->getErliShippingMethod();

        if ($magentoShippingMethod !== null) {
            $options['option_' . $this->getMagentoShippingMethodRenderer()->calcOptionHash($magentoShippingMethod)] = 'selected="selected"';
        }

        if ($erliShippingMethod !== null) {
            $options['option_' . $this->getErliShippingMethodRenderer()->calcOptionHash($erliShippingMethod)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return MagentoShippingMethodColumn
     */
    private function getMagentoShippingMethodRenderer()
    {
        if (!$this->magentoShippingMethodRenderer) {
            $this->magentoShippingMethodRenderer = $this->getLayout()->createBlock(
                MagentoShippingMethodColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->magentoShippingMethodRenderer;
    }

    /**
     * @return MagentoShippingMethodColumn
     */
    private function getErliShippingMethodRenderer()
    {
        if (!$this->erliShippingMethodRenderer) {
            $this->erliShippingMethodRenderer = $this->getLayout()->createBlock(
                ErliShippingMethodColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->erliShippingMethodRenderer;
    }
}
