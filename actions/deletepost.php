<?php
session_start();

if (!isset($_SESSION['username'])) {
    die("You must be logged in to delete posts.");
}

$currentUserID = $_SESSION['id'];
$currentUserType = $_SESSION['type'];
$isAdmin = ($currentUserType === 'Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$postID = intval($_POST['postID'] ?? 0);
if ($postID <= 0) {
    die("Invalid post ID.");
}

$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=mininet", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verify user can delete post
    $stmt = $conn->prepare("
        SELECT UP.UserID 
        FROM UserPosts UP
        WHERE UP.PostID = :postID
        LIMIT 1
    ");
    $stmt->bindParam(':postID', $postID, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("Post not found.");
    }

    $ownerID = $row['UserID'];
    if (!$isAdmin && $ownerID != $currentUserID) {
        die("You do not have permission to delete this post.");
    }

    // Delete from UserPosts first
    $stmt = $conn->prepare("DELETE FROM UserPosts WHERE PostID = :postID");
    $stmt->bindParam(':postID', $postID, PDO::PARAM_INT);
    $stmt->execute();

    // Delete from Posts table
    $stmt = $conn->prepare("DELETE FROM Posts WHERE PostID = :postID");
    $stmt->bindParam(':postID', $postID, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: /posts.php");
    exit();

} catch (PDOException $e) {
    die("Error deleting post: " . $e->getMessage());
}
?>
