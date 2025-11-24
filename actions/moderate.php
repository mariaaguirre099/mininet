<?php
session_start();  // Must be first

// Ensure user is logged in and is an admin
$isLoggedIn = isset($_SESSION['username']);
$currentUserType = $isLoggedIn ? $_SESSION['type'] : null;
if (!$isLoggedIn || $currentUserType !== 'Admin') {
    header("Location: /index.php");
    exit();
}

// Get POST data
$userID = isset($_POST['userID']) ? intval($_POST['userID']) : null;
$userStatus = $_POST['userStatus'] ?? null;
$action = $_POST['action'] ?? null;

// Validate input
if (!$userID || !$userStatus || !$action) {
    die("Invalid request.");
}

// Determine new status
switch ($action) {
    case 'restrict':
        $newStatus = ($userStatus === 'Active') ? 'Restricted' : 'Active';
        break;
    case 'ban':
        $newStatus = ($userStatus === 'Active') ? 'Banned' : 'Active';
        break;
    default:
        die("Unknown action.");
}

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

    // Update user status
    $stmt = $conn->prepare("UPDATE Users SET UserStatus = :newStatus WHERE UserID = :id");
    $stmt->bindParam(':id', $userID, PDO::PARAM_INT);
    $stmt->bindParam(':newStatus', $newStatus, PDO::PARAM_STR);
    $stmt->execute();

    $conn = null;

    // Redirect back
    $redirect = $_SERVER['HTTP_REFERER'] ?? '/index.php';
    header("Location: $redirect");
    exit();

} catch (PDOException $e) {
    die("Error updating user status: " . $e->getMessage());
}
?>