<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the request path
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$trimmedPath = ltrim($requestPath, '/');

// Helper: include page and exit
function route($file) {
    if (file_exists($file)) {
        include $file;
        exit();
    } else {
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        exit();
    }
}

// Serve user profiles: /users/username
if (str_starts_with($trimmedPath, 'users/')) {
    $_GET['user'] = substr($trimmedPath, strlen('users/'));
    route('profile.php');
}

// Serve homepage
if ($trimmedPath === '' || $trimmedPath === 'index.php') {
    route('posts.php'); // posts.php handles sessions internally
}

// Serve static assets (CSS, JS, images, etc.)
$staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico'];
$extension = pathinfo($trimmedPath, PATHINFO_EXTENSION);

if (in_array(strtolower($extension), $staticExtensions)) {
    $file = __DIR__ . '/' . $trimmedPath;
    if (file_exists($file)) {
        // Serve file with proper Content-Type
        $mimeTypes = [
            'css' => 'text/css',
            'js'  => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg'=> 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
        ];
        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';
        header('Content-Type: ' . $contentType);
        readfile($file);
        exit();
    }
}

// Dynamic routing for any other PHP page
$pagePath = __DIR__ . '/' . $trimmedPath;
if (file_exists($pagePath) && is_file($pagePath)) {
    route($pagePath);
}

// If no route matched, return 404
http_response_code(404);
echo "<h1>404 - Page Not Found</h1>";
exit();
