<?php
// Ensure user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['username']);
$currentUserID = $isLoggedIn ? $_SESSION['id'] : null;
$currentUserType = $isLoggedIn ? $_SESSION['type'] : null;
$isAdmin = ($currentUserType === 'Admin');

if (!$isLoggedIn || !$isAdmin) {
    header("Location: /index.php");
    exit();
}

// Database connection
$servername = "127.0.0.1";
$dbUsername = "root";
$dbPassword = "root";
$port = 8889;

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=mininet", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// Fetch users
function fetchUsers($conn, $type) {
    $stmt = $conn->prepare("
        SELECT UserID, UserUsername, UserStatus, UserType, UserDateOfBirth
        FROM Users
        WHERE UserType = :type
    ");
    $stmt->bindParam(':type', $type);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$admins = fetchUsers($conn, 'Admin');
$users = fetchUsers($conn, 'Regular');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mininet | Users</title>
    <link rel="stylesheet" href="/css/users.css">
    <link rel="stylesheet" href="/css/header.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

<main>
    <h2>Admins</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Status</th>
            <th>D.O.B.</th>
        </tr>
        <?php foreach ($admins as $admin): ?>
        <tr>
            <td><?= $admin['UserID'] ?></td>
            <td><a href="/users/<?= urlencode($admin['UserUsername']) ?>"><?= htmlspecialchars($admin['UserUsername']) ?></a></td>
            <td><?= $admin['UserStatus'] ?></td>
            <td><?= $admin['UserDateOfBirth'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</main>

<main>
    <h2>Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Status</th>
            <th>D.O.B.</th>
            <th>Restrict</th>
            <th>Ban</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['UserID'] ?></td>
            <td><a href="/users/<?= urlencode($user['UserUsername']) ?>"><?= htmlspecialchars($user['UserUsername']) ?></a></td>
            <td><?= $user['UserStatus'] ?></td>
            <td><?= $user['UserDateOfBirth'] ?></td>
            <td>
                <?php if ($user['UserStatus'] !== 'Banned'): ?>
                <form action="/actions/moderate.php" method="post">
                    <input type="hidden" name="action" value="restrict">
                    <input type="hidden" name="userID" value="<?= $user['UserID'] ?>">
                    <input type="hidden" name="userStatus" value="<?= $user['UserStatus'] ?>">
                    <input type="submit" value="<?= $user['UserStatus'] === 'Active' ? 'Restrict' : 'Unrestrict' ?>">
                </form>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($user['UserStatus'] !== 'Restricted'): ?>
                <form action="/actions/moderate.php" method="post">
                    <input type="hidden" name="action" value="ban">
                    <input type="hidden" name="userID" value="<?= $user['UserID'] ?>">
                    <input type="hidden" name="userStatus" value="<?= $user['UserStatus'] ?>">
                    <input type="submit" value="<?= $user['UserStatus'] === 'Active' ? 'Ban' : 'Unban' ?>">
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</main>

</body>
</html>
