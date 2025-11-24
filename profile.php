<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session info
$isLoggedIn = isset($_SESSION['username']);
$currentUserID = $isLoggedIn ? $_SESSION['id'] : null;
$currentUsername = $isLoggedIn ? $_SESSION['username'] : null;
$currentStatus = $isLoggedIn ? $_SESSION['status'] : null;
$currentUserType = $isLoggedIn ? $_SESSION['type'] : null;

$isActive = ($currentStatus === 'Active');
$isBanned = ($currentStatus === 'Banned');
$isAdmin = ($currentUserType === 'Admin');

// Ensure a username is specified
if (!isset($_GET['user'])) {
    die("No user specified.");
}

$profileUsername = $_GET['user'];
$isUser = ($currentUsername === $profileUsername);

// Database connection
$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=mininet", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch user info
$stmt = $conn->prepare("SELECT UserID, UserType FROM Users WHERE TRIM(UserUsername) = :username LIMIT 1");
$stmt->bindParam(':username', $profileUsername, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

$userID = $user['UserID'];
$userType = $user['UserType'];

// Fetch friends
$friends = [];
$friendIDs = [];

$friendStmt = $conn->prepare("
    SELECT User1ID, User2ID 
    FROM UserFriends 
    WHERE User1ID = :id OR User2ID = :id
");
$friendStmt->bindParam(':id', $userID, PDO::PARAM_INT);
$friendStmt->execute();

foreach ($friendStmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
    $friendIDs[] = ($f['User1ID'] == $userID) ? $f['User2ID'] : $f['User1ID'];
}

if (!empty($friendIDs)) {
    $placeholders = implode(',', array_fill(0, count($friendIDs), '?'));
    $stmt = $conn->prepare("SELECT UserID, UserUsername FROM Users WHERE UserID IN ($placeholders)");
    $stmt->execute($friendIDs);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check friendship with current logged-in user
$isFriend = $isLoggedIn && !$isUser && in_array($currentUserID, $friendIDs);

// Fetch posts
$stmt = $conn->prepare("
    SELECT P.PostID, P.PostContent, P.PostTimestamp
    FROM Posts P
    INNER JOIN UserPosts UP ON P.PostID = UP.PostID
    WHERE UP.UserID = :id
    ORDER BY P.PostTimestamp DESC
    LIMIT 50
");
$stmt->bindParam(':id', $userID, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$conn = null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>mininet | <?php echo htmlspecialchars($profileUsername); ?></title>
    <link rel="stylesheet" href="/css/profile.css">
    <link rel="stylesheet" href="/css/header.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

<div class="profile-layout">

    <!-- SIDEBAR -->
    <aside class="friends-sidebar">
        <h2>Friends</h2>
        <?php if (!empty($friends)): ?>
            <ul class="friends-list">
            <?php foreach ($friends as $friend): ?>
                <li>
                    <a href="/users/<?php echo urlencode($friend['UserUsername']); ?>">
                        <?php echo htmlspecialchars($friend['UserUsername']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="no-friends">No friends yet.</p>
        <?php endif; ?>
    </aside>

    <!-- MAIN POSTS SECTION -->
    <main class="profile-content">

        <!-- Friend Actions -->
        <?php if ($isActive && !$isUser): ?>
            <?php if (!$isFriend): ?>
                <form action="/actions/addfriend.php" method="post" class="add-friend-form">
                    <input type="hidden" name="senderID" value="<?php echo htmlspecialchars($currentUserID); ?>">
                    <input type="hidden" name="receiverID" value="<?php echo htmlspecialchars($userID); ?>">
                    <input type="submit" value="Add Friend">
                </form>
            <?php else: ?>
                <form action="/actions/removefriend.php" method="post" class="remove-friend-form">
                    <input type="hidden" name="senderID" value="<?php echo htmlspecialchars($currentUserID); ?>">
                    <input type="hidden" name="receiverID" value="<?php echo htmlspecialchars($userID); ?>">
                    <input type="submit" value="Remove Friend">
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <!-- POSTS -->
        <div class="posts-container">
            <?php if ($isBanned): ?>
                <p class="no-posts-message">You don't have permission to access this page.</p>
            <?php elseif (empty($posts)): ?>
                <p class="no-posts-message">No posts yet.</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="timestamp"><?php echo htmlspecialchars($post['PostTimestamp']); ?></div>
                        <div class="content"><?php echo htmlspecialchars($post['PostContent']); ?></div>

                        <?php if ($isUser || $isAdmin): ?>
                        <form action="/actions/deletepost.php" method="post" class="delete-post-form">
                            <input type="hidden" name="postID" value="<?php echo $post['PostID']; ?>">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($profileUsername); ?>">
                            <input type="submit" value="Delete Post">
                        </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</div> <!-- end profile-layout -->

</body>
</html>
