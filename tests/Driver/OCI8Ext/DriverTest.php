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
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Driver\OCI8Ext\OCI8Connection;

/**
 * Class DriverTest
 *
 * @package Doctrine\DBAL\Driver\OCI8Ext\Test
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-23 3:01 PM
 */
class DriverTest extends AbstractTestCase
{
    public function testDriverRegistersCursorType() : void
    {
        $this->getConnection();

        $this->assertTrue(Type::hasType('cursor'));
    }

    public function testDriverManagerReturnsWrappedOci8ExtConnection() : void
    {
        $this->assertInstanceOf(
            OCI8Connection::class,
            $this->getConnection()->getWrappedConnection()
        );
    }
}
