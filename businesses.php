<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_connection.php';

try {
    $stmt = $pdo->prepare("SELECT b.ownerID, b.businessName, b.category, b.location, b.websiteURL, b.email, b.phone, b.description, b.photos, b.created_at, 
                                  s.facebook, s.instagram, s.otherPlatforms, 
                                  w.day, w.time
                           FROM businessowner b
                           LEFT JOIN socialmedia s ON b.ownerID = s.ownerID
                           LEFT JOIN workinghours w ON b.ownerID = w.ownerID");
    $stmt->execute();
    $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $organizedBusinesses = [];
    foreach ($businesses as $business) {
        $ownerID = $business['ownerID'];
        if (!isset($organizedBusinesses[$ownerID])) {
            $organizedBusinesses[$ownerID] = [
                'ownerID' => $ownerID,
                'businessName' => $business['businessName'],
                'category' => $business['category'],
                'location' => $business['location'],
                'websiteURL' => $business['websiteURL'],
                'email' => $business['email'],
                'phone' => $business['phone'],
                'description' => $business['description'],
                'photos' => $business['photos'],
                'created_at' => $business['created_at'],
                'facebook' => $business['facebook'],
                'instagram' => $business['instagram'],
                'otherPlatforms' => $business['otherPlatforms'],
                'workingHours' => []
            ];
        }
        

        if ($business['day'] && $business['time']) {
            $organizedBusinesses[$ownerID]['workingHours'][] = [
                'day' => $business['day'],
                'time' => $business['time']
            ];
        }
    }

    echo json_encode(["success" => true, "data" => array_values($organizedBusinesses)]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>