<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'laravel';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $stmt = $pdo->query("DESCRIBE leads");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columns in leads table:\n";
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
