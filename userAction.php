<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connection.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->action) || !isset($data->userID)) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$action = $data->action;
$userID = $data->userID;

if ($action !== "Suspend" && $action !== "Ban" && $action !== "Delete") {
    echo json_encode(["success" => false, "message" => "Invalid action"]);
    exit;
}

if ($action === "Suspend") {
    $query = "UPDATE user SET status = 'suspended' WHERE userID = ?";
} else if ($action === "Ban") {
    $query = "UPDATE user SET status = 'banned' WHERE userID = ?";
} else if ($action === "Delete") {
    $query = "DELETE FROM user WHERE userID = ?";
}

$stmt = $pdo->prepare($query);
$stmt->execute([$userID]);

if ($stmt->rowCount() > 0) {
    echo json_encode(["success" => true, "message" => "$action was successful."]);
} else {
    echo json_encode(["success" => false, "message" => "Action failed."]);
}
?>