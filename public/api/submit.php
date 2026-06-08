<?php
/**
 * API: Submit Reservation
 * Handles the form submission via AJAX
 */

require_once __DIR__ . '/../../src/autoload.php';
require_once __DIR__ . '/../../src/config/config.php';

use App\Includes\Security;
use App\Handlers\ReservationHandler;

// CORS headers
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Rate limiting
if (!Security::checkRateLimit('reservation_submit', 10, 120)) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Too many requests. Please try again in a few minutes.',
    ]);
    exit;
}

try {
    $result = ReservationHandler::create($_POST);

    if ($result['success']) {
        http_response_code(200);
    } else {
        http_response_code(422);
    }

    echo json_encode($result);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => APP_DEBUG ? $e->getMessage() : 'An internal error occurred',
    ]);
}
