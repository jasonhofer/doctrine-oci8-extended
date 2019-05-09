<?php

/*
 * This file is part of the doctrine-oci8-extended package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\DBAL\Test\Driver\OCI8Ext;

use Doctrine\DBAL\Driver\OCI8Ext\OCI8;
use Doctrine\DBAL\Test\AbstractTestCase;
use PDO;

/**
 * Class OCI8Test
 *
 * @package Doctrine\DBAL\Driver\OCI8Ext\Test
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-24 1:02 AM
 */
class OCI8Test extends AbstractTestCase
{
    public function testIsParamConstant() : void
    {
        $this->assertTrue(OCI8::isParamConstant(OCI8::PARAM_CURSOR));

        $this->assertFalse(OCI8::isParamConstant(0));
        $this->assertFalse(OCI8::isParamConstant(1));
        $this->assertFalse(OCI8::isParamConstant(PDO::PARAM_STMT));
        $this->assertFalse(OCI8::isParamConstant(PHP_INT_MAX));
    }

    public function testDecodeParamConstant() : void
    {
        $this->assertSame(OCI_B_CURSOR, OCI8::decodeParamConstant(OCI8::PARAM_CURSOR));
    }

    public function testDecodeParamConstantReturnsGivenValueIfNotParamConstant() : void
    {
        $this->assertSame(0, OCI8::decodeParamConstant(0));
        $this->assertSame(1, OCI8::decodeParamConstant(1));
        $this->assertSame(PDO::PARAM_STMT, OCI8::decodeParamConstant(PDO::PARAM_STMT));
        $this->assertSame(PHP_INT_MAX, OCI8::decodeParamConstant(PHP_INT_MAX));
    }
}
