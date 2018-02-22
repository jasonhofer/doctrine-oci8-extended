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

use Doctrine\DBAL\Driver\OCI8\OCI8Statement as BaseStatement;

/**
 * Class OCI8Statement
 *
 * @package Doctrine\DBAL\Driver\OCI8Ext
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-21 7:54 PM
 */
class OCI8Statement extends BaseStatement
{
    /**
     * Holds references to bound parameter values.
     *
     * This is a new requirement for PHP7's oci8 extension that prevents bound values from being garbage collected.
     *
     * @see \Doctrine\DBAL\Driver\OCI8\OCI8Statement::$boundValues
     *
     * @var array
     */
    private $references = [];

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param string          $column
     * @param mixed           $variable
     * @param int|string|null $type
     * @param int|null        $length
     *
     * @return bool
     */
    public function bindParam($column, &$variable, $type = \PDO::PARAM_STR, $length = null)
    {
        $origCol = $column;
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $column  = isset($this->_paramMap[$column]) ? $this->_paramMap[$column] : $column;
        $ociType = null;

        // Figure out the type.
        if (is_numeric($type)) {
            $type = (int) $type;
        } elseif (0 === strpos($type, 'OCI_') || 0 === strpos($type, 'SQLT_')) {
            $ociType = constant($type); // Allow "OCI_" and "SQLT_" constants as strings.
        }

        // Type: Cursor.
        if (\PDO::PARAM_STMT === $type || OCI_B_CURSOR === $ociType) {
            $variable = new OCI8Cursor($this->_dbh, $this->_conn);

            $this->references[$column] =& $variable;

            return oci_bind_by_name($this->_sth, $column, $variable->_sth, -1, OCI_B_CURSOR);
        }

        // Type: Null.
        if (null === $variable) {
            $this->references[$column] =& $variable;

            return oci_bind_by_name($this->_sth, $column, $variable);
        }

        // Type: Array.
        if (is_array($variable)) {
            $length = null === $length ? -1 : $length;

            if (!$ociType) {
                $ociType = \PDO::PARAM_INT === $type ? SQLT_INT : SQLT_STR;
            }
            
            $this->references[$column] =& $variable;

            return oci_bind_array_by_name(
                $this->_sth,
                $column,
                $variable,
                max(count($variable), 1),
                empty($variable) ? 0 : $length,
                $ociType
            );
        }

        // Type: Lob
        if (OCI_B_CLOB === $ociType || OCI_B_BLOB === $ociType) {
            $type = \PDO::PARAM_LOB;
        } elseif ($ociType) {
            $this->references[$column] =& $variable;

            return oci_bind_by_name(
                $this->_sth, $column,
                $variable,
                null === $length ? -1 : $length,
                $ociType
            );
        }

        return parent::bindParam($origCol, $variable, $type, $length);
    }
}
