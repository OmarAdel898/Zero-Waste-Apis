<?php
header("Content-Type: application/json");

$request = $_SERVER['REQUEST_URI'];
switch ($request) {
    case '/register':
        include '../routes/register.php';
        break;
    case '/login':
        include '../routes/login.php';
        break;
    case '/disposal':
        include '../routes/disposal.php';
        break;
    // Add other routes here...
    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
        break;
}
?>