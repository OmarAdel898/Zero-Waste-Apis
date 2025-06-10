<?php
include './config/db_connection.php';
header("Access-Control-Allow-Origin: *");

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$collector_id = $data['collector_id'] ?? null;
$bin_id = $data['bin_id'] ?? null;
$message = $data['message'] ?? null;

if (!$collector_id || !$bin_id || !$message) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO notifications (collector_id, bin_id, message) VALUES (:collector_id, :bin_id, :message)");
    $stmt->execute([
        'collector_id' => $collector_id,
        'bin_id' => $bin_id,
        'message' => $message
    ]);

    http_response_code(201);
    echo json_encode(["message" => "✅ Notification sent successfully"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "❌ Failed to send notification: " . $e->getMessage()]);
}
?>