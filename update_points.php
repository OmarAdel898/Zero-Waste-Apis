<?php
include './config/db_connection.php';
header("Access-Control-Allow-Origin: *"); // or set a specific origin
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'] ?? null;
    $points = $data['points'] ?? null;

    if (!$user_id || !$points) {
        http_response_code(400);
        echo json_encode(["error" => "Missing user ID or points"]);
        exit;
    }

    try {
        // 🔹 Replace existing points with the new value
        $stmt = $pdo->prepare("UPDATE user_points SET points = :points WHERE user_id = :user_id");
        $stmt->execute([
            'points' => $points,
            'user_id' => $user_id
        ]);

        http_response_code(200);
        echo json_encode(["message" => "✅ User points updated successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "❌ Failed to update points: " . $e->getMessage()]);
    }
}
?>