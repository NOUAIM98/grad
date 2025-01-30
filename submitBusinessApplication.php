<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $businessName = $_POST['businessName'] ?? '';
    $category = $_POST['category'] ?? '';
    $websiteURL = $_POST['websiteURL'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $facebook = $_POST['facebook'] ?? '';
    $instagram = $_POST['instagram'] ?? '';
    $other = $_POST['other'] ?? '';
    $workingHours = json_decode($_POST['workingHours'], true);

    if (empty($businessName) || empty($category) || empty($email)) {
        echo json_encode(["success" => false, "message" => "Required fields are missing."]);
        exit;
    }

    $uploadedFiles = [];
    $targetDirectory = "uploads/";
    if (!file_exists($targetDirectory)) {
        mkdir($targetDirectory, 0777, true);
    }

    if (isset($_FILES['photos'])) {
        foreach ($_FILES['photos']['name'] as $key => $fileName) {
            $targetFile = $targetDirectory . basename($fileName);
            if (move_uploaded_file($_FILES['photos']['tmp_name'][$key], $targetFile)) {
                $uploadedFiles[] = $targetFile;
            }
        }
    }

    $photos = implode(',', $uploadedFiles);

    $emailCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM businessowner WHERE email = ?");
    $emailCheckStmt->execute([$email]);
    $emailExists = $emailCheckStmt->fetchColumn();

    if ($emailExists > 0) {
        echo json_encode(["success" => false, "message" => "Email already exists."]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO businessowner (businessName, category, websiteURL, email, phone, location, description, photos) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$businessName, $category, $websiteURL, $email, $phone, $location, $description, $photos]);

        $ownerID = $pdo->lastInsertId();

        if ($workingHours && is_array($workingHours)) {
            $stmtHours = $pdo->prepare("INSERT INTO workinghours (ownerID, day, time) VALUES (?, ?, ?)");
            foreach ($workingHours as $hour) {
                $day = $hour['day'] ?? '';
                $time = $hour['time'] ?? '';
                $stmtHours->execute([$ownerID, $day, $time]);
            }
        }

        $stmtSocialMedia = $pdo->prepare("INSERT INTO socialmedia (ownerID, facebook, instagram, otherPlatforms) VALUES (?, ?, ?, ?)");
        $stmtSocialMedia->execute([$ownerID, $facebook, $instagram, $other]);

        $pdo->commit();

        echo json_encode(["success" => true, "message" => "Application submitted successfully."]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>