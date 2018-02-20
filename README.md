Doctrine OCI8 Extended
======================

The Doctrine OCI8 driver with cursor support.

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

