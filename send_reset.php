<?php
require './vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
include './config/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? null;

if (!$email) {
    http_response_code(400);
    echo json_encode(["error" => "Missing email"]);
    exit;
}

// Check if user exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(["error" => "Email not found"]);
    exit;
}

// Generate and hash token
$token = bin2hex(random_bytes(32));
$hashedToken = hash('sha256', $token);
$expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Store hashed token
$stmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_expires = :expires WHERE email = :email");
$stmt->execute([
    'token' => $hashedToken,
    'expires' => $expires_at,
    'email' => $email
]);

// Send email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'zerowaste.reset@gmail.com';
    $mail->Password = 'uqeo fcch vmom dont'; // Use App Password from Google
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('zerowaste.reset@gmail.com', 'Support Team');
    $mail->addAddress($email);
    $mail->Subject = 'Password Reset Request';
    $mail->isHTML(true);

    $resetLink = "https://zerowaste-cgdtdqhpcuhxceb2.uaenorth-01.azurewebsites.net/reset_password.php?token=$token";
    $mail->Body = "<p>Click <a href='$resetLink'>here</a> to reset your password. This link expires in 1 hour.</p>";

    $mail->send();
    echo json_encode(["message" => "✅ Password reset link sent successfully", "expires_at" => $expires_at]);
} catch (Exception $e) {
    echo json_encode(["error" => "❌ Email sending failed: " . $mail->ErrorInfo]);
}
?>