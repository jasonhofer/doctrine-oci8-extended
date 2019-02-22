Doctrine OCI8 Extended
======================

The Doctrine OCI8 driver with cursor support, for PHP 7.1+.

**IMPORTANT: I have not tested this branch yet! Use at your own risk!**

Usage
-----
```php
$config = new Doctrine\DBAL\Configuration();
$params = [
    'dbname'      => 'database_sid',
    'user'        => 'database_username',
    'password'    => 'database_password',
    'host'        => 'database.host',
    'port'        => 1521,
    'persistent'  => true,
    'driverClass' => 'Doctrine\DBAL\Driver\OCI8Ext\Driver',
];
$conn = Doctrine\DBAL\DriverManager::getConnection($params, $config);

$stmt = $conn->prepare('BEGIN MY_STORED_PROCEDURE(:user_id, :cursor); END;');
$stmt->bindValue('user_id', 42);
$stmt->bindParam('cursor', $cursor, \PDO::PARAM_STMT);
$stmt->execute();

/** @var $cursor Doctrine\DBAL\Driver\OCI8Ext\OCI8Cursor */
$cursor->execute();

while ($row = $cursor->fetch()) {
    print_r($row);
    echo PHP_EOL;
}

$cursor->closeCursor();
$stmt->closeCursor();
```

Types
-----
For `OCI8` types that are not represented by `PDO::PARAM_` constants, pass
`OCI8::PARAM_` constants as the `type` argument of `bindValue()` and
`bindParam()`.

Cursors
-------
Cursors can be specified as `PDO::PARAM_STMT`, `OCI8::PARAM_CURSOR`, or just
`'cursor'`. Only the `bindParam()` method can be used to bind a cursor to
a statement.

Sub-Cursors
-----------
Cursor resources returned in a column of a result set are automatically fetched.
You can change this behavior by passing in one of these *fetch mode* flags:

- `OCI8::RETURN_RESOURCES` to return the raw PHP resources.
- `OCI8::RETURN_CURSORS` to return the `OCI8Cursor` objects that have not
   yet been executed.

```php
use Doctrine\DBAL\Driver\OCI8Ext\OCI8;

$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC+OCI8::RETURN_CURSORS);
$rows = $stmt->fetchAll(\PDO::FETCH_BOTH+OCI8::RETURN_RESOURCES);
```
