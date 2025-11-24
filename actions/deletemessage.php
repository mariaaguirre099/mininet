<?php
session_start();

if (!isset($_SESSION['username'])) {
    die("You must be logged in to delete messages.");
}

$currentUserID = $_SESSION['id'];
$currentUserType = $_SESSION['type'];
$isAdmin = ($currentUserType === 'Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$messageID = intval($_POST['messageID'] ?? 0);
if ($messageID <= 0) {
    die("Invalid message ID.");
}

// Database connection
$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=mininet", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verify user can delete message
    $stmt = $conn->prepare("
        SELECT UserSenderID 
        FROM MessageParticipants
        WHERE MessageID = :messageID
        LIMIT 1
    ");
    $stmt->bindParam(':messageID', $messageID, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("Message not found.");
    }

    $senderID = $row['UserSenderID'];
    if (!$isAdmin && $senderID != $currentUserID) {
        die("You do not have permission to delete this message.");
    }

    // Delete from MessageParticipants first
    $stmt = $conn->prepare("DELETE FROM MessageParticipants WHERE MessageID = :messageID");
    $stmt->bindParam(':messageID', $messageID, PDO::PARAM_INT);
    $stmt->execute();

    // Delete from Messages table
    $stmt = $conn->prepare("DELETE FROM Messages WHERE MessageID = :messageID");
    $stmt->bindParam(':messageID', $messageID, PDO::PARAM_INT);
    $stmt->execute();

    $redirect = $_SERVER['HTTP_REFERER'] ?? '/messages.php';
    header("Location: $redirect");
    exit();

} catch (PDOException $e) {
    die("Error deleting message: " . $e->getMessage());
}
?>
