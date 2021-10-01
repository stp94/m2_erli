<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 04.02.2021, 11:38, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Edirect\Erli\Helper\Erli;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Registry;

class ErliPriceList extends AbstractSource
{
    /**
     * @var Erli
     */
    protected $erliHelper;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * ErliPriceList constructor.
     * @param Erli $erliHelper
     * @param Json $json
     */
    public function __construct(
        Erli $erliHelper,
        Json $json,
        Registry $registry
    ) {
        $this->erliHelper = $erliHelper;
        $this->json = $json;
        $this->registry = $registry;
    }

    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {

            if ($this->erliHelper->getPriceList()) {
                $priceList = $this->erliHelper->getPriceList();

                foreach ($priceList as $priceListItem) {
                    $this->_options[] = [
                        'label' => $priceListItem,
                        'value' => $priceListItem
                    ];
                }
            } else {
                if ($this->getCurrentProduct() && !empty($this->getCurrentProduct()->getErliPriceList())) {
                    $productPriceList = $this->getCurrentProduct()->getErliPriceList();

                    $this->_options[] = [
                        'label' => $productPriceList,
                        'value' => $productPriceList
                    ];
                } else {
                    $this->_options[] = [
                        'label' => "can't get price list",
                        'value' => ''
                    ];
                }
            }

        }

        return $this->_options;
    }

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }

        return false;
    }
}
