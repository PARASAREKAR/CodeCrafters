<?php
$tests = [
    ['host' => 'localhost', 'port' => 3307, 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'port' => 3307, 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'port' => 3307, 'user' => 'root', 'pass' => 'root'],
    ['host' => 'localhost', 'port' => 3307, 'user' => 'root', 'pass' => 'password'],
    ['host' => 'localhost', 'port' => 3307, 'user' => 'root', 'pass' => '1234'],
];
foreach ($tests as $t) {
    try {
        $dsn = "mysql:host={$t['host']};port={$t['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $t['user'], $t['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
        echo "SUCCESS: host={$t['host']} port={$t['port']} user={$t['user']} pass='{$t['pass']}'\n";
        echo "Databases: " . implode(', ', $dbs) . "\n";
        $hasDb = in_array('event_registration_db', $dbs);
        echo "Has event_registration_db: " . ($hasDb ? "YES" : "NO") . "\n";
        break;
    } catch (Exception $e) {
        echo "FAIL: host={$t['host']} port={$t['port']} user={$t['user']} pass='{$t['pass']}' => " . $e->getMessage() . "\n";
    }
}
