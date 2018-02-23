<?php

/*
 * This file is part of the doctrine-oci8-extended package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$autoloader = require __DIR__.'/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__.'/..');
$dotenv->load();
