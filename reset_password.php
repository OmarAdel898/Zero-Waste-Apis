<?php
include './config/db_connection.php';
header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $token = hash('sha256', $data['token']); // Hash the token to match stored values
    $new_password = $data['password'];

    // Check if token is valid
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_expires > NOW()");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(400);
        echo json_encode(["error" => "❌ Invalid or expired token"]);
        exit;
    }

    // Hash the new password
    $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

    // Update user password & remove token
    $stmt = $pdo->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE reset_token = :token");
    $stmt->execute([
        'password' => $hashedPassword,
        'token' => $token
    ]);

    http_response_code(200);
    echo json_encode(["message" => "✅ Password reset successfully"]);
}
?>