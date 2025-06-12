<?php
include './config/db_connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['bins']) || count($data['bins']) !== 4) {
    http_response_code(400);
    echo json_encode(["error" => "You must provide exactly 4 bin updates"]);
    exit;
}

try {
    $pdo->beginTransaction();

    foreach ($data['bins'] as $bin) {
        $stmt = $pdo->prepare("UPDATE bins SET fill_level = :fill_level WHERE bin_id = :bin_id");
        $stmt->execute([
            'fill_level' => $bin['fill_level'],
            'bin_id' => $bin['bin_id']
        ]);
    }

    $pdo->commit();
    http_response_code(200);
    echo json_encode(["message" => "✅ Fill levels updated successfully"]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["error" => "❌ Failed to update bins: " . $e->getMessage()]);
}
?>