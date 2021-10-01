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

namespace Edirect\Erli\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PaymentMethod
 */
class Erli extends AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'erli';
}
