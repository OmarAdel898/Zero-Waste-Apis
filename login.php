<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include './config/db_connection.php';
require './vendor/autoload.php'; // For JWT

use Firebase\JWT\JWT;

$secretKey = "YOUR_SECRET_KEY"; // Replace with a secure key

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $email = $data['email'];
    $password = $data['password'];

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(["error" => "Missing email or password"]);
        exit;
    }

    // Look up user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid email or password"]);
        exit;
    }

    // Generate JWT
    $payload = [
        'user_id' => $user['user_id'],
        'role' => $user['role'],
        'name' => $user['name'],
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60) 
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    http_response_code(200);
    echo json_encode([
        "message" => "✅ Login successful",
        "token" => $jwt,
        "user_id" => $user['user_id']
    ]);
}
?>