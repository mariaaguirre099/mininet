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
    exit("You must be logged in to change your username.");
}

//Read input
$currentUsername = $_SESSION['username'];
$newUsername     = trim($_POST['newUsername'] ?? '');
$password        = trim($_POST['password'] ?? '');

//Validate input
if ($newUsername === '' || $password === '') {
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

  // Fetch current user info
  $stmt = $conn->prepare("SELECT UserPassword FROM Users WHERE UserUsername = :u LIMIT 1");
  $stmt->execute([':u' => $currentUsername]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  //Verify user exists
  if (!$user) {
    exit("User not found.");
  }

  // Verify password
  if (!password_verify($password, $user['UserPassword'])) {
      exit("Incorrect password.");
  }

  // Check if new username already exists
  $checkStmt = $conn->prepare("SELECT UserID FROM Users WHERE UserUsername = :new LIMIT 1");
  $checkStmt->execute([':new' => $newUsername]);
  if ($checkStmt->fetch()) {
    exit("That username is already taken.");
  }

  // Update username
  $updateStmt = $conn->prepare("
      UPDATE Users 
      SET UserUsername = :new 
      WHERE UserUsername = :old
  ");
  $updateStmt->execute([
    ':new' => $newUsername,
    ':old' => $currentUsername
  ]);

  $_SESSION['username'] = $newUsername;

  $redirect = $_SERVER['HTTP_REFERER'] ?? '/settings.php';
  header("Location: $redirect");
  exit();
} catch (PDOException $e) {
    error_log("changeusername error: " . $e->getMessage());
    exit("Database error.");
}
?>