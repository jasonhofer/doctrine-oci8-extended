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
use Doctrine\DBAL\Driver\OCI8Ext\OCI8Cursor;
use Doctrine\DBAL\Test\AbstractTestCase;
use Doctrine\DBAL\Test\OciWrapper;
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
    protected static $employees = [
        ['FIRST_NAME' => 'John'],
        ['FIRST_NAME' => 'Jane'],
        ['FIRST_NAME' => 'George'],
        ['FIRST_NAME' => 'Albert'],
    ];

    public static function setUpBeforeClass() : void
    {
        $oci = new OciWrapper();
        self::setupEmployeesTable($oci);
        self::setupArrayBindPackage($oci);
        $oci->close();
    }

    public static function tearDownAfterClass() : void
    {
        $oci = new OciWrapper();
        self::tearDownEmployeesTable($oci);
        self::tearDownArrayBindPackage($oci);
        $oci->close();
    }

    /**
     * @expectedException        \LogicException
     * @expectedExceptionMessage You must call "bindParam()" to bind a cursor.
     */
    public function testBindValueThrowsExceptionWhenTypeIsCursor() : void
    {
        $stmt   = $this->getConnection()->prepare('BEGIN MOCK_PROC(:cursor); END;');
        $cursor = null;

        $stmt->bindValue('cursor', $cursor, 'cursor');
    }

    /**
     * @expectedException        \LogicException
     * @expectedExceptionMessage You must call "bindParam()" to bind a cursor.
     */
    public function testBindValueThrowsExceptionWhenTypeIsOciCursor() : void
    {
        $stmt   = $this->getConnection()->prepare('BEGIN MOCK_PROC(:cursor); END;');
        $cursor = null;

        $stmt->bindValue('cursor', $cursor, OCI8::PARAM_CURSOR);
    }

    /**
     * @expectedException        \LogicException
     * @expectedExceptionMessage You must call "bindParam()" to bind a cursor.
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

    public function testCursorFetchAll() : void
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare('BEGIN FIRST_NAMES(:cursor); END;');

        /** @var $cursor OCI8Cursor */
        $stmt->bindParam('cursor', $cursor, 'cursor');
        $stmt->execute();
        $cursor->execute();

        $results = $cursor->fetchAll(PDO::FETCH_ASSOC);

        $this->assertSame(self::$employees, $results);
    }

    public function testCursorFetchColumn() : void
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare('BEGIN FIRST_NAMES(:cursor); END;');

        /** @var $cursor OCI8Cursor */
        $stmt->bindParam('cursor', $cursor, 'cursor');
        $stmt->execute();
        $cursor->execute();

        $results = [];
        while (false !== ($columnValue = $cursor->fetchColumn())) {
            $results[] = $columnValue;
        }

        $this->assertSame(array_column(self::$employees, 'FIRST_NAME'), $results);
    }

    public function testBindArrayByName() : void
    {
        $conn  = $this->getConnection();
        $stmt  = $conn->prepare('BEGIN ARRAY_BIND_PKG_1.IO_BIND(:c1); END;');
        $array = ['one', 'two', 'three', 'four', 'five'];
        $stmt->bindParam('c1', $array);
        $stmt->execute();

        $this->assertSame(['five', 'four', 'three', 'two', 'one'], $array);
    }

    //
    // Setup and tear-down methods.
    //

    protected static function setupEmployeesTable(OciWrapper $oci) : void
    {
        self::tearDownEmployeesTable($oci);

        $oci->execute('CREATE TABLE employees ( first_name VARCHAR(20) )');

        foreach (self::$employees as $emp) {
            $oci->execute(sprintf('INSERT INTO employees (first_name) VALUES (\'%s\')', $emp['FIRST_NAME']));
        }
        $oci->execute('
            CREATE OR REPLACE PROCEDURE FIRST_NAMES(my_rc OUT sys_refcursor) AS
            BEGIN
                OPEN my_rc FOR SELECT first_name FROM employees;
            END;
        ');
    }

    protected static function tearDownEmployeesTable(OciWrapper $oci) : void
    {
        $oci->drop('procedure', 'FIRST_NAMES');
        $oci->drop('table', 'employees');
    }

    protected static function setupArrayBindPackage(OciWrapper $oci) : void
    {
        self::tearDownArrayBindPackage($oci);

        $oci->execute('CREATE TABLE bind_example ( name VARCHAR(20) )');
        $oci->execute('
            CREATE OR REPLACE PACKAGE ARRAY_BIND_PKG_1 AS
                TYPE ARR_TYPE IS TABLE OF VARCHAR(20) INDEX BY BINARY_INTEGER;
                PROCEDURE IO_BIND(c1 IN OUT ARR_TYPE);
            END ARRAY_BIND_PKG_1;'
        );
        $oci->execute('
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
    }

    protected static function tearDownArrayBindPackage(OciWrapper $oci) : void
    {
        $oci->drop('package', 'ARRAY_BIND_PKG_1');
        $oci->drop('table', 'bind_example');
    }
}
