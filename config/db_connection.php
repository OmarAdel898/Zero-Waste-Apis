<?php
$host = 'zerowaste-server.mysql.database.azure.com';
$db = 'waste_management';
$user = 'cmblzjoncg';
$pass = 'rwQa2hhzFj5$YZu0';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
?>