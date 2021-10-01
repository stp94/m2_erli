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

namespace Edirect\Erli\Plugin\Sales\Order;

use Magento\Framework\DB\Select;

/**
 * Plugin Class Grid
 */
class Grid
{

    /**
     * @var string
     */
    public static $table = 'sales_order_grid';

    /**
     * @var string
     */
    public static $leftJoinTable = 'sales_order';

    /**
     * @param $intercepter
     * @param $collection
     * @return mixed
     */
    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {

            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);

            $collection
                ->getSelect()
                ->joinLeft(
                    ['so' => $leftJoinTableName],
                    "so.entity_id = main_table.entity_id",
                    ['erli_id' => 'so.erli_id']
                )
                ->group("main_table.entity_id");

            $where = $collection->getSelect()->getPart(Select::WHERE);
            $collection->getSelect()->setPart(Select::WHERE, $where);
        }

        return $collection;
    }
}
