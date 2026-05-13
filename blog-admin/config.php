<?php
/**
 * Shared admin credentials (no session logic — safe to require from login + auth).
 */
define('ADMIN_USERNAME', 'xkqmvp');
/** Bcrypt hash for the live admin password (password_verify in index.php). */
define('ADMIN_PASSWORD_HASH', '$2b$10$rMlHmtGpw4QKQC8Q7zY/t..98gBUbqRgaH7KMDwFbWXDewMVkiSu2');
