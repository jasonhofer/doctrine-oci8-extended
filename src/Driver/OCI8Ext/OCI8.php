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

const PARAM_PREFIX = 0xA000;
const PARAM_MAX    = 0xAFFF;

/**
 * Class OCI8
 *
 * @package Doctrine\DBAL\Driver\OCI8Ext
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-22 9:55 AM
 */
final class OCI8
{
    const RETURN_RESOURCES = 0x0100;
    const RETURN_CURSORS   = 0x0200;

    // OCI8::PARAM_* constants are prefixed with binary 1010 (0xA) in the -4th nibble.
    const PARAM_CHR     = 0xA001;
    const PARAM_NUM     = 0xA002;
    const PARAM_INT     = 0xA003;
    const PARAM_FLT     = 0xA004;
    const PARAM_STR     = 0xA005;
    const PARAM_LNG     = 0xA008;
    const PARAM_VCS     = 0xA009;
    const PARAM_BFLOAT  = 0xA015;
    const PARAM_BDOUBLE = 0xA016;
    const PARAM_BIN     = 0xA017;
    const PARAM_LBI     = 0xA018;
    const PARAM_UIN     = 0xA044;
    const PARAM_LVC     = 0xA05E;
    const PARAM_AFC     = 0xA060;
    const PARAM_AVC     = 0xA061;
    const PARAM_ROWID   = 0xA068;
    const PARAM_NTY     = 0xA06C;
    const PARAM_CLOB    = 0xA070;
    const PARAM_BLOB    = 0xA071;
    const PARAM_BFILEE  = 0xA072;
    const PARAM_CFILEE  = 0xA073;
    const PARAM_CURSOR  = 0xA074;
    const PARAM_ODT     = 0xA09C;
    const PARAM_BOOL    = 0xA0FC;

    /**
     * @param int $value
     *
     * @return bool
     */
    public static function isParamConstant($value)
    {
        return $value >= PARAM_PREFIX && $value <= PARAM_MAX;
    }

    /**
     * @param int $value
     *
     * @return int
     */
    public static function decodeParamConstant($value)
    {
        return self::isParamConstant($value) ? ($value & ~PARAM_PREFIX) : $value;
    }
}
