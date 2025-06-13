<?php
header("Access-Control-Allow-Origin: *"); // or set a specific origin
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


require './vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
include './config/db_connection.php';

// Get Authorization Header
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
    // Decode the JWT token
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    $user_id = $decoded->user_id;

    // Fetch user points
    $stmt = $pdo->prepare("SELECT points FROM user_points WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404);
        echo json_encode(["error" => "❌ User points not found"]);
        exit;
    }

    http_response_code(200);
    echo json_encode(["message" => "✅ Points retrieved successfully", "user_id" => $user_id, "points" => $result['points']]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "❌ Invalid token: " . $e->getMessage()]);
    exit;
}
?>