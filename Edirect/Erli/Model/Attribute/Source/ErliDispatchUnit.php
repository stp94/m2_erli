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

/**
 * Model Class ErliDispatchUnit
 */
class ErliDispatchUnit extends AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Hour'), 'value' => 'hour'],
                ['label' => __('Day'), 'value' => 'day'],
                ['label' => __('Month'), 'value' => 'month'],
            ];
        }

        return $this->_options;
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
