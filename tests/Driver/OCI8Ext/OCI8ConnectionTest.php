<?php

/*
 * This file is part of the doctrine-oci8-extended package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* @noinspection PhpUnhandledExceptionInspection */

namespace Doctrine\DBAL\Test\Driver\OCI8Ext;

use Doctrine\DBAL\Test\AbstractTestCase;
use Doctrine\DBAL\Driver\OCI8Ext\OCI8Statement;

/**
 * Class OCI8ConnectionTest
 *
 * @package Doctrine\DBAL\Driver\OCI8Ext\Test
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-23 4:24 PM
 */
class OCI8ConnectionTest extends AbstractTestCase
{
    public function testPrepareReturnsWrappedOci8ExtStatement() : void
    {
        $stmt = $this->getConnection()->prepare('SELECT * FROM SYS.DUAL');

        $driverStmt = $this->getPropertyValue($stmt, 'stmt');

        $this->assertInstanceOf(OCI8Statement::class, $driverStmt);
    }
}
