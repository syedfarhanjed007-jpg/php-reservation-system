<?php
/**
 * Reservation Handler — create, manage, check availability
 */

namespace App\Handlers;

use App\Config\Database;
use App\Includes\Security;
use App\Includes\Validator;

class ReservationHandler
{
    /**
     * Create a new reservation
     */
    public static function create(array $data): array
    {
        try {
            // Validate
            $validator = new Validator($data);
            $validator
                ->required('first_name', 'last_name', 'email', 'reservation_date', 'reservation_time')
                ->email('email')
                ->phone('phone')
                ->minLength('first_name', 2)
                ->minLength('last_name', 2)
                ->futureDate('reservation_date')
                ->businessHours('reservation_date', 'reservation_time');

            if (!$validator->passes()) {
                return [
                    'success' => false,
                    'errors'  => $validator->getErrors(),
                    'message' => $validator->getFirstError(),
                ];
            }

            $validData = $validator->getValidatedData();

            // Check availability
            $availabilityCheck = self::checkAvailability(
                $validData['reservation_date'] ?? '',
                $validData['reservation_time'] ?? ''
            );

            if (!$availabilityCheck['available']) {
                return [
                    'success' => false,
                    'errors'  => ['time' => $availabilityCheck['message']],
                    'message' => $availabilityCheck['message'],
                ];
            }

            Database::getInstance()->getConnection()->beginTransaction();

            // Find or create customer
            $customerId = self::findOrCreateCustomer($validData);

            // Generate confirmation code
            $code = Security::generateConfirmationCode();

            // Calculate end time
            $serviceId = $validData['service_id'] ?? null;
            $duration = 60; // default 60 minutes
            if ($serviceId) {
                $serviceData = Database::query(
                    "SELECT duration_minutes FROM services WHERE id = ? AND is_active = 1",
                    [$serviceId]
                )->fetch();
                if ($serviceData) {
                    $duration = (int) $serviceData['duration_minutes'];
                }
            }

            $start = strtotime($validData['reservation_time']);
            $endTime = date('H:i:s', $start + ($duration * 60));

            // Insert reservation
            Database::query(
                "INSERT INTO reservations 
                (customer_id, service_id, reservation_date, reservation_time, end_time, guests, notes, confirmation_code, source, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'web', 'pending')",
                [
                    $customerId,
                    !empty($validData['service_id']) ? (int)$validData['service_id'] : null,
                    $validData['reservation_date'],
                    date('H:i:s', $start),
                    $endTime,
                    (int)($validData['guests'] ?? 1),
                    $validData['notes'] ?? null,
                    $code,
                ]
            );

            $reservationId = Database::lastInsertId();

            Database::getInstance()->getConnection()->commit();

            // Send email notification
            self::sendConfirmationEmail($validData, $code, $reservationId);

            return [
                'success'           => true,
                'reservation_id'    => $reservationId,
                'confirmation_code' => $code,
                'message'           => 'Reservation confirmed! Your confirmation code is: ' . $code,
            ];
        } catch (\Exception $e) {
            Database::getInstance()->getConnection()->rollBack();

            if (APP_DEBUG) {
                throw $e;
            }

            return [
                'success' => false,
                'errors'  => ['system' => 'An error occurred. Please try again.'],
                'message' => 'System error. Please try again later.',
            ];
        }
    }

    /**
     * Check if a time slot is available
     */
    public static function checkAvailability(string $date, string $time, ?int $serviceId = null): array
    {
        // Check if date is within allowed range
        $maxDate = date('Y-m-d', strtotime('+' . MAX_ADVANCE_DAYS . ' days'));
        $minDate = date('Y-m-d', strtotime('+' . MIN_NOTICE_HOURS . ' hours'));

        if ($date < $minDate) {
            return [
                'available' => false,
                'message'   => 'Reservations must be made at least ' . MIN_NOTICE_HOURS . ' hours in advance',
            ];
        }

        if ($date > $maxDate) {
            return [
                'available' => false,
                'message'   => 'Reservations can only be made up to ' . MAX_ADVANCE_DAYS . ' days in advance',
            ];
        }

        // Check for availability exceptions
        $exception = Database::query(
            "SELECT is_available, start_time, end_time 
             FROM availability_exceptions 
             WHERE (service_id IS NULL OR service_id = ?) 
               AND exception_date = ?
             ORDER BY is_available ASC LIMIT 1",
            [$serviceId, $date]
        )->fetch();

        if ($exception) {
            if (!$exception['is_available']) {
                // Fully blocked
                if ($exception['start_time'] && $exception['end_time']) {
                    $timeInt = strtotime($time);
                    $blockStart = strtotime($exception['start_time']);
                    $blockEnd = strtotime($exception['end_time']);
                    if ($timeInt >= $blockStart && $timeInt < $blockEnd) {
                        return [
                            'available' => false,
                            'message'   => 'This time slot is blocked: ' . ($exception['reason'] ?? 'Unavailable'),
                        ];
                    }
                } else {
                    return [
                        'available' => false,
                        'message'   => 'This date is fully booked',
                    ];
                }
            }
        }

        // Check existing reservations (simplified — count per slot)
        $slotEnd = date('H:i:s', strtotime($time) + (TIME_SLOT_INTERVAL * 60));
        $existingCount = Database::query(
            "SELECT COUNT(*) as cnt FROM reservations 
             WHERE reservation_date = ? 
               AND reservation_time = ? 
               AND status NOT IN ('cancelled', 'no_show')",
            [$date, $time]
        )->fetchColumn();

        if ($existingCount >= MAX_GUESTS_PER_SLOT) {
            return [
                'available' => false,
                'message'   => 'This time slot is fully booked. Please choose another time.',
            ];
        }

        return [
            'available' => true,
            'message'   => 'Slot is available',
            'slots_remaining' => MAX_GUESTS_PER_SLOT - (int)$existingCount,
        ];
    }

    /**
     * Get available time slots for a given date
     */
    public static function getAvailableSlots(string $date): array
    {
        $dayOfWeek = strtolower(date('D', strtotime($date)));

        // Get operating hours
        try {
            $hoursJson = Database::query(
                "SELECT setting_value FROM settings WHERE setting_key = 'operating_hours'"
            )->fetchColumn();
            $hours = json_decode($hoursJson, true) ?? [];
        } catch (\Exception $e) {
            $hours = [];
        }

        $defaults = [
            'mon' => ['open' => '09:00', 'close' => '18:00'],
            'tue' => ['open' => '09:00', 'close' => '18:00'],
            'wed' => ['open' => '09:00', 'close' => '18:00'],
            'thu' => ['open' => '09:00', 'close' => '18:00'],
            'fri' => ['open' => '10:00', 'close' => '16:00'],
            'sat' => ['open' => '10:00', 'close' => '14:00'],
            'sun' => ['open' => 'closed', 'close' => 'closed'],
        ];

        $dayHours = $hours[$dayOfWeek] ?? $defaults[$dayOfWeek] ?? ['open' => 'closed', 'close' => 'closed'];

        if (($dayHours['open'] ?? '') === 'closed') {
            return ['date' => $date, 'slots' => [], 'message' => 'Closed on this day'];
        }

        $openTime = strtotime($dayHours['open']);
        $closeTime = strtotime($dayHours['close']);
        $interval = TIME_SLOT_INTERVAL * 60;
        $slots = [];

        // Get existing reservations for this date to check availability
        $existing = Database::query(
            "SELECT reservation_time, COUNT(*) as cnt 
             FROM reservations 
             WHERE reservation_date = ? 
               AND status NOT IN ('cancelled', 'no_show')
             GROUP BY reservation_time",
            [$date]
        )->fetchAll();

        $bookedMap = [];
        foreach ($existing as $row) {
            $bookedMap[$row['reservation_time']] = (int)$row['cnt'];
        }

        // Get exceptions for this date
        $exceptions = Database::query(
            "SELECT start_time, end_time, is_available 
             FROM availability_exceptions 
             WHERE exception_date = ? 
               AND service_id IS NULL",
            [$date]
        )->fetchAll();

        $blockedRanges = [];
        foreach ($exceptions as $exc) {
            if (!$exc['is_available']) {
                $blockedRanges[] = [
                    'start' => strtotime($exc['start_time'] ?? '00:00'),
                    'end'   => strtotime($exc['end_time'] ?? '23:59'),
                ];
            }
        }

        for ($time = $openTime; $time < $closeTime; $time += $interval) {
            $timeStr = date('H:i:s', $time);
            $displayTime = date('h:i A', $time);

            // Check blocked ranges
            $isBlocked = false;
            foreach ($blockedRanges as $range) {
                if ($time >= $range['start'] && $time < $range['end']) {
                    $isBlocked = true;
                    break;
                }
            }

            $bookedCount = $bookedMap[$timeStr] ?? 0;
            $remaining = MAX_GUESTS_PER_SLOT - $bookedCount;

            $slots[] = [
                'time'            => $timeStr,
                'display'         => $displayTime,
                'available'       => !$isBlocked && $remaining > 0,
                'remaining'       => max(0, $remaining),
                'is_booked'       => $bookedCount >= MAX_GUESTS_PER_SLOT,
            ];
        }

        return [
            'date'  => $date,
            'slots' => $slots,
        ];
    }

    /**
     * Find or create a customer
     */
    private static function findOrCreateCustomer(array $data): int
    {
        // Try to find existing customer by email
        $existing = Database::query(
            "SELECT id FROM customers WHERE email = ?",
            [$data['email']]
        )->fetch();

        if ($existing) {
            // Update info if different
            Database::query(
                "UPDATE customers SET 
                 first_name = ?, last_name = ?, phone = COALESCE(NULLIF(?, ''), phone),
                 company = COALESCE(NULLIF(?, ''), company)
                 WHERE id = ?",
                [
                    $data['first_name'],
                    $data['last_name'],
                    $data['phone'] ?? '',
                    $data['company'] ?? '',
                    $existing['id'],
                ]
            );
            return (int)$existing['id'];
        }

        // Create new customer
        Database::query(
            "INSERT INTO customers (first_name, last_name, email, phone, company) VALUES (?, ?, ?, ?, ?)",
            [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['company'] ?? null,
            ]
        );

        return (int)Database::lastInsertId();
    }

    /**
     * Send confirmation email
     */
    private static function sendConfirmationEmail(array $data, string $code, int $reservationId): void
    {
        $enabled = getenv('MAIL_USER') && getenv('MAIL_PASS');
        if (!$enabled) {
            return; // SMTP not configured
        }

        $to      = $data['email'];
        $subject = APP_NAME . ' - Reservation Confirmed #' . $code;
        $guests = $data['guests'] ?? 1;

        $message = '<html><body style="font-family:Arial,sans-serif;padding:20px;">';
        $message .= '<h2 style="color:#2563eb;">Reservation Confirmed!</h2>';
        $message .= '<p>Dear ' . htmlspecialchars($data['first_name']) . ',</p>';
        $message .= '<p>Your reservation has been confirmed.</p>';
        $message .= '<table style="border-collapse:collapse;width:100%;max-width:500px;">';
        $message .= '<tr><td style="padding:8px;border-bottom:1px solid #eee;"><strong>Confirmation Code</strong></td>';
        $message .= '<td style="padding:8px;border-bottom:1px solid #eee;color:#2563eb;font-weight:bold;">' . htmlspecialchars($code) . '</td></tr>';
        $message .= '<tr><td style="padding:8px;border-bottom:1px solid #eee;"><strong>Date</strong></td>';
        $message .= '<td style="padding:8px;border-bottom:1px solid #eee;">' . htmlspecialchars($data['reservation_date']) . '</td></tr>';
        $message .= '<tr><td style="padding:8px;border-bottom:1px solid #eee;"><strong>Time</strong></td>';
        $message .= '<td style="padding:8px;border-bottom:1px solid #eee;">' . htmlspecialchars($data['reservation_time']) . '</td></tr>';
        $message .= '<tr><td style="padding:8px;"><strong>Guests</strong></td>';
        $message .= '<td style="padding:8px;">' . (int)$guests . '</td></tr>';
        $message .= '</table>';
        $message .= '<p style="margin-top:20px;color:#666;">Please keep this code for reference.</p>';
        $message .= '<p>Best regards,<br>' . APP_NAME . ' Team</p>';
        $message .= '</body></html>';

        // Simple mail() fallback; for production use PHPMailer/SwiftMailer
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";

        @mail($to, $subject, $message, $headers);
    }

    /**
     * Get reservation by confirmation code (for lookup)
     */
    public static function getByCode(string $code): ?array
    {
        $data = Database::query(
            "SELECT r.*, c.first_name, c.last_name, c.email, c.phone, c.company,
                    s.name AS service_name
             FROM reservations r
             JOIN customers c ON r.customer_id = c.id
             LEFT JOIN services s ON r.service_id = s.id
             WHERE r.confirmation_code = ?",
            [$code]
        )->fetch();

        return $data ?: null;
    }

    /**
     * Cancel a reservation
     */
    public static function cancel(string $code): array
    {
        $reservation = self::getByCode($code);
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reservation not found'];
        }

        Database::query(
            "UPDATE reservations SET status = 'cancelled' WHERE confirmation_code = ?",
            [$code]
        );

        return ['success' => true, 'message' => 'Reservation cancelled successfully'];
    }
}
