<?php
/**
 * Application Configuration
 * PHP Professional Reservation System
 *
 * Copy this file to config.php and fill in your values.
 */

// ------------------------------------------------------------
// Database Configuration
// ------------------------------------------------------------
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'reservation_system');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// ------------------------------------------------------------
// Application Settings
// ------------------------------------------------------------
define('APP_NAME', 'ReservationPro');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8080');
define('APP_ENV', getenv('APP_ENV') ?: 'development'); // development | production
define('APP_DEBUG', getenv('APP_DEBUG') ?: true);

// ------------------------------------------------------------
// Security
// ------------------------------------------------------------
define('CSRF_TOKEN_SECRET', getenv('CSRF_SECRET') ?: 'change-this-to-a-random-secret-key');
define('SESSION_LIFETIME', 7200); // 2 hours
define('BCRYPT_COST', 12);

// ------------------------------------------------------------
// Reservation Settings
// ------------------------------------------------------------
define('TIME_SLOT_INTERVAL', 30);        // minutes
define('MAX_ADVANCE_DAYS', 90);           // how far ahead bookings are allowed
define('MIN_NOTICE_HOURS', 2);            // minimum hours before booking
define('MAX_GUESTS_PER_SLOT', 20);
define('BUSINESS_TIMEZONE', 'Asia/Riyadh');

// ------------------------------------------------------------
// Email (SMTP)
// ------------------------------------------------------------
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.gmail.com');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
define('MAIL_USER', getenv('MAIL_USER') ?: '');
define('MAIL_PASS', getenv('MAIL_PASS') ?: '');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM', getenv('MAIL_FROM') ?: 'noreply@example.com');
define('MAIL_FROM_NAME', APP_NAME);

// ------------------------------------------------------------
// Error Reporting (auto-adjusts for production)
// ------------------------------------------------------------
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ------------------------------------------------------------
// Session Configuration
// ------------------------------------------------------------
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', APP_ENV === 'production' ? '1' : '0');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------------------------------------------
// Timezone
// ------------------------------------------------------------
date_default_timezone_set(BUSINESS_TIMEZONE);
