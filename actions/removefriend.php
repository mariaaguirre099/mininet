<?php
session_start();  // MUST be first

// Only handle POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}

// Get and validate input
$senderID = isset($_POST['senderID']) ? intval($_POST['senderID']) : null;
$receiverID = isset($_POST['receiverID']) ? intval($_POST['receiverID']) : null;

if (!$senderID || !$receiverID) {
    die("Missing user ID");
}

// Ensure consistent ordering for friendship record
$user1ID = min($senderID, $receiverID);
$user2ID = max($senderID, $receiverID);

// Database configuration
$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;

try {
    $conn = new PDO(
        "mysql:host=$servername;port=$port;dbname=mininet",
        $dbUsername,
        $dbPassword
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Delete friendship
    $stmt = $conn->prepare("DELETE FROM UserFriends WHERE User1ID = :user1ID AND User2ID = :user2ID");
    $stmt->bindParam(':user1ID', $user1ID, PDO::PARAM_INT);
    $stmt->bindParam(':user2ID', $user2ID, PDO::PARAM_INT);
    $stmt->execute();

    // Close connection
    $conn = null;

    // Redirect back to referring page
    $redirect = $_SERVER['HTTP_REFERER'] ?? '/index.php';
    header("Location: $redirect");
    exit();

} catch (PDOException $e) {
    die("Error removing friend: " . $e->getMessage());
}
?>
