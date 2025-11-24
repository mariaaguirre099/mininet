<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
$isLoggedIn = isset($_SESSION['username']);
if (!$isLoggedIn) {
    die("Please log in to view settings.");
}

// Session info
$currentUserID = $_SESSION['id'];
$currentUsername = $_SESSION['username'];
$currentUserType = $_SESSION['type'];
$currentStatus = $_SESSION['status'];

$isAdmin = ($currentUserType === 'Admin');
$isActive = ($currentStatus === 'Active');
$isRestricted = ($currentStatus === 'Restricted');
$isBanned = ($currentStatus === 'Banned');

// Database connection
$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=mininet", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>mininet | settings</title>
    <link rel="stylesheet" href="/css/settings.css">
    <link rel="stylesheet" href="/css/header.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

<div class="forms-container">

    <!-- Empty left space -->
    <div class="loginForm"></div>

    <!-- Change Username -->
    <div class="loginForm">
        <form action="/actions/changeusername.php" method="post">
            <input type="text" name="newUsername" placeholder="New username" required>
            <input type="password" name="password" placeholder="Current password" required>
            <input type="submit" class="login-button" value="Change Username">
        </form>
    </div>

    <!-- Delete Account -->
    <div class="loginForm">
        <form action="/actions/deleteaccount.php" method="post" class="delete-account-form">
            <input type="password" name="password" placeholder="Current password" required>
            <input type="password" name="confirmPassword" placeholder="Confirm password" required>
            <input type="submit" value="Delete Account">
        </form>
    </div>

    <!-- Change Password -->
    <div class="loginForm">
        <form action="/actions/changepassword.php" method="post">
            <input type="password" name="password" placeholder="Current password" required>
            <input type="password" name="newPassword" placeholder="New password" required>
            <input type="submit" class="login-button" value="Change Password">
        </form>
    </div>

    <!-- Empty right space -->
    <div class="loginForm"></div>

</div> <!-- end forms-container -->

</body>
</html>
