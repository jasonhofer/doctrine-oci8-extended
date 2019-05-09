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

use function oci_new_cursor;

/**
 * Class OCI8Cursor
 *
 * @package Doctrine\DBAL\Driver\OCI8Ext
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-21 7:56 PM
 */
class OCI8Cursor extends OCI8Statement
{
    /** @noinspection PhpMissingParentConstructorInspection */
    /** @noinspection MagicMethodsValidityInspection */
    /**
     * @param resource       $dbh
     * @param OCI8Connection $conn
     * @param resource       $sth
     *
     * @override
     */
    public function __construct($dbh, OCI8Connection $conn, $sth = null)
    {
        $this->_dbh  = $dbh;
        $this->_conn = $conn;
        $this->_sth  = $sth ?: oci_new_cursor($dbh);
    }
}
