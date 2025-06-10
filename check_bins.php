<?php
header("Access-Control-Allow-Origin: *");

include './config/db_connection.php';

header('Content-Type: application/json');

try {
    // 🔹 Get bins where fill level <= 10
    $stmt = $pdo->query("SELECT bin_id FROM bins WHERE fill_level <= 10");
    $bins_to_notify = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($bins_to_notify) {
        // 🔹 Get all waste collectors
        $stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'collector'");
        $collectors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($bins_to_notify as $bin) {
            foreach ($collectors as $collector) {
                // 🔹 Send notification for each collector
                $stmt = $pdo->prepare("INSERT INTO notifications (collector_id, bin_id, message) VALUES (:collector_id, :bin_id, :message)");
                $stmt->execute([
                    'collector_id' => $collector['user_id'],
                    'bin_id' => $bin['bin_id'],
                    'message' => "🚨 Bin #{$bin['bin_id']} needs emptying! Fill level is below 10."
                ]);
            }
        }

        http_response_code(201);
        echo json_encode(["message" => "✅ Notifications sent to all collectors"]);
    } else {
        http_response_code(200);
        echo json_encode(["message" => "✅ No bins need emptying"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "❌ Database error: " . $e->getMessage()]);
}
?>