<?php

namespace App\Settings\DB;

use App\Settings\Exceptions\DatabaseException;
use Spot\Config;
use Spot\Locator;

/**
 * Class Database
 * @package Slimex
 */
class Database
{
    /** @var \Spot\Locator[] */
    private static $_connections = [];
    /** @var string */
    public const DEFAULT_CONNECTION_NAME = 'mysql';

    /**
     * add connection to db
     * maybe we need to add connections map for cases, when we creating new connections
     * with same params but different names
     *
     * @param string $connectionString
     * @param null|array $driverOptions
     * @param null|string $connectionName default Database::DEFAULT_CONNECTION_NAME
     * @throws \Exception
     * @throws \Spot\Exception
     */
    public static function addConnection($connectionString, $driverOptions = null, $connectionName = null): void
    {
        $connectionName = $connectionName ?: self::DEFAULT_CONNECTION_NAME;
        if (!array_key_exists($connectionName, self::$_connections)) {
            $connectionParams = self::parseDsn($connectionString);

            $defaultOptions = [
                \PDO::ATTR_TIMEOUT => getenv('DB_CONNECTION_TIMEOUT'),
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ];
            $driverOptions = ($driverOptions ?? []) + $defaultOptions;
            $connectionParams['driverOptions'] = $driverOptions;

            $cfg = new Config();
            $cfg->addConnection($connectionName, $connectionParams);

            $cfg->connection()->getConfiguration();

            self::$_connections[$connectionName] = new Locator($cfg);
            self::$_connections[$connectionName]->config()->connection()->query('SET time_zone = \'' . getenv('APP_TIME_ZONE') . '\'');
        }
    }

    /**
     * Return db connection by name. `mysql` default
     *
     * @param null $connectionName
     * @return Locator|null
     * @throws DatabaseException
     */
    public static function db($connectionName = null): ?Locator
    {
        $connectionName = $connectionName ?: self::DEFAULT_CONNECTION_NAME;
        if (array_key_exists($connectionName, self::$_connections)) {
            return self::$_connections[$connectionName];
        }
        throw new DatabaseException("Connection \"$connectionName\" does not exist");
    }

    /**
     * Taken from \Spot\Config::parseDsn but fixed to make it work with PHP7.2 (as described at https://github.com/spotorm/spot2/pull/279 )
     *
     * Returns the Data Source Name as a structure containing the various parts of the DSN.
     *
     * Additional keys can be added by appending a URI query string to the
     * end of the DSN.
     *
     * The format of the supplied DSN is in its fullest form:
     * <code>
     *  adapter(dbsyntax)://user:password@protocol+host/database?option=8&another=true
     * </code>
     *
     * Most variations are allowed:
     * <code>
     *  adapter://user:password@protocol+host:110//usr/db_file.db?mode=0644
     *  adapter://user:password@host/database_name
     *  adapter://user:password@host
     *  adapter://user@host
     *  adapter://host/database
     *  adapter://host
     *  adapter(dbsyntax)
     *  adapter
     * </code>
     *
     * @param string $dsn Data Source Name to be parsed
     *
     * @return array an associative array with the following keys:
     *               + adapter:  Database backend used in PHP (mysql, odbc etc.)
     *               + dbsyntax: Database used with regards to SQL syntax etc.
     *               + protocol: Communication protocol to use (tcp, unix etc.)
     *               + host: Host specification (hostname[:port])
     *               + dbname: Database to use on the DBMS server
     *               + user: User name for login
     *               + password: Password for login
     */
    public static function parseDsn($dsn): array
    {
        if ($dsn === 'sqlite::memory:') {
            $dsn = 'sqlite://:memory:';
        }

        $parsed = [
            'adapter' => FALSE,
            'dbsyntax' => FALSE,
            'user' => FALSE,
            'password' => FALSE,
            'protocol' => FALSE,
            'host' => FALSE,
            'port' => FALSE,
            'socket' => FALSE,
            'dbname' => FALSE,
        ];

        if (is_array($dsn)) {
            $dsn = array_merge($parsed, $dsn);
            if (!$dsn['dbsyntax']) {
                $dsn['dbsyntax'] = $dsn['adapter'];
            }

            return $dsn;
        }

        // Find phptype and dbsyntax
        if (($pos = strpos($dsn, '://')) !== FALSE) {
            $str = substr($dsn, 0, $pos);
            $dsn = substr($dsn, $pos + 3);
        } elseif (($pos = strpos($dsn, ':')) !== FALSE) {
            $str = substr($dsn, 0, $pos);
            $dsn = substr($dsn, $pos + 1);
        } else {
            $str = $dsn;
            $dsn = null;
        }

        // Get phptype and dbsyntax
        // $str => phptype(dbsyntax)
        if (preg_match('|^(.+?)\((.*?)\)$|', $str, $arr)) {
            $parsed['adapter'] = $arr[1];
            $parsed['dbsyntax'] = !$arr[2] ? $arr[1] : $arr[2];
        } else {
            $parsed['adapter'] = $str;
            $parsed['dbsyntax'] = $str;
        }

        if (empty($dsn)) {
            return $parsed;
        }

        // Get (if found): user and password
        // $dsn => user:password@protocol+host/database
        if (($at = strrpos((string)$dsn, '@')) !== FALSE) {
            $str = substr($dsn, 0, $at);
            $dsn = substr($dsn, $at + 1);
            if (($pos = strpos($str, ':')) !== FALSE) {
                $parsed['user'] = rawurldecode(substr($str, 0, $pos));
                $parsed['password'] = rawurldecode(substr($str, $pos + 1));
            } else {
                $parsed['user'] = rawurldecode($str);
            }
        }

        // Find protocol and host

        if (preg_match('|^([^(]+)\((.*?)\)/?(.*?)$|', $dsn, $match)) {
            // $dsn => proto(proto_opts)/database
            $proto = $match[1];
            $proto_opts = $match[2] ?: false;
            $dsn = $match[3];
        } else {
            // $dsn => protocol+host/database (old format)
            if (strpos($dsn, '+') !== FALSE) {
                list($proto, $dsn) = explode('+', $dsn, 2);
            }
            if (strpos($dsn, '/') !== FALSE) {
                list($proto_opts, $dsn) = explode('/', $dsn, 2);
            } else {
                $proto_opts = $dsn;
                $dsn = null;
            }
        }

        // process the different protocol options
        $parsed['protocol'] = (!empty($proto)) ? $proto : 'tcp';
        $proto_opts = rawurldecode($proto_opts);
        if ($parsed['protocol'] === 'tcp') {
            if (strpos($proto_opts, ':') !== FALSE) {
                list($parsed['host'], $parsed['port']) = explode(':', $proto_opts);
            } else {
                $parsed['host'] = $proto_opts;
            }
        } elseif ($parsed['protocol'] === 'unix') {
            $parsed['socket'] = $proto_opts;
        }

        // Get dabase if any
        // $dsn => database
        if ($dsn) {
            if (($pos = strpos($dsn, '?')) === FALSE) {
                // /database
                $parsed['dbname'] = rawurldecode($dsn);
            } else {
                // /database?param1=value1&param2=value2
                $parsed['dbname'] = rawurldecode(substr($dsn, 0, $pos));
                $dsn = substr($dsn, $pos + 1);
                if (strpos($dsn, '&') !== FALSE) {
                    $opts = explode('&', $dsn);
                } else { // database?param1=value1
                    $opts = [$dsn];
                }
                foreach ($opts as $opt) {
                    list($key, $value) = explode('=', $opt);
                    if (!isset($parsed[$key])) {
                        // don't allow params overwrite
                        $parsed[$key] = rawurldecode($value);
                    }
                }
            }
        }

        // Replace 'adapter' with 'driver' and add 'pdo_'
        if (isset($parsed['adapter'])) {
            $parsed['driver'] = 'pdo_' . $parsed['adapter'];
            unset($parsed['adapter']);
        }

        return $parsed;
    }

}
