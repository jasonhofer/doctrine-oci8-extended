<?php

namespace Doctrine\DBAL\Driver\OCI8Ext;

use Doctrine\DBAL\Driver\OCI8\OCI8Connection as BaseConnection;

class OCI8Connection extends BaseConnection
{
    public function prepare($prepareString)
    {
        return new OCI8Statement($this->dbh, $prepareString, $this);
    }
}

