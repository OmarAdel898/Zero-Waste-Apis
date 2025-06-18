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

// 🔹 Decode JWT Token
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["error" => "❌ Unauthorized: Missing token"]);
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);
$secretKey = 'YOUR_SECRET_KEY'; // Match the key used to generate JWT

try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    $user_id = $decoded->user_id;
    $user_role = $decoded->role;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "❌ Invalid token: " . $e->getMessage()]);
    exit;
}

// 🔹 Only allow users with role "manager"
if ($user_role !== 'manager') {
    http_response_code(403);
    echo json_encode(["error" => "❌ Access denied: Only managers can view waste disposal records"]);
    exit;
}

try {
    // 🔹 Fetch all waste disposal records
    $stmt = $pdo->query("SELECT * FROM waste_disposal ORDER BY timestamp DESC");
    $disposals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["disposals" => $disposals]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "❌ Database error: " . $e->getMessage()]);
}
?>