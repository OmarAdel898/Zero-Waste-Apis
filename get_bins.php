<?php
include './config/db_connection.php';

header('Content-Type: application/json');

try {
    // 🔹 Fetch all bins from the database
    $stmt = $pdo->query("SELECT * FROM bins");
    $bins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($bins) {
        echo json_encode(["bins" => $bins]);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "No bins found"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>