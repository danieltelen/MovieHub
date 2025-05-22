<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "your_db_user", "your_db_password", "your_db_name");

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to connect to database"]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';

if ($action === 'register') {
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (!$username || !$email || !$password) {
        http_response_code(400);
        echo json_encode(["error" => "All fields are required"]);
        exit();
    }

    // Check if username or email exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(["error" => "Username or email already exists"]);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password_hash);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Registration successful"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to register user"]);
    }

    $stmt->close();

} elseif ($action === 'login') {
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if (!$username || !$password) {
        http_response_code(400);
        echo json_encode(["error" => "Username and password are required"]);
        exit();
    }

    $stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid username or password"]);
        $stmt->close();
        exit();
    }

    $stmt->bind_result($user_id, $password_hash);
    $stmt->fetch();

    if (password_verify($password, $password_hash)) {
        // Generate a simple token (replace with JWT in production)
        $token = bin2hex(random_bytes(16));
        echo json_encode(["token" => $token]);
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Invalid username or password"]);
    }

    $stmt->close();

} else {
    http_response_code(400);
    echo json_encode(["error" => "Invalid action"]);
}

$mysqli->close();
?>
