<?php
session_start();

if (!isset($_SESSION['username'])) {
    die("You must be logged in to send messages.");
}

$currentUserID = $_SESSION['id'];
$currentStatus = $_SESSION['status'];
$isActive = ($currentStatus === 'Active');

if (!$isActive) {
    die("Your account is not active. Cannot send messages.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$messageContent = trim($_POST['messageContent'] ?? '');
$receiverID = intval($_POST['receiverID'] ?? 0);

if (empty($messageContent)) {
    die("Message content cannot be empty.");
}
if ($receiverID <= 0 || $receiverID === $currentUserID) {
    die("Invalid receiver.");
}

// Database connection
$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=mininet", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: check if receiver is a friend
    $stmt = $conn->prepare("
        SELECT 1 FROM UserFriends 
        WHERE (User1ID = :user AND User2ID = :friend) 
           OR (User1ID = :friend AND User2ID = :user)
        LIMIT 1
    ");
    $stmt->bindParam(':user', $currentUserID, PDO::PARAM_INT);
    $stmt->bindParam(':friend', $receiverID, PDO::PARAM_INT);
    $stmt->execute();
    if (!$stmt->fetch()) {
        die("You can only send messages to friends.");
    }

    // Insert into Messages table
    $stmt = $conn->prepare("INSERT INTO Messages (MessageContent, MessageTimestamp) VALUES (:content, NOW())");
    $stmt->bindParam(':content', $messageContent, PDO::PARAM_STR);
    $stmt->execute();
    $messageID = $conn->lastInsertId();

    // Link sender and receiver
    $stmt = $conn->prepare("
        INSERT INTO MessageParticipants (UserSenderID, UserReceiverID, MessageID) 
        VALUES (:senderID, :receiverID, :messageID)
    ");
    $stmt->bindParam(':senderID', $currentUserID, PDO::PARAM_INT);
    $stmt->bindParam(':receiverID', $receiverID, PDO::PARAM_INT);
    $stmt->bindParam(':messageID', $messageID, PDO::PARAM_INT);
    $stmt->execute();

    // Redirect back
    $redirect = $_SERVER['HTTP_REFERER'] ?? '/messages.php';
    header("Location: $redirect");
    exit();

} catch (PDOException $e) {
    die("Error sending message: " . $e->getMessage());
}
?>
