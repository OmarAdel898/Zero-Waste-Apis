<?php
include './config/db_connection.php';
header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $bin_id = $data['bin_id'];
    $type = $data['type'];
    $fill_level = $data['fill_level'] ?? 0;
    $image_path = $data['image_path'] ?? null;
    $timestamp = date('Y-m-d H:i:s');

    if (!$bin_id || !$type) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    // 🔹 Assign points based on waste type
    $points_mapping = [
        "plastic" => 5,
        "paper" => 4,
        "glass" => 3,
        "metal" => 7
    ];
    $points_earned = $points_mapping[$type] ?? 0;  // Default to 0 if type not recognized

    try {
        // 🔹 Function 1: Check and Update/Add Bin
        $stmt = $pdo->prepare("SELECT bin_id FROM bins WHERE bin_id = :bin_id LIMIT 1");
        $stmt->execute(['bin_id' => $bin_id]);
        $existingBin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingBin) {
            // Update existing bin's fill level & timestamp
            $stmt = $pdo->prepare("UPDATE bins SET fill_level = :fill_level, last_updated = :timestamp WHERE bin_id = :bin_id");
            $stmt->execute([
                'fill_level' => $fill_level,
                'timestamp' => $timestamp,
                'bin_id' => $bin_id
            ]);
        } else {
            // Insert new bin with default location ("Cairo")
            $stmt = $pdo->prepare("INSERT INTO bins (bin_id, type, location, fill_level, last_updated) VALUES (:bin_id, :type, 'Cairo', :fill_level, :timestamp)");
            $stmt->execute([
                'bin_id' => $bin_id,
                'type' => $type,
                'fill_level' => $fill_level,
                'timestamp' => $timestamp
            ]);
        }

        // 🔹 Function 2: Waste Disposal Recording
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE is_throwing = TRUE LIMIT 1");
        $stmt->execute();
        $throwingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $throwingUser ? $throwingUser['user_id'] : null;

        $sql = "INSERT INTO waste_disposal (user_id, bin_id, type, points_earned, image_path, timestamp) 
                VALUES (:user_id, :bin_id, :type, :points_earned, :image_path, :timestamp)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'bin_id' => $bin_id,
            'type' => $type,
            'points_earned' => $points_earned,
            'image_path' => $image_path,
            'timestamp' => $timestamp
        ]);

        // 🔹 Update user points
        if ($user_id) {
            $stmt = $pdo->prepare("UPDATE user_points SET points = points + :points_earned WHERE user_id = :user_id");
            $stmt->execute([
                'points_earned' => $points_earned,
                'user_id' => $user_id
            ]);
        }

        http_response_code(201);
        echo json_encode(["message" => "✅ Waste disposal recorded successfully and bin updated", "user_id" => $user_id]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "❌ Failed to process request: " . $e->getMessage()]);
    }
}
?>