<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require 'db_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$inputData = json_decode(file_get_contents('php://input'), true);

if (
    !isset($inputData['first_name'], $inputData['last_name'], $inputData['email'], $inputData['password']) ||
    empty($inputData['first_name']) || empty($inputData['last_name']) || empty($inputData['email']) || empty($inputData['password'])
) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit();
}

$first_name = trim($inputData['first_name']);
$last_name = trim($inputData['last_name']);
$email = trim($inputData['email']);
$password = trim($inputData['password']);

$hashed_password = password_hash($password, PASSWORD_BCRYPT);

try {
    $query = "SELECT * FROM user WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
        exit();
    }

    $query = "INSERT INTO user (firstName, lastName, email, password) VALUES (:first_name, :last_name, :email, :password)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error occurred while registering user']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
}
?>