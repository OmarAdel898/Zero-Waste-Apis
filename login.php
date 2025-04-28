<?php
include './config/db_connection.php';
require './vendor/autoload.php'; // For JWT library, e.g., firebase/php-jwt

use Firebase\JWT\JWT;

echo 'login';
$secretKey = "YOUR_SECRET_KEY"; // Use a secure key for token signing

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $email = $data['email'];
    $password = $data['password'];

    // Check for missing fields
    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(["error" => "Missing email or password"]);
        exit;
    }

    // Find the user in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid email or password"]);
        exit;
    }

    // Verify the password
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid email or password"]);
        exit;
    }

    // Generate a JWT token
    $payload = [
        'user_id' => $user['user_id'],
        'role' => $user['role'],
        'name' => $user['name'],
        'iat' => time(), // Issued at
        'exp' => time() + (60 * 60) // Token expires in 1 hour
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    http_response_code(200);
    echo json_encode(["message" => "✅ Login successful", "token" => $jwt]);
}
?>