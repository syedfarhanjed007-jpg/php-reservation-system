<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve files from public/ directly
if (strpos($path, '/admin/') === 0) {
    $file = __DIR__ . $path;
    if (file_exists($file)) {
        return false;
    }
    http_response_code(404);
    echo "Not Found";
    return true;
}

// Serve public files
$publicFile = __DIR__ . '/public' . $path;
if ($path === '/' || $path === '') {
    require __DIR__ . '/public/index.php';
    return true;
}

if (file_exists($publicFile)) {
    return false;
}

// Fallback to index.php for API routes
$apiFile = __DIR__ . '/public/api/' . basename($path) . '.php';
if (file_exists($apiFile)) {
    require $apiFile;
    return true;
}

// 404
http_response_code(404);
echo "Not Found";
return true;
