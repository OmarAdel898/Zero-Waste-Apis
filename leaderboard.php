<?php
include './config/db_connection.php';
header("Access-Control-Allow-Origin: *"); // Allow all origins (for development)
header("Content-Type: application/json");

try {
    // Fetch normal users ranked by points (top 10)
    $stmt = $pdo->prepare("
        SELECT users.user_id, users.name, user_points.points 
        FROM users 
        JOIN user_points ON users.user_id = user_points.user_id 
        WHERE users.role = 'normal_user' 
        ORDER BY user_points.points DESC 
        LIMIT 10
    ");
    
    $stmt->execute();
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["message" => "✅ Leaderboard retrieved!", "leaderboard" => $leaderboard]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "❌ Database error: " . $e->getMessage()]);
}
?>