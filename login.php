<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
require 'db_connection.php';
$inputData = json_decode(file_get_contents('php://input'), true);
$email = $inputData['email'];
$password = $inputData['password'];
if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM user WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    exit;
}
$hashed_password = $user['password'];
if (password_verify($password, $hashed_password)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'You have successfully logged in',
        'user' => [
            'firstName' => $user['firstName'],
            'email' => $user['email']
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
}
