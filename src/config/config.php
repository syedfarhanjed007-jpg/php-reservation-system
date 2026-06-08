<?php
/**
 * Application Configuration — Test Environment
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'reservation_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'ReservationPro');
define('APP_URL', 'http://localhost:8080');
define('APP_ENV', 'development');
define('APP_DEBUG', true);

define('CSRF_TOKEN_SECRET', 'test-secret-key-for-development-only');
define('SESSION_LIFETIME', 7200);
define('BCRYPT_COST', 12);

define('TIME_SLOT_INTERVAL', 30);
define('MAX_ADVANCE_DAYS', 90);
define('MIN_NOTICE_HOURS', 2);
define('MAX_GUESTS_PER_SLOT', 20);
define('BUSINESS_TIMEZONE', 'Asia/Riyadh');

define('MAIL_HOST', '');
define('MAIL_PORT', 587);
define('MAIL_USER', '');
define('MAIL_PASS', '');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM', 'test@localhost');
define('MAIL_FROM_NAME', APP_NAME);

// Session config
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', APP_ENV === 'production' ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_start();
}
date_default_timezone_set(BUSINESS_TIMEZONE);
