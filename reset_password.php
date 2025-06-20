<?php
// CORS and Content-Type
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include './config/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

// Extract values
$token = $data['token'] ?? '';
$new_password = $data['new_password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';

// Validate required fields
if (!$token || !$new_password || !$confirm_password) {
    http_response_code(400);
    echo json_encode(["error" => "❌ All fields are required"]);
    exit;
}

// Check password match
if ($new_password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(["error" => "❌ Passwords do not match"]);
    exit;
}

// Hash the token (to match what’s stored in DB)
$hashedToken = hash('sha256', $token);

// Verify token exists and is not expired
$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_expires > NOW()");
$stmt->execute(['token' => $hashedToken]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(400);
    echo json_encode(["error" => "❌ Invalid or expired token"]);
    exit;
}

// Hash and update password
$hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

$update = $pdo->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE reset_token = :token");
$update->execute([
    'password' => $hashedPassword,
    'token' => $hashedToken
]);

http_response_code(200);
echo json_encode(["message" => "✅ Password has been reset successfully"]);
?>