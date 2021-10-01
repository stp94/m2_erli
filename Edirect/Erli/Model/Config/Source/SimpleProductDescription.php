<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 03.02.2021, 10:42, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Model\Config\Source;

/**
 * Model Class SimpleProductDescription
 */
class SimpleProductDescription implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'simple', 'label' => __('from simple')],
            ['value' => 'configurable', 'label' => __('from configurable')]
        ];
    }
}
