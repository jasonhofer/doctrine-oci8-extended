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

/**
 * Class Driver
 *
 * @package Doctrine\DBAL\Driver\OCI8Ext
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-21 7:55 PM
 */
class Driver extends BaseDriver
{
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param array  $params
     * @param string $username
     * @param string $password
     * @param array  $driverOptions
     *
     * @return OCI8Connection
     *
     * @throws \Exception
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        try {
            return new OCI8Connection(
                $username,
                $password,
                $this->_constructDsn($params),
                isset($params['charset']) ? $params['charset'] : null,
                isset($params['sessionMode']) ? $params['sessionMode'] : OCI_DEFAULT,
                isset($params['persistent']) ? $params['persistent'] : false
            );
        } catch (\Exception $e) {
            if ($e instanceof OCI8Exception) {
                throw DBALException::driverException($this, $e);
            }
            /** @noinspection PhpUnhandledExceptionInspection */
            throw $e;
        }
    }
}
