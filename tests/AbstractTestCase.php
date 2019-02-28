<?php

/*
 * This file is part of the doctrine-oci8-extended package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\DBAL\Test;

use Doctrine\DBAL;
use PHPUnit_Framework_TestCase;
use Doctrine\DBAL\Driver\OCI8Ext\Driver;
use ReflectionObject;
use RuntimeException;
use function getenv;
use function implode;
use function oci_close;
use function oci_connect;
use function oci_error;
use function oci_execute;
use function oci_parse;
use function sprintf;
use function strpos;
use function strrpos;
use function strtoupper;
use function substr;

/**
 * Class AbstractTestCase
 *
 * @package Doctrine\DBAL\Test
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-23 4:26 PM
 */
abstract class AbstractTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var DBAL\Connection
     */
    private $connection;

    /**
     * @var OciWrapper
     */
    private $oci;

    /**
     * @return DBAL\Connection
     *
     * @throws DBAL\DBALException
     */
    protected function getConnection() : DBAL\Connection
    {
        if ($this->connection) {
            return $this->connection;
        }

        $params = [
            'user'        => getenv('DB_USER'),
            'password'    => getenv('DB_PASSWORD'),
            'host'        => getenv('DB_HOST'),
            'port'        => getenv('DB_PORT'),
            'dbname'      => getenv('DB_SCHEMA'),
            'driverClass' => Driver::class,
        ];

        $config = new DBAL\Configuration();

        return $this->connection = DBAL\DriverManager::getConnection($params, $config);
    }

    protected function getPropertyValue($obj, $prop)
    {
        $rObj  = new ReflectionObject($obj);
        $rProp = $rObj->getProperty($prop);
        $rProp->setAccessible(true);

        return $rProp->getValue($obj);
    }

    protected function invokeMethod($obj, $method, array $args = [])
    {
        $rObj    = new ReflectionObject($obj);
        $rMethod = $rObj->getMethod($method);
        $rMethod->setAccessible(true);

        return $rMethod->invokeArgs($obj, $args);
    }

    /**
     * @return OciWrapper
     */
    protected function oci() : OciWrapper
    {
        return $this->oci ?: ($this->oci = new OciWrapper());
    }
}

// Utility class for performing database setup and tear-down.
class OciWrapper
{
    private $dbh;

    public function connect()
    {
        if (!$this->dbh) {
            $this->dbh = oci_connect(
                getenv('DB_USER'),
                getenv('DB_PASSWORD'),
                '//' . getenv('DB_HOST') . ':' . getenv('DB_PORT') . '/' . getenv('DB_SCHEMA'),
                getenv('DB_CHARSET'),
                OCI_DEFAULT
            );
            if (!$this->dbh) {
                /** @var array $m */
                $m = oci_error();
                throw new RuntimeException($m['message']);
            }
        }

        return $this->dbh;
    }

    public function parse($sql)
    {
        return oci_parse($this->connect(), $sql);
    }

    public function execute($sql)
    {
        $stmt = $this->parse($sql);

        return oci_execute($stmt) ? $stmt : false;
    }

    public function createTable($name, array $columns)
    {
        $this->drop('table', $name);

        return $this->execute(sprintf('CREATE TABLE %s (%s)', $name, implode(', ', $columns)));
    }

    /**
     * https://stackoverflow.com/questions/1799128/oracle-if-table-exists
     *
     * @param string $type
     * @param string $name
     *
     * @return bool
     */
    public function drop($type, $name) : bool
    {
        static $codes = [
            'COLUMN'     => '-904',
            'TABLE'      => '-942',
            'CONSTRAINT' => '-2443',
            'FUNCTION'   => '-4043',
            'PACKAGE'    => '-4043',
            'PROCEDURE'  => '-4043',
        ];
        $type = strtoupper($type);
        $code = $codes[$type];
        if (false !== strpos('COLUMN CONSTRAINT', $type)) {
            $pos    = strrpos($name, '.');
            $table  = substr($name, 0, $pos);  // "PACKAGE_NAME.TABLE_NAME" or just "TABLE_NAME"
            $column = substr($name, $pos + 1); // "COLUMN_NAME"
            $query  = "ALTER TABLE {$table} DROP {$type} {$column}";
        } else {
            $query = "DROP {$type} {$name}";
        }
        $sql = "
            BEGIN
               EXECUTE IMMEDIATE '{$query}';
            EXCEPTION
               WHEN OTHERS THEN
                  IF SQLCODE != {$code} THEN
                     RAISE;
                  END IF;
            END;
        ";

        return (bool) $this->execute($sql);
    }

    public function close() : bool
    {
        $result    = oci_close($this->dbh);
        $this->dbh = null;

        return $result;
    }
}
