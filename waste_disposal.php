<?php
include './config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'] ?? null;  // Allows NULL user_id
    $bin_id = $data['bin_id'];
    $type = $data['type'];
    $points_earned = $data['points_earned'] ?? 0;
    $image_path = $data['image_path'] ?? null;

    // Validate required fields
    if (!$bin_id || !$type) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

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
        http_response_code(201);
        echo json_encode(["message" => "✅ Waste disposal recorded successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "❌ Failed to record waste disposal: " . $e->getMessage()]);
    }
}

?>