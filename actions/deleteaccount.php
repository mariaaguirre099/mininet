<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Must be logged in
if (!isset($_SESSION['username'])) {
    exit("You must be logged in to delete your account.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Invalid request.");
}

$username         = $_SESSION['username'];
$password         = trim($_POST['password'] ?? '');
$confirmPassword  = trim($_POST['confirmPassword'] ?? '');

if ($password === '' || $confirmPassword === '') {
    exit("Missing required fields.");
}

if ($password !== $confirmPassword) {
    exit("Passwords do not match.");
}

$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;

try {
    //DB connection
    $conn = new PDO(
        "mysql:host=$servername;port=$port;dbname=mininet",
        $dbUsername,
        $dbPassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    //Get user data
    $stmt = $conn->prepare("
        SELECT UserPassword, UserType 
        FROM Users 
        WHERE UserUsername = :u
        LIMIT 1
    ");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        exit("User not found.");
    }

    //Verify password
    if (!password_verify($password, $user['UserPassword'])) {
        exit("Incorrect password.");
    }

    //Delete user
    $deleteStmt = $conn->prepare("
        DELETE FROM Users
        WHERE UserUsername = :u
        LIMIT 1
    ");
    $deleteStmt->execute([':u' => $username]);

    //Clear session
    $_SESSION = [];
    session_unset();
    session_destroy();

    //Redirect to homepage
    header("Location: /index.php");
    exit();

} catch (PDOException $e) {
    error_log("deleteaccount error: " . $e->getMessage());
    exit("Database error.");
}
