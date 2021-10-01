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

namespace Edirect\Erli\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Model Class ErliShippingMethod
 */
class ErliShippingMethod extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\ErliShippingMethod::class);
        $this->setIdFieldName('entity_id');
    }
}
