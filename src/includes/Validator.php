<?php
/**
 * Validator — comprehensive form validation
 */

namespace App\Includes;

class Validator
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validate required fields
     */
    public function required(string ...$fields): self
    {
        foreach ($fields as $field) {
            $value = $this->data[$field] ?? '';
            if (empty($value) && $value !== '0') {
                $this->errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        return $this;
    }

    /**
     * Validate email format
     */
    public function email(string $field): self
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && !Security::validateEmail($value)) {
            $this->errors[$field][] = 'Please enter a valid email address';
        }
        return $this;
    }

    /**
     * Validate phone format
     */
    public function phone(string $field): self
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && !Security::validatePhone($value)) {
            $this->errors[$field][] = 'Please enter a valid phone number';
        }
        return $this;
    }

    /**
     * Validate minimum string length
     */
    public function minLength(string $field, int $min): self
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && mb_strlen($value) < $min) {
            $this->errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters";
        }
        return $this;
    }

    /**
     * Validate maximum string length
     */
    public function maxLength(string $field, int $max): self
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && mb_strlen($value) > $max) {
            $this->errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max} characters";
        }
        return $this;
    }

    /**
     * Validate date is in the future
     */
    public function futureDate(string $field): self
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value)) {
            $timestamp = strtotime($value);
            if ($timestamp === false) {
                $this->errors[$field][] = 'Invalid date format';
            } elseif ($timestamp < strtotime('today')) {
                $this->errors[$field][] = 'Date must be today or later';
            }
        }
        return $this;
    }

    /**
     * Validate time slot is within business hours
     */
    public function businessHours(string $dateField, string $timeField): self
    {
        $date = $this->data[$dateField] ?? '';
        $time = $this->data[$timeField] ?? '';

        if (empty($date) || empty($time)) {
            return $this;
        }

        $dayOfWeek = strtolower(date('D', strtotime($date)));
        
        // Default hours if DB is not available
        $defaultHours = [
            'mon' => ['open' => '09:00', 'close' => '18:00'],
            'tue' => ['open' => '09:00', 'close' => '18:00'],
            'wed' => ['open' => '09:00', 'close' => '18:00'],
            'thu' => ['open' => '09:00', 'close' => '18:00'],
            'fri' => ['open' => '10:00', 'close' => '16:00'],
            'sat' => ['open' => '10:00', 'close' => '14:00'],
            'sun' => ['open' => 'closed', 'close' => 'closed'],
        ];
        
        $hours = $defaultHours;

        // Try to load from DB
        try {
            $settings = \App\Config\Database::query(
                "SELECT setting_value FROM settings WHERE setting_key = 'operating_hours'"
            )->fetchColumn();
            if ($settings) {
                $dbHours = json_decode($settings, true);
                if (is_array($dbHours)) {
                    $hours = $dbHours;
                }
            }
        } catch (\Exception $e) {
            // Fall back to default hours
        }

        if (isset($hours[$dayOfWeek])) {
            $dayHours = $hours[$dayOfWeek];
            if (($dayHours['open'] ?? '') === 'closed') {
                $this->errors[$timeField][] = 'We are closed on ' . ucfirst($dayOfWeek) . 's';
                return $this;
            }

            $timeInt = strtotime($time);
            $openInt = strtotime($dayHours['open']);
            $closeInt = strtotime($dayHours['close']);

            if ($timeInt < $openInt || $timeInt >= $closeInt) {
                $this->errors[$timeField][] = 'Please select a time between ' . $dayHours['open'] . ' and ' . $dayHours['close'];
            }
        }

        return $this;
    }

    /**
     * Custom regex validation
     */
    public function regex(string $field, string $pattern, string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && preg_match($pattern, $value) !== 1) {
            $this->errors[$field][] = $message ?: "Invalid format for " . str_replace('_', ' ', $field);
        }
        return $this;
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error message
     */
    public function getFirstError(): ?string
    {
        foreach ($this->errors as $field => $msgs) {
            return $msgs[0];
        }
        return null;
    }

    /**
     * Get sanitized valid data
     */
    public function getValidatedData(): array
    {
        $sanitized = [];
        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = Security::sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
