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

use Doctrine\DBAL\Driver\OCI8Ext\OCI8;
use Doctrine\DBAL\Test\AbstractTestCase;
use Doctrine\DBAL\Driver\OCI8Ext\OCI8Cursor;
use PDO;
use function sprintf;

/**
 * Class OCI8StatementTest
 *
 * @package Doctrine\DBAL\Driver\OCI8Ext\Test
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-23 4:51 PM
 */
class OCI8StatementTest extends AbstractTestCase
{
    public static function setUpBeforeClass() : void
    {
    }

    /**
     * @expectedException \LogicException
     */
    public function testBindValueThrowsExceptionWhenTypeIsCursor() : void
    {
        $stmt   = $this->getConnection()->prepare('BEGIN MOCK_PROC(:cursor); END;');
        $cursor = null;

        $stmt->bindValue('cursor', $cursor, 'cursor');
    }

    /**
     * @expectedException \LogicException
     */
    public function testBindValueThrowsExceptionWhenTypeIsOciCursor() : void
    {
        $stmt   = $this->getConnection()->prepare('BEGIN MOCK_PROC(:cursor); END;');
        $cursor = null;

        $stmt->bindValue('cursor', $cursor, OCI8::PARAM_CURSOR);
    }

    /**
     * @expectedException \LogicException
     */
    public function testBindValueThrowsExceptionWhenTypeIsPdoStmt() : void
    {
        $stmt   = $this->getConnection()->prepare('BEGIN MOCK_PROC(:cursor); END;');
        $cursor = null;

        $stmt->bindValue('cursor', $cursor, PDO::PARAM_STMT);
    }

    public function testBindParamSetsOci8Cursor() : void
    {
        $stmt = $this->getConnection()->prepare('BEGIN MOCK_PROC(:cursor1, :cursor2, :cursor3); END;');

        $stmt->bindParam('cursor1', $cursor1, 'cursor');
        $stmt->bindParam('cursor2', $cursor2, OCI8::PARAM_CURSOR);
        $stmt->bindParam('cursor3', $cursor3, PDO::PARAM_STMT);

        $this->assertInstanceOf(OCI8Cursor::class, $cursor1);
        $this->assertInstanceOf(OCI8Cursor::class, $cursor2);
        $this->assertInstanceOf(OCI8Cursor::class, $cursor3);
    }

    public function testCursor() : void
    {
        $this->oci()->drop('procedure', 'FIRST_NAMES');
        $this->oci()->drop('table', 'employees');

        $expected = [
            ['FIRST_NAME' => 'John'],
            ['FIRST_NAME' => 'Jane'],
            ['FIRST_NAME' => 'George'],
            ['FIRST_NAME' => 'Albert'],
        ];

        $this->oci()->execute('CREATE TABLE employees ( first_name VARCHAR(20) )');

        foreach ($expected as $emp) {
            $this->oci()->execute(sprintf('INSERT INTO employees (first_name) VALUES (\'%s\')', $emp['FIRST_NAME']));
        }
        $this->oci()->execute('
            CREATE OR REPLACE PROCEDURE FIRST_NAMES(my_rc OUT sys_refcursor) AS
            BEGIN
                OPEN my_rc FOR SELECT first_name FROM employees;
            END;
        ');
        $this->oci()->close();

        $conn = $this->getConnection();
        $stmt = $conn->prepare('BEGIN FIRST_NAMES(:cursor); END;');

        /** @var $cursor \Doctrine\DBAL\Driver\OCI8Ext\OCI8Cursor */
        $stmt->bindParam('cursor', $cursor, 'cursor');
        $stmt->execute();
        $cursor->execute();

        $results = $cursor->fetchAll(PDO::FETCH_ASSOC);

        $this->assertSame($expected, $results);

        $this->oci()->drop('procedure', 'FIRST_NAMES');
        $this->oci()->drop('table', 'employees');
    }

    public function testBindArrayByName() : void
    {
        $this->oci()->drop('package', 'ARRAY_BIND_PKG_1');
        $this->oci()->drop('table', 'bind_example');

        $this->oci()->execute('CREATE TABLE bind_example ( name VARCHAR(20) )');
        $this->oci()->execute('
            CREATE OR REPLACE PACKAGE ARRAY_BIND_PKG_1 AS
                TYPE ARR_TYPE IS TABLE OF VARCHAR(20) INDEX BY BINARY_INTEGER;
                PROCEDURE IO_BIND(c1 IN OUT ARR_TYPE);
            END ARRAY_BIND_PKG_1;'
        );
        $this->oci()->execute('
            CREATE OR REPLACE PACKAGE BODY ARRAY_BIND_PKG_1 AS
                CURSOR CUR IS SELECT name FROM bind_example;
                PROCEDURE IO_BIND(c1 IN OUT ARR_TYPE) IS
                    BEGIN
                    -- Bulk Insert
                    FORALL i IN INDICES OF c1
                        INSERT INTO bind_example VALUES (c1(i));
                    -- Fetch and reverse
                    IF NOT CUR%ISOPEN THEN
                        OPEN CUR;
                    END IF;
                    FOR i IN REVERSE 1..5 LOOP
                        FETCH CUR INTO c1(i);
                        IF CUR%NOTFOUND THEN
                            CLOSE CUR;
                            EXIT;
                        END IF;
                    END LOOP;
                END IO_BIND;
            END ARRAY_BIND_PKG_1;'
        );
        $this->oci()->close();

        $conn  = $this->getConnection();
        $stmt  = $conn->prepare('BEGIN ARRAY_BIND_PKG_1.IO_BIND(:c1); END;');
        $array = ['one', 'two', 'three', 'four', 'five'];
        $stmt->bindParam('c1', $array);
        $stmt->execute();

        $this->assertSame(['five', 'four', 'three', 'two', 'one'], $array);

        $this->oci()->drop('package', 'ARRAY_BIND_PKG_1');
        $this->oci()->drop('table', 'bind_example');
    }
}
