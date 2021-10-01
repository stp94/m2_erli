<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 19.01.2021, 15:38, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Api;

interface HooksInterface
{

    /**
     * Retrieve list of products stock information
     *
     * @return \Edirect\Erli\Api\HooksInterface[]
     */
    public function checkBuyAbility();
}
