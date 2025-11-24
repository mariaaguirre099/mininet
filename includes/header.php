<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logged-in user info
$isLoggedIn = isset($_SESSION['username']);
$currentUsername = $isLoggedIn ? $_SESSION['username'] : null;
$currentUserType = $isLoggedIn ? $_SESSION['type'] : null;
$isAdmin = ($currentUserType === 'Admin');

// Determine current page for highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Determine profile page and accessed username
$profileUsername = null;
$isOwnProfile = false;
$requestUri = $_SERVER['REQUEST_URI'];
if ($isLoggedIn && preg_match('#^/users/([^/]+)#', $requestUri, $matches)) {
    $profileUsername = $matches[1];
    if ($profileUsername === $currentUsername) {
        $isOwnProfile = true;
    }
}
?>
<div class="headercontainer">
    <!-- Home link -->
    <div class="headeritem">
        <h1><a href="/posts.php" class="<?= $currentPage === 'posts.php' ? 'active' : '' ?>">home</a></h1>
    </div>

    <?php if ($isLoggedIn): ?>
        <!-- Profile link -->
        <div class="headeritem">
            <h1>
                <a href="/users/<?= urlencode($currentUsername) ?>" class="<?= $isOwnProfile ? 'active' : '' ?>">
                    profile
                </a>
            </h1>
        </div>

        <!-- Messages link -->
        <div class="headeritem">
            <h1><a href="/messages.php" class="<?= $currentPage === 'messages.php' ? 'active' : '' ?>">messages</a></h1>
        </div>

        <!-- Settings link -->
        <div class="headeritem">
            <h1><a href="/settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">settings</a></h1>
        </div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
        <!-- Admin Users link -->
        <div class="headeritem">
            <h1><a href="/users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">users</a></h1>
        </div>
    <?php endif; ?>
</div>

<?php if ($isLoggedIn): ?>
    <div class="current-user">
        <h1>
            <?= htmlspecialchars($currentUsername) ?>
            <?php if ($profileUsername && $profileUsername !== $currentUsername): ?>
                viewing <?= htmlspecialchars($profileUsername) ?>
            <?php endif; ?>
        </h1>
    </div>
<?php endif; ?>
