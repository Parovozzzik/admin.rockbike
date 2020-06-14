<?php

namespace App\Helpers;

use App\Settings\DB\DB;

/**
 * Class DbHelper
 * @package App\Helpers
 */
class DbHelper
{
    /**
     * @param string $tableName
     * @return bool
     */
    public static function tableExists(string $tableName): bool
    {
        $dbName = getenv('DB_DATABASE');
        $query =
            "SELECT * " .
            "FROM information_schema.tables " .
            "WHERE table_schema = '{$dbName}' " .
                "AND table_name = '{$tableName}' " .
            "LIMIT 1;";
        $db = DB::connection();

        return $db->query($query)->rowCount() > 0;
    }

    /**
     * @param string $tableName
     * @return mixed
     */
    public static function getColumnNameByPrimaryKey(string $tableName)
    {
        $query =
            "SHOW KEYS " .
            "FROM {$tableName} " .
            "WHERE Key_name = 'PRIMARY';";

        $db = DB::connection();

        return $db->query($query)->fetch(\PDO::FETCH_ASSOC)['Column_name'];
    }
}