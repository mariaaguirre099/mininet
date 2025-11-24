<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Logged-in user info
$isLoggedIn = isset($_SESSION['username']);
$currentUserID = $isLoggedIn ? $_SESSION['id'] : null;
$currentUsername = $isLoggedIn ? $_SESSION['username'] : null;
$currentUserType = $isLoggedIn ? $_SESSION['type'] : null;
$currentStatus = $isLoggedIn ? $_SESSION['status'] : null;
$isAdmin = ($currentUserType === 'Admin');
$isActive = ($currentStatus === 'Active');
$isBanned = ($currentStatus === 'Banned');
$isRestricted = ($currentStatus === 'Restricted');

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

// Fetch recent posts
try {
    $stmt = $conn->prepare("
        SELECT U.UserID, U.UserUsername, P.PostID, P.PostContent, P.PostTimestamp
        FROM Posts P
        INNER JOIN UserPosts UP ON P.PostID = UP.PostID
        INNER JOIN Users U ON UP.UserID = U.UserID
        ORDER BY P.PostTimestamp DESC
        LIMIT 50
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch friends for visibility
    $friends = [];
    if ($isLoggedIn) {
        $friendStmt = $conn->prepare("
            SELECT User1ID, User2ID 
            FROM UserFriends 
            WHERE User1ID = :id OR User2ID = :id
        ");
        $friendStmt->bindParam(':id', $currentUserID, PDO::PARAM_INT);
        $friendStmt->execute();

        foreach ($friendStmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $friends[] = ($f['User1ID'] == $currentUserID) ? $f['User2ID'] : $f['User1ID'];
        }
    }
} catch(PDOException $e) {
    die("Error fetching posts: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>mininet | posts</title>
    <link rel="stylesheet" href="/css/posts.css">
    <link rel="stylesheet" href="/css/header.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

<div class="forms-container">
    <!-- Login / Logout -->
    <div class="loginForm">
        <?php if ($isLoggedIn): ?>
            <?php if ($isActive): ?>
                <form action="/actions/logout.php" method="post">
                    <input type="submit" class="logout-button" value="Logout">
                </form>
            <?php endif; ?>
        <?php else: ?>
            <form action="/actions/login.php" method="post">
                <input type="text" name="username" placeholder="username" required>
                <input type="password" name="password" placeholder="password" required>
                <input type="submit" class="login-button" value="Login">
            </form>
        <?php endif; ?>
    </div>

    <!-- Post form -->
    <div class="postForm">
        <?php if (!$isLoggedIn): ?>
            <p class="login-to-post">Login to post content.</p>
        <?php elseif ($isActive): ?>
            <form action="/actions/post.php" method="post">
                <input type="text" name="postContent" placeholder="Write a post..." required>
                <input type="submit" class="post-button" value="Post">
            </form>
        <?php else: ?>
            <form action="/actions/logout.php" method="post">
                <input type="submit" class="logout-button" value="Logout">
            </form>
        <?php endif; ?>
    </div>

    <!-- Signup form -->
    <div class="signupForm">
        <?php if (!$isLoggedIn): ?>
            <form action="/actions/signup.php" method="post">
                <input type="text" name="username" placeholder="username" required>
                <input type="date" name="dob" required>
                <input type="password" name="password" placeholder="password" required>
                <input type="password" name="confirmPassword" placeholder="confirm password" required>
                <input type="submit" class="signup-button" value="Sign up">
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!$isBanned): ?>
<div class="posts-container">
    <?php foreach ($posts as $row):
        $userID = $row['UserID'];
        $username = $row['UserUsername'];

        // Show username if user is admin, owner, or friend
        $showUsername = $isLoggedIn && ($isAdmin || $userID == $currentUserID || in_array($userID, $friends));
    ?>
    <div class="post-card">
        <div class="<?= $showUsername ? 'username' : 'usernameUnknown' ?>">
            <?php if ($showUsername): ?>
                <a href="/users/<?= urlencode($username) ?>"><?= htmlspecialchars($username) ?></a>
            <?php endif; ?>
        </div>
        <div class="timestamp"><?= htmlspecialchars($row['PostTimestamp']) ?></div>
        <div class="content"><?= htmlspecialchars($row['PostContent']) ?></div>

        <?php if ($isAdmin || $userID == $currentUserID): ?>
            <form action="/actions/deletepost.php" method="post" class="delete-post-form">
                <input type="hidden" name="postID" value="<?= $row['PostID'] ?>">
                <input type="submit" value="Delete Post">
            </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>
