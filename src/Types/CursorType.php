<?php

/*
 * This file is part of the doctrine-oci8-extended package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use LogicException;
use PDO;

/**
 * Class CursorType
 *
 * @package Doctrine\DBAL\Types
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-24 9:15 AM
 */
class CursorType extends Type
{
    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array                                     $fieldDeclaration The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform         The currently used database platform.
     *
     * @throws LogicException
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        throw new LogicException('Doctrine does not support SQL declarations for cursors.');
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName() : string
    {
        return 'cursor';
    }

    /**
     * @return int
     */
    public function getBindingType() : int
    {
        return PDO::PARAM_STMT;
    }
}
