<?php
include './config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $bin_id = $data['bin_id'];
    $type = $data['type'];
    $points_earned = $data['points_earned'] ?? 0;
    $image_path = $data['image_path'] ?? null;

    if (!$bin_id || !$type) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    // Get user ID where `is_throwing = TRUE`
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE is_throwing = TRUE LIMIT 1");
    $stmt->execute();
    $throwingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    $user_id = $throwingUser ? $throwingUser['user_id'] : null;

    // Insert waste disposal record
    $sql = "INSERT INTO waste_disposal (user_id, bin_id, type, points_earned, image_path) 
            VALUES (:user_id, :bin_id, :type, :points_earned, :image_path)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            'user_id' => $user_id,
            'bin_id' => $bin_id,
            'type' => $type,
            'points_earned' => $points_earned,
            'image_path' => $image_path
        ]);

        // Update user points
        if ($user_id) {
            $stmt = $pdo->prepare("UPDATE user_points SET points = points + :points_earned WHERE user_id = :user_id");
            $stmt->execute([
                'points_earned' => $points_earned,
                'user_id' => $user_id
            ]);
        }

        http_response_code(201);
        echo json_encode(["message" => "✅ Waste disposal recorded successfully", "user_id" => $user_id]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "❌ Failed to record waste disposal: " . $e->getMessage()]);
    }
}
?>