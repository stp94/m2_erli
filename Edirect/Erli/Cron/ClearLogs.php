<?php
/**
 * Created in PhpStorm.
 * by Edirect24
 * on 09.02.2021, 12:20, 2021
 * @author   biuro@edirect24.pl
 * @copyright Copyright (C) 2021 Edirect24
 **/

namespace Edirect\Erli\Cron;

use Magento\Framework\App\ResourceConnection;

/**
 * Cron to clear erli logs
 * Class ClearLogs
 */
class ClearLogs
{

    const QUOTE_TABLE = 'ed_erli_log';

    private $resourceConnection;

    /**
     * ClearLogs constructor.
     * @param LoggerInterface $loggerInterface
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $connection  = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName(self::QUOTE_TABLE);

        $connection->delete(
            $tableName,
            "created_at < date_sub(CURDATE(), INTERVAL 14 Day)"
        );

        return $this;
    }
}
