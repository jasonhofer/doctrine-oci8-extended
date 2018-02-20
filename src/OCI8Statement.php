<?php

namespace Doctrine\DBAL\Driver\OCI8Ext;

use Doctrine\DBAL\Driver\OCI8\OCI8Statement as BaseStatement;

class OCI8Statement extends BaseStatement
{
    public function bindParam($column, &$variable, $type = \PDO::PARAM_STR, $length = null)
    {
        if (\PDO::PARAM_STMT === $type) {
            $variable = new OCI8Cursor($this->_dbh, $this->_conn);

            return oci_bind_by_name($this->_sth, $column, $variable->_sth, -1, OCI_B_CURSOR);
        }

        return parent::bindParam($column, $variable, $type, $length);
    }
}

