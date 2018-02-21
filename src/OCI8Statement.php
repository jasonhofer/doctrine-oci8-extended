<?php

namespace Doctrine\DBAL\Driver\OCI8Ext;

use Doctrine\DBAL\Driver\OCI8\OCI8Statement as BaseStatement;

class OCI8Statement extends BaseStatement
{
    public function bindParam($column, &$variable, $type = \PDO::PARAM_STR, $length = null)
    {
        $origCol = $column;
        $column  = isset($this->_paramMap[$column]) ? $this->_paramMap[$column] : $column;
        $ociType = null;

        if (is_numeric($type)) {
            $type = (int) $type;
        } elseif (0 === strpos($type, 'OCI_') || 0 === strpos($type, 'SQLT_')) {
            $ociType = constant($type); // Allow "OCI_" and "SQLT_" constants as strings.
        }

        if (\PDO::PARAM_STMT === $type || OCI_B_CURSOR === $ociType) {
            $variable = new OCI8Cursor($this->_dbh, $this->_conn);

            $this->boundValues[$column] = $variable;

            return oci_bind_by_name($this->_sth, $column, $variable->_sth, -1, OCI_B_CURSOR);
        }

        if (null === $variable) {
            $this->boundValues[$column] =& $variable;

            return oci_bind_by_name($this->_sth, $column, $variable, -1, SQLT_CHR);
        }

        if (is_array($variable)) {
            $length = null === $length ? -1 : $length;

            if (!$ociType) {
                $ociType = \PDO::PARAM_INT === $type ? SQLT_INT : SQLT_STR;
            }
            
            $this->boundValues[$column] =& $variable;

            return oci_bind_array_by_name($this->_sth, $column, $variable, max(count($variable), 1), empty($variable) ? 0 : $length, $ociType);
        }

        if (OCI_B_CLOB === $ociType || OCI_B_BLOB === $ociType) {
            $type = \PDO::PARAM_LOB;
        } elseif ($ociType) {
            $this->boundValues[$column] =& $variable;

            return oci_bind_by_name($this->_sth, $column, $variable, null === $length ? -1 :  $length, $ociType);
        }

        return parent::bindParam($origCol, $variable, $type, $length);
    }
}

