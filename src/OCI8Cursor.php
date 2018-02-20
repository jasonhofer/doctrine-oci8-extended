<?php

namespace Doctrine\DBAL\Driver\OCI8Ext;

class OCI8Cursor extends OCI8Statement
{
    public function __construct($dbh, $conn)
    {
        $this->_dbh  = $dbh;
        $this->_conn = $conn;
        $this->_sth  = oci_new_cursor($dbh);
    }
}

