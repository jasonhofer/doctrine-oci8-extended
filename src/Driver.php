<?php

namespace Doctrine\DBAL\Driver\OCI8Ext;

use Doctrine\DBAL\Driver\OCI8\Driver as BaseDriver;
use Doctrine\DBAL\Driver\OCI8\OCI8Exception;
use Doctrine\DBAL\DBALException;

class Driver extends BaseDriver
{
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
        } catch (OCI8Exception $e) {
            throw DBALException::driverException($this, $e);
        }
    }
}

