<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    die("Please log in to view messages.");
}


//Session info
$currentUserID = $_SESSION['id'];
$currentUsername = $_SESSION['username'];
$currentUserType = $_SESSION['type'];
$currentStatus = $_SESSION['status'];
$isAdmin = ($currentUserType === 'Admin');
$isActive = ($currentStatus === 'Active');
$isBanned = ($currentStatus === 'Banned');

//Database connection
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

//Fetch messages
try {
    $stmt = $conn->prepare("
        SELECT
            M.MessageID,
            M.MessageContent,
            M.MessageTimestamp,
            MP.UserSenderID,
            MP.UserReceiverID,
            Us.UserUsername AS SenderUsername,
            Ur.UserUsername AS ReceiverUsername
        FROM Messages M
        INNER JOIN MessageParticipants MP ON M.MessageID = MP.MessageID
        INNER JOIN Users Us ON MP.UserSenderID = Us.UserID
        INNER JOIN Users Ur ON MP.UserReceiverID = Ur.UserID
        WHERE MP.UserSenderID = :id OR MP.UserReceiverID = :id
        ORDER BY M.MessageTimestamp DESC
    ");
    $stmt->bindParam(':id', $currentUserID, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching messages: " . $e->getMessage());
}

//Fetch friends
$friends = [];
try {
    $friendStmt = $conn->prepare("
        SELECT User1ID, User2ID
        FROM UserFriends
        WHERE User1ID = :id OR User2ID = :id
    ");
    $friendStmt->bindParam(':id', $currentUserID, PDO::PARAM_INT);
    $friendStmt->execute();
    $friendIDs = [];
    foreach ($friendStmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
        $friendIDs[] = ($f['User1ID'] == $currentUserID) ? $f['User2ID'] : $f['User1ID'];
    }

    if (!empty($friendIDs)) {
        $placeholders = implode(',', array_fill(0, count($friendIDs), '?'));
        $stmt = $conn->prepare("SELECT UserID, UserUsername FROM Users WHERE UserID IN ($placeholders)");
        $stmt->execute($friendIDs);
        $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    die("Error fetching friends: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>mininet | messages </title>
    <link rel="icon" type="image/x-icon" href="/images/favicon.png">
    <link rel="stylesheet" href="/css/messages.css">
    <link rel="stylesheet" href="/css/header.css">
</head>
<body>
    <?php include 'includes/header.php';?>

<?php if (!$isBanned): ?>
<div class="forms-container">
    <div class="postForm"></div> <!-- empty left placeholder -->

    <div class="postForm">
        <?php if ($isActive): ?>
            <form action="/actions/message.php" method="post">
                <input type="text" name="messageContent" placeholder="write a message..." required>
                <input type="hidden" name="senderID" value="<?= $currentUserID ?>">
                <select name="receiverID" required>
                    <?php foreach ($friends as $friend): ?>
                        <option value="<?= $friend['UserID'] ?>"><?= htmlspecialchars($friend['UserUsername']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="post-button" value="Send">
            </form>
        <?php else: ?>
            <p class="login-to-post">You are restricted from sending messages.</p>
        <?php endif; ?>
    </div>

    <div class="postForm"></div> <!-- empty right placeholder -->
</div>
<!-- Posts container -->
<div class="posts-container">
    <?php if (empty($messages)): ?>
        <p class="no-posts-message">No messages yet.</p>
    <?php else: ?>
        <?php foreach ($messages as $row):
            $isSender = $row['UserSenderID'] == $currentUserID;
            $otherID = $isSender ? $row['UserReceiverID'] : $row['UserSenderID'];
            $otherUsername = $isSender ? $row['ReceiverUsername'] : $row['SenderUsername'];
            $label = $isSender ? "to" : "from";
        ?>
        <div class="post-card message-card">
            <div class="username">
                <span><?= $label ?> </span>
                <a href="/users/<?= urlencode(htmlspecialchars($otherUsername)) ?>"><?= htmlspecialchars($otherUsername) ?></a>
            </div>
            <div class="timestamp"><?= htmlspecialchars($row['MessageTimestamp']) ?></div>
            <div class="content"><?= htmlspecialchars($row['MessageContent']) ?></div>

            <?php if ($isSender): ?>
            <form action="/actions/deletemessage.php" method="post" class="delete-post-form">
                <input type="hidden" name="messageID" value="<?= $row['MessageID'] ?>">
                <input type="submit" value="Delete Message">
            </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php endif; ?>
</body>
</html>