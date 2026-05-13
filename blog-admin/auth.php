<?php
session_start();
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

define('POSTS_DIR', dirname(__DIR__) . '/blog/posts/');
define('BLOG_INDEX', dirname(__DIR__) . '/blog/index.html');
