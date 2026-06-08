<?php
/**
 * Security Helper — CSRF, input sanitization, rate limiting
 */

namespace App\Includes;

class Security
{
    /**
     * Generate a CSRF token and store in session
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken(string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitize string for safe output
     */
    public static function sanitizeOutput(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize input string (strip tags, trim)
     */
    public static function sanitizeInput(string $value): string
    {
        return trim(strip_tags($value));
    }

    /**
     * Validate email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number (international format)
     */
    public static function validatePhone(string $phone): bool
    {
        // Accepts: +966501234567, 0501234567, +1-555-123-4567
        return preg_match('/^\+?[\d\s\-\(\)]{7,20}$/', $phone) === 1;
    }

    /**
     * Sanitize phone to store consistently
     */
    public static function sanitizePhone(string $phone): string
    {
        // Remove everything except digits and leading +
        return preg_replace('/[^\d+]/', '', $phone);
    }

    /**
     * Simple rate limiting by IP
     * Returns true if allowed, false if rate limited
     */
    public static function checkRateLimit(string $action, int $maxAttempts = 5, int $windowSeconds = 60): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'rate_limit_' . $action . '_' . md5($ip);
        $file = sys_get_temp_dir() . '/' . $key . '.tmp';

        $data = @file_get_contents($file);
        $attempts = $data ? json_decode($data, true) : ['count' => 0, 'first_attempt' => time()];

        // Reset window if expired
        if (time() - $attempts['first_attempt'] > $windowSeconds) {
            $attempts = ['count' => 0, 'first_attempt' => time()];
        }

        $attempts['count']++;

        file_put_contents($file, json_encode($attempts), LOCK_EX);

        return $attempts['count'] <= $maxAttempts;
    }

    /**
     * Generate a unique confirmation code
     */
    public static function generateConfirmationCode(): string
    {
        return strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    /**
     * Hash password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
