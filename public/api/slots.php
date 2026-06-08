<?php
/**
 * API: Get Available Time Slots
 * Returns JSON of available slots for a given date
 */

require_once __DIR__ . '/../../src/autoload.php';
require_once __DIR__ . '/../../src/config/config.php';

use App\Handlers\ReservationHandler;

// CORS headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$date = $_GET['date'] ?? '';

if (empty($date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Date parameter is required']);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

try {
    $slots = ReservationHandler::getAvailableSlots($date);
    http_response_code(200);
    echo json_encode($slots);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to load slots',
        'message' => APP_DEBUG ? $e->getMessage() : 'Internal server error',
    ]);
}
