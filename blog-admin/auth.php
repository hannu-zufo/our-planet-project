<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

define('ADMIN_USERNAME', 'xkqmvp');
define('ADMIN_PASSWORD_HASH', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

define('POSTS_DIR', dirname(__DIR__) . '/blog/posts/');
define('BLOG_INDEX', dirname(__DIR__) . '/blog/index.html');
