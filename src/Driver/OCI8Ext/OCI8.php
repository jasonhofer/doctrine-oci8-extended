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
    public const RETURN_RESOURCES = 0x0100;
    public const RETURN_CURSORS   = 0x0200;

    // OCI8::PARAM_* constants are prefixed with binary 1010 (0xA) in the -4th nibble.
    public const PARAM_CHR     = 0xA001;
    public const PARAM_NUM     = 0xA002;
    public const PARAM_INT     = 0xA003;
    public const PARAM_FLT     = 0xA004;
    public const PARAM_STR     = 0xA005;
    public const PARAM_LNG     = 0xA008;
    public const PARAM_VCS     = 0xA009;
    public const PARAM_BFLOAT  = 0xA015;
    public const PARAM_BDOUBLE = 0xA016;
    public const PARAM_BIN     = 0xA017;
    public const PARAM_LBI     = 0xA018;
    public const PARAM_UIN     = 0xA044;
    public const PARAM_LVC     = 0xA05E;
    public const PARAM_AFC     = 0xA060;
    public const PARAM_AVC     = 0xA061;
    public const PARAM_ROWID   = 0xA068;
    public const PARAM_NTY     = 0xA06C;
    public const PARAM_CLOB    = 0xA070;
    public const PARAM_BLOB    = 0xA071;
    public const PARAM_BFILEE  = 0xA072;
    public const PARAM_CFILEE  = 0xA073;
    public const PARAM_CURSOR  = 0xA074;
    public const PARAM_ODT     = 0xA09C;
    public const PARAM_BOOL    = 0xA0FC;

    /**
     * @param int $value
     *
     * @return bool
     */
    public static function isParamConstant($value) : bool
    {
        return $value >= PARAM_PREFIX && $value <= PARAM_MAX;
    }

    /**
     * @param int $value
     *
     * @return int
     */
    public static function decodeParamConstant($value) : int
    {
        return self::isParamConstant($value) ? ($value & ~PARAM_PREFIX) : $value;
    }
}
