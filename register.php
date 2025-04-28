<?php
include '/config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $role = $data['role']; // Values: normal_user, collector, manager

    // Check for missing fields
    if (!$name || !$email || !$password || !$role) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    // Check if the email is already registered
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(["error" => "Email already exists"]);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert the new user into the database
    $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role
        ]);
        http_response_code(201);
        echo json_encode(["message" => "✅ User registered successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "❌ Failed to register user: " . $e->getMessage()]);
    }
}
?>