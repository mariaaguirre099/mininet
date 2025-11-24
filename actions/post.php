<?php
session_start();

if (!isset($_SESSION['username'])) {
    die("You must be logged in to post.");
}

$currentUserID = $_SESSION['id'];
$currentStatus = $_SESSION['status'];
$isActive = ($currentStatus === 'Active');

if (!$isActive) {
    die("Your account is not active. Cannot post.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$postContent = trim($_POST['postContent'] ?? '');
if (empty($postContent)) {
    die("Post content cannot be empty.");
}

$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=mininet", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //Insert post into Posts table
    $stmt = $conn->prepare("INSERT INTO Posts (PostContent, PostTimestamp) VALUES (:content, NOW())");
    $stmt->bindParam(':content', $postContent, PDO::PARAM_STR);
    $stmt->execute();
    $postID = $conn->lastInsertId();

    //Link post to user in UserPosts
    $stmt = $conn->prepare("INSERT INTO UserPosts (UserID, PostID) VALUES (:userID, :postID)");
    $stmt->bindParam(':userID', $currentUserID, PDO::PARAM_INT);
    $stmt->bindParam(':postID', $postID, PDO::PARAM_INT);
    $stmt->execute();

    //Redirect back to posts page
    header("Location: /posts.php");
    exit();

} catch (PDOException $e) {
    die("Error creating post: " . $e->getMessage());
}
?>
