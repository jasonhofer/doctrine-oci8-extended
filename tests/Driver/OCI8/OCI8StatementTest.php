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

/**
 * Class OCI8StatementTest
 *
 * @package Doctrine\DBAL\Driver\OCI8Ext\Test
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-23 4:51 PM
 */
class OCI8StatementTest extends AbstractTestCase
{
    public static function setUpBeforeClass()
    {
    }

    /**
     * @expectedException \LogicException
     */
    public function testBindValueThrowsExceptionWhenTypeIsCursor()
    {
        $stmt   = $this->getConnection()->prepare('BEGIN MOCK_PROC(:cursor); END;');
        $cursor = null;

        $stmt->bindValue('cursor', $cursor, 'cursor');
    }

    /**
     * @expectedException \LogicException
     */
    public function testBindValueThrowsExceptionWhenTypeIsOciCursor()
    {
        $stmt   = $this->getConnection()->prepare('BEGIN MOCK_PROC(:cursor); END;');
        $cursor = null;

        $stmt->bindValue('cursor', $cursor, OCI8::PARAM_CURSOR);
    }

    /**
     * @expectedException \LogicException
     */
    public function testBindValueThrowsExceptionWhenTypeIsPdoStmt()
    {
        $stmt   = $this->getConnection()->prepare('BEGIN MOCK_PROC(:cursor); END;');
        $cursor = null;

        $stmt->bindValue('cursor', $cursor, \PDO::PARAM_STMT);
    }

    public function testBindParamSetsOci8Cursor()
    {
        $stmt = $this->getConnection()->prepare('BEGIN MOCK_PROC(:cursor1, :cursor2, :cursor3); END;');

        $stmt->bindParam('cursor1', $cursor1, 'cursor');
        $stmt->bindParam('cursor2', $cursor2, OCI8::PARAM_CURSOR);
        $stmt->bindParam('cursor3', $cursor3, \PDO::PARAM_STMT);

        $this->assertInstanceOf('Doctrine\DBAL\Driver\OCI8Ext\OCI8Cursor', $cursor1);
        $this->assertInstanceOf('Doctrine\DBAL\Driver\OCI8Ext\OCI8Cursor', $cursor2);
        $this->assertInstanceOf('Doctrine\DBAL\Driver\OCI8Ext\OCI8Cursor', $cursor3);
    }
}
