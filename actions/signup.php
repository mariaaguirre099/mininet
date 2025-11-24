<?php
session_start();

//Only handle POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}

//Get and sanitize inputs
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$dob = $_POST['dob'] ?? '';

//Basic validation
if (empty($username) || empty($password) || empty($dob)) {
    die("All fields are required.");
}

if ($password !== $confirmPassword) {
    die("Passwords do not match.");
}

//Database configuration
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

    //Check if username already exists
    $stmt = $conn->prepare("SELECT UserUsername FROM Users WHERE UserUsername = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        die("Username unavailable.");
    }

    //Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    //Insert new user
    $stmt = $conn->prepare("
        INSERT INTO Users (UserUsername, UserPassword, UserType, UserDateOfBirth)
        VALUES (:username, :password, 'Regular', :dob)
    ");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':dob', $dob, PDO::PARAM_STR);
    $stmt->execute();

    //Set session
    $_SESSION['username'] = $username;
    $_SESSION['id'] = $conn->lastInsertId();
    $_SESSION['type'] = 'Regular';
    $_SESSION['status'] = 'Active';

    //Redirect to home
    header("Location:/index.php");
    exit();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
