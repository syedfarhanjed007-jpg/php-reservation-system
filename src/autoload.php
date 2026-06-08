<?php
/**
 * PSR-4 Autoloader
 * Loads classes from src/ directory
 * Handles case-insensitive directory names on case-sensitive filesystems
 */

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $path = str_replace('\\', '/', $relativeClass);
    
    // Try exact case first
    $file = $baseDir . $path . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }
    
    // Try lowercase directory names (for case-sensitive filesystems)
    $parts = explode('/', $path);
    $filename = array_pop($parts);
    $partsLower = array_map('strtolower', $parts);
    $file = $baseDir . implode('/', $partsLower) . '/' . $filename . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }
});
