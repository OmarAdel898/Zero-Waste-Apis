<?php
header("Access-Control-Allow-Origin: *");

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

    $resetLink = "https://zerowaste-cgdtdqhpcuhxceb2.uaenorth-01.azurewebsites.net/reset_form.php?token=$token";
    $mail->Body = $mail->Body = "
<div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px;'>
  <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
    <div style='background-color: #2ecc71; padding: 20px; text-align: center; color: white;'>
      <h2 style='margin: 0;'>Zero Waste Management</h2>
    </div>
    <div style='padding: 30px;'>
      <h3 style='color: #333;'>Reset Your Password</h3>
      <p style='font-size: 16px; color: #555;'>
        Hello, we received a request to reset your password. Click the button below to reset it.
        This link will expire in 1 hour for your security.
      </p>
      <div style='text-align: center; margin: 30px 0;'>
        <a href='$resetLink' style='background-color: #2ecc71; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;'>Reset Password</a>
      </div>
      <p style='font-size: 14px; color: #999;'>If you did not request this, you can ignore this email.</p>
    </div>
    <div style='background-color: #f0f0f0; text-align: center; padding: 15px; font-size: 12px; color: #aaa;'>
      &copy; 2025 Zero Waste Management. All rights reserved.
    </div>
  </div>
</div>";


    $mail->send();
    echo json_encode(["message" => "✅ Password reset link sent successfully", "expires_at" => $expires_at]);
} catch (Exception $e) {
    echo json_encode(["error" => "❌ Email sending failed: " . $mail->ErrorInfo]);
}
?>