<?php
header("Access-Control-Allow-Origin: *");
include './config/db_connection.php';

header('Content-Type: application/json');

// 🔹 Get token from request headers
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["error" => "❌ Unauthorized: Missing token"]);
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);
$secretKey = 'YOUR_SECRET_KEY';

try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    $collector_id = $decoded->user_id;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "❌ Invalid token: " . $e->getMessage()]);
    exit;
}

// 🔹 Get bin_id from request
$data = json_decode(file_get_contents("php://input"), true);
$bin_id = $data['bin_id'] ?? null;

if (!$bin_id) {
    http_response_code(400);
    echo json_encode(["error" => "❌ Missing bin_id"]);
    exit;
}

try {
    // 🔹 Delete notification for this collector and bin
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE collector_id = :collector_id AND bin_id = :bin_id");
    $stmt->execute(['collector_id' => $collector_id, 'bin_id' => $bin_id]);

    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo json_encode(["message" => "✅ Notification deleted successfully"]);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "❌ No notification found for this bin"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "❌ Database error: " . $e->getMessage()]);
}
?>