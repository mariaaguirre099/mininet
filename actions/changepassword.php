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
    exit("You must be logged in to change your password.");
}

//Read input
$currentUsername = $_SESSION['username'];
$currentPassword = $_POST['password']     ?? null;
$newPassword     = $_POST['newPassword'] ?? null;

//Basic validation
if (!$currentPassword || !$newPassword) {
    exit("Missing required fields.");
}

$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;

try {
    //Connect to DB
    $conn = new PDO(
        "mysql:host=$servername;port=$port;dbname=mininet",
        $dbUsername,
        $dbPassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    //Fetch user
    $stmt = $conn->prepare("
        SELECT UserPassword 
        FROM Users 
        WHERE UserUsername = :username
        LIMIT 1
    ");
    $stmt->bindParam(':username', $currentUsername, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //Check current password
    if (!$user || !password_verify($currentPassword, $user['UserPassword'])) {
        exit("Incorrect current password.");
    }

    //Hash and update new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE Users
        SET UserPassword = :newPassword
        WHERE UserUsername = :username
    ");
    $stmt->bindParam(':newPassword', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':username', $currentUsername, PDO::PARAM_STR);
    $stmt->execute();

    //Redirect back to where the user came from
    $redirect = $_SERVER['HTTP_REFERER'] ?? '/settings.php';
    header("Location: $redirect");
    exit();

} catch (PDOException $e) {
    exit("Error: " . $e->getMessage());
}
