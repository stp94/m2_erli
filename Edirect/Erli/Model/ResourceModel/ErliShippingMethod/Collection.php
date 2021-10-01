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

namespace Edirect\Erli\Model\ResourceModel\ErliShippingMethod;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Model Class Collection
 */
class Collection extends AbstractCollection
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Edirect\Erli\Model\ErliShippingMethod::class,
            \Edirect\Erli\Model\ResourceModel\ErliShippingMethod::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
