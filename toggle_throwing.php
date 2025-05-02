<?php
require './vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
include './config/db_connection.php';

// Decode JWT Token
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
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "❌ Invalid token: " . $e->getMessage()]);
    exit;
}

// Check if any other user has `is_throwing = TRUE`
$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM users WHERE is_throwing = TRUE AND user_id != :user_id");
$stmt->execute(['user_id' => $user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result['count'] > 0) {
    http_response_code(409); // Conflict
    echo json_encode(["error" => "❌ Another user is currently throwing"]);
    exit;
}

// Toggle `is_throwing`
$stmt = $pdo->prepare("UPDATE users SET is_throwing = NOT is_throwing WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);

// Get updated status
$stmt = $pdo->prepare("SELECT is_throwing FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$new_status = $stmt->fetch(PDO::FETCH_ASSOC)['is_throwing'];

http_response_code(200);
echo json_encode(["message" => "✅ Toggled successfully", "is_throwing" => $new_status]);
?>