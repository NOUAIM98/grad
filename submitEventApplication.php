<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $eventName = $_POST['eventName'] ?? '';
    $category = $_POST['category'] ?? '';
    $websiteURL = $_POST['websiteURL'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $description = $_POST['description'] ?? '';
    $eventDate = $_POST['eventDate'] ?? '';
    $eventTime = $_POST['eventTime'] ?? '';
    $ticketType = $_POST['ticketType'] ?? '';
    $ticketPrice = $_POST['ticketPrice'] ?? '';
    $totalTickets = $_POST['totalTickets'] ?? '';
    $facebook = $_POST['facebook'] ?? '';
    $instagram = $_POST['instagram'] ?? '';
    $otherPlatforms = $_POST['otherPlatforms'] ?? '';


    if (empty($eventName) || empty($category) || empty($email)) {
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


    try {

        $pdo->beginTransaction();

        $stmtOrganizer = $pdo->prepare("INSERT INTO eventorganizer (name, email, phone) VALUES (?, ?, ?)");
        $stmtOrganizer->execute([$eventName, $email, $phone]);

        $organizerID = $pdo->lastInsertId();

        $stmtEvent = $pdo->prepare("INSERT INTO event (eventName, category, websiteURL, email, phone, address, eventDescription, eventDate, eventTime, ticketType, ticketPrice, totalTickets, photos, facebook, instagram, otherPlatforms, organizerID) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmtEvent->execute([
            $eventName,
            $category,
            $websiteURL,
            $email,
            $phone,
            $address,
            $description,
            $eventDate,
            $eventTime,
            $ticketType,
            $ticketPrice,
            $totalTickets,
            $photos,
            $facebook,
            $instagram,
            $otherPlatforms,
            $organizerID
        ]);

        $pdo->commit();

        echo json_encode(["success" => true, "message" => "Event application submitted successfully."]);

    } catch (Exception $e) {

        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>