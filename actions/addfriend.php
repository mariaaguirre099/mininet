<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//Ensure request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Invalid request.");
}

//Redirect if user is not logged in
if (!isset($_SESSION['username'])) {
    exit("You must be logged in to add friends.");
}

//Read input
$senderID   = $_POST['senderID'] ?? null;
$receiverID = $_POST['receiverID'] ?? null;

//Validate input
if (!$senderID || !$receiverID) {
    die("Missing user ID");
}

//Normalize ordering (user1ID is smaller ID) to store friendships consistently
$user1ID = min($senderID, $receiverID);
$user2ID = max($senderID, $receiverID);

//DB connection
$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;

try {
    $conn = new PDO(
        "mysql:host=$servername;port=$port;dbname=mininet",
        $dbUsername,
        $dbPassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    //Insert friendship
    $stmt = $conn->prepare("
    INSERT INTO UserFriends (User1ID, User2ID)
    VALUES (:user1ID, :user2ID)
    ");

    $stmt->bindParam(':user1ID', $user1ID, PDO::PARAM_INT);
    $stmt->bindParam(':user2ID', $user2ID, PDO::PARAM_INT);
    $stmt->execute();

    //Redirect to referring page
    $redirect = $_SERVER['HTTP_REFERER'] ?? '/index.php';
    header("Location: $redirect");
    exit();

} catch (PDOException $e) {
    
    // Handle duplicate entry by checking for integrity constraint violation
    if ($e->getCode() == 23000) {
        exit("You are already friends.");
    }

    exit("Error adding friend: " . $e->getMessage());
}
?>