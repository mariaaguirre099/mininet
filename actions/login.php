<?php
session_start();

$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;
$dbname = "mininet";

//Only handle POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Please submit the login form.");
}

//Validate input
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (!$username || !$password) {
    exit("Username and password are required.");
}

try {
    $pdo = new PDO(
        "mysql:host={$servername};port={$port};dbname={$dbname}",
        $dbUsername,
        $dbPassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    //Fetch the user by username
    $stmt = $pdo->prepare("
        SELECT UserID, UserUsername, UserPassword, UserType, UserStatus 
        FROM Users 
        WHERE UserUsername = :username
        LIMIT 1
    ");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //Verify password
    if ($user && password_verify($password, $user['UserPassword'])) {
        $_SESSION['username'] = $user['UserUsername'];
        $_SESSION['id'] = $user['UserID'];
        $_SESSION['type'] = $user['UserType'];
        $_SESSION['status'] = $user['UserStatus'];

        header("Location: /index.php");
        exit();
    }

    //If login fails
    echo "Invalid username or password.";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
