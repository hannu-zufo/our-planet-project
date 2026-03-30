<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Credentials config - override defaults here
define('ADMIN_USERNAME', 'xkqmvp');
define('ADMIN_PASSWORD_HASH', password_hash('changeme123', PASSWORD_DEFAULT));

// Path to blog posts directory (relative to this file's parent)
define('POSTS_DIR', dirname(__DIR__) . '/blog/posts/');
define('BLOG_INDEX', dirname(__DIR__) . '/blog/index.html');
define('BLOG_CSS_PATH', '../../css/style.css');
define('BLOG_CSS_BLOG_PATH', '../../css/blog.css');
