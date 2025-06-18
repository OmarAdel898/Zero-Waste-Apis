<?php
header("Access-Control-Allow-Origin: *"); // Change to specific origin like http://localhost:8000 in production
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
require './vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
include './config/db_connection.php';

header('Content-Type: application/json');

// 🔹 Validate Token
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
    $pdo->beginTransaction();

    // 🔹 Record collector action
    $stmt = $pdo->prepare("INSERT INTO collector_actions (collector_id, bin_id, action_type) VALUES (:collector_id, :bin_id, 'empty_bin')");
    $stmt->execute(['collector_id' => $collector_id, 'bin_id' => $bin_id]);

    // 🔹 Delete notifications for this bin
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE bin_id = :bin_id");
    $stmt->execute(['bin_id' => $bin_id]);

    $pdo->commit();

    http_response_code(200);
    echo json_encode(["message" => "✅ Bin task accepted. Notifications deleted & action recorded."]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["error" => "❌ Database error: " . $e->getMessage()]);
}
?>