<?php
include './config/db_connection.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");


$collector_id = $_GET['collector_id'] ?? null;

if (!$collector_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing collector ID"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM collector_actions WHERE collector_id = :collector_id ORDER BY timestamp DESC");
    $stmt->execute(['collector_id' => $collector_id]);
    $actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["actions" => $actions]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>