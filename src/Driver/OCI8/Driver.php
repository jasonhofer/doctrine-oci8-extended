<?php

/*
 * This file is part of the doctrine-oci8-extended package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\DBAL\Driver\OCI8Ext;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\OCI8\Driver as BaseDriver;
use Doctrine\DBAL\Driver\OCI8\OCI8Exception;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\CursorType;
use Exception;
use const OCI_DEFAULT;

/**
 * Class Driver
 *
 * @package Doctrine\DBAL\Driver\OCI8Ext
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-21 7:55 PM
 */
class Driver extends BaseDriver
{
    /**
     * Driver constructor.
     *
     * @throws DBALException
     */
    public function __construct()
    {
        if (!Type::hasType('cursor')) {
            Type::addType('cursor', CursorType::class);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param array  $params
     * @param string $username
     * @param string $password
     * @param array  $driverOptions
     *
     * @return OCI8Connection
     *
     * @throws Exception
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array()) : OCI8Connection
    {
        try {
            return new OCI8Connection(
                $username,
                $password,
                $this->_constructDsn($params),
                $params['charset'] ?? null,
                $params['sessionMode'] ?? OCI_DEFAULT,
                $params['persistent'] ?? false
            );
        } catch (Exception $e) {
            if ($e instanceof OCI8Exception) {
                throw DBALException::driverException($this, $e);
            }
            /** @noinspection PhpUnhandledExceptionInspection */
            throw $e;
        }
    }
}
