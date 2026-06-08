<?php
/**
 * Reservation Form — Frontend Entry Point
 */

require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../src/config/config.php';

use App\Includes\Security;

$csrfToken = Security::generateCsrfToken();

// Fetch services for the dropdown
$services = [];
try {
    $services = \App\Config\Database::query(
        "SELECT id, name, description, duration_minutes, max_capacity, price FROM services WHERE is_active = 1 ORDER BY name"
    )->fetchAll();
} catch (\Exception $e) {
    // Database not connected yet
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — Book a Reservation</title>
    <meta name="description" content="Professional reservation booking system. Book your appointment or service online.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ============================================================
           CSS Variables & Reset
           ============================================================ */
        :root {
            --color-bg: #f8fafc;
            --color-surface: #ffffff;
            --color-surface-hover: #f1f5f9;
            --color-border: #e2e8f0;
            --color-border-focus: #2563eb;
            --color-text: #0f172a;
            --color-text-secondary: #64748b;
            --color-text-muted: #94a3b8;
            --color-primary: #2563eb;
            --color-primary-hover: #1d4ed8;
            --color-primary-light: #eff6ff;
            --color-success: #059669;
            --color-success-light: #ecfdf5;
            --color-error: #dc2626;
            --color-error-light: #fef2f2;
            --color-warning: #d97706;
            --color-warning-light: #fffbeb;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-family);
            background: var(--color-bg);
            color: var(--color-text);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ============================================================
           Layout
           ============================================================ */
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: var(--color-surface);
            border-bottom: 1px solid var(--color-border);
            padding: 16px 24px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--color-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background: var(--color-primary);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 14px;
        }

        .header-cta {
            font-size: 14px;
            color: var(--color-text-secondary);
        }

        .header-cta a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .header-cta a:hover {
            text-decoration: underline;
        }

        .main {
            flex: 1;
            padding: 40px 24px 80px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ============================================================
           Hero Section
           ============================================================ */
        .hero {
            text-align: center;
            margin-bottom: 48px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--color-primary-light);
            color: var(--color-primary);
            font-size: 13px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 100px;
            margin-bottom: 20px;
        }

        .hero-badge-dot {
            width: 6px;
            height: 6px;
            background: var(--color-primary);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .hero h1 {
            font-size: clamp(32px, 5vw, 56px);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 16px;
        }

        .hero h1 span {
            background: linear-gradient(135deg, var(--color-primary), #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 18px;
            color: var(--color-text-secondary);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* ============================================================
           Form Layout — Two Column
           ============================================================ */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 32px;
            align-items: start;
            max-width: 1100px;
            margin: 0 auto;
        }

        @media (max-width: 900px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ============================================================
           Card
           ============================================================ */
        .card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 32px;
            box-shadow: var(--shadow-sm);
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .card-subtitle {
            font-size: 14px;
            color: var(--color-text-secondary);
            margin-bottom: 24px;
        }

        /* ============================================================
           Form Fields
           ============================================================ */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 6px;
        }

        .form-label .required {
            color: var(--color-error);
            margin-left: 2px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 11px 14px;
            font-size: 15px;
            font-family: var(--font-family);
            color: var(--color-text);
            background: var(--color-surface);
            border: 1.5px solid var(--color-border);
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: var(--color-border-focus);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-input.error,
        .form-select.error {
            border-color: var(--color-error);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-error {
            font-size: 13px;
            color: var(--color-error);
            margin-top: 4px;
            display: none;
        }

        .form-error.visible {
            display: block;
        }

        .form-input-icon-wrap {
            position: relative;
        }

        .form-input-icon-wrap .form-input {
            padding-left: 38px;
        }

        .form-input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-text-muted);
            font-size: 16px;
            pointer-events: none;
        }

        /* ============================================================
           Time Slot Grid
           ============================================================ */
        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 8px;
            margin-top: 12px;
        }

        .slot-btn {
            padding: 10px 6px;
            font-size: 13px;
            font-weight: 500;
            font-family: var(--font-family);
            border: 1.5px solid var(--color-border);
            border-radius: var(--radius-sm);
            background: var(--color-surface);
            color: var(--color-text);
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .slot-btn:hover:not(.slot-unavailable) {
            border-color: var(--color-primary);
            background: var(--color-primary-light);
        }

        .slot-btn.selected {
            border-color: var(--color-primary);
            background: var(--color-primary);
            color: white;
        }

        .slot-btn.slot-unavailable {
            opacity: 0.4;
            cursor: not-allowed;
            text-decoration: line-through;
        }

        .slot-btn .slot-remaining {
            display: block;
            font-size: 10px;
            font-weight: 400;
            color: var(--color-text-muted);
            margin-top: 2px;
        }

        .slot-btn.selected .slot-remaining {
            color: rgba(255, 255, 255, 0.8);
        }

        .slots-loading {
            text-align: center;
            padding: 20px;
            color: var(--color-text-secondary);
            font-size: 14px;
        }

        .slots-loading .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid var(--color-border);
            border-top-color: var(--color-primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .no-slots-message {
            text-align: center;
            padding: 24px;
            color: var(--color-text-secondary);
            background: var(--color-surface-hover);
            border-radius: var(--radius-sm);
            font-size: 14px;
        }

        /* ============================================================
           Submit Button
           ============================================================ */
        .btn-submit {
            width: 100%;
            padding: 14px 24px;
            font-size: 16px;
            font-weight: 600;
            font-family: var(--font-family);
            color: white;
            background: var(--color-primary);
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: var(--color-primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-submit .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .btn-submit.loading .spinner {
            display: inline-block;
        }

        .btn-submit.loading .btn-text {
            display: none;
        }

        /* ============================================================
           Sidebar — Summary
           ============================================================ */
        .summary-card {
            position: sticky;
            top: 88px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--color-border);
            font-size: 14px;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: var(--color-text-secondary);
        }

        .summary-value {
            font-weight: 600;
            color: var(--color-text);
        }

        .summary-value.empty {
            color: var(--color-text-muted);
            font-weight: 400;
            font-style: italic;
        }

        .summary-price {
            font-size: 24px;
            font-weight: 800;
            color: var(--color-primary);
        }

        .summary-price .currency {
            font-size: 14px;
            font-weight: 600;
        }

        .summary-actions {
            margin-top: 20px;
        }

        /* ============================================================
           Success / Error States
           ============================================================ */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .alert.visible {
            display: block;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: var(--color-success-light);
            color: var(--color-success);
            border: 1px solid rgba(5, 150, 105, 0.2);
        }

        .alert-error {
            background: var(--color-error-light);
            color: var(--color-error);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .alert-warning {
            background: var(--color-warning-light);
            color: var(--color-warning);
            border: 1px solid rgba(217, 119, 6, 0.2);
        }

        .success-screen {
            text-align: center;
            padding: 48px 24px;
        }

        .success-screen .checkmark {
            width: 72px;
            height: 72px;
            background: var(--color-success-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 32px;
            color: var(--color-success);
        }

        .success-screen h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .success-screen p {
            color: var(--color-text-secondary);
            margin-bottom: 8px;
        }

        .success-screen .confirmation-code {
            display: inline-block;
            font-size: 28px;
            font-weight: 800;
            color: var(--color-primary);
            letter-spacing: 4px;
            background: var(--color-primary-light);
            padding: 12px 28px;
            border-radius: var(--radius-sm);
            margin: 16px 0;
            font-family: 'Courier New', monospace;
        }

        .btn-new-booking {
            display: inline-block;
            margin-top: 24px;
            padding: 12px 28px;
            font-size: 15px;
            font-weight: 600;
            color: var(--color-primary);
            background: var(--color-surface);
            border: 1.5px solid var(--color-primary);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: var(--font-family);
            text-decoration: none;
        }

        .btn-new-booking:hover {
            background: var(--color-primary-light);
        }

        /* ============================================================
           Date input custom styling
           ============================================================ */
        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0.5;
            cursor: pointer;
        }

        input[type="date"]:focus::-webkit-calendar-picker-indicator {
            opacity: 1;
        }

        /* ============================================================
           Footer
           ============================================================ */
        .footer {
            text-align: center;
            padding: 24px;
            font-size: 13px;
            color: var(--color-text-muted);
            border-top: 1px solid var(--color-border);
        }

        /* ============================================================
           Responsive
           ============================================================ */
        @media (max-width: 600px) {
            .card {
                padding: 20px;
            }

            .hero h1 {
                font-size: 28px;
            }

            .hero p {
                font-size: 15px;
            }

            .main {
                padding: 24px 16px 60px;
            }
        }

        /* Hide/show form vs success */
        #form-section { display: block; }
        #success-section { display: none; }
        body.success #form-section { display: none; }
        body.success #success-section { display: block; }
        body.success #header-cta { display: none; }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- Header -->
        <header class="header">
            <div class="header-inner">
                <a href="#" class="logo">
                    <span class="logo-icon">R</span>
                    <?= APP_NAME ?>
                </a>
                <div class="header-cta" id="header-cta">
                    Already booked? <a href="lookup.php">Check status</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main">
            <div class="container">
                <!-- Hero -->
                <div class="hero" id="form-section">
                    <div class="hero-badge">
                        <span class="hero-badge-dot"></span>
                        Online Booking Available
                    </div>
                    <h1>Book Your <span>Reservation</span></h1>
                    <p>Schedule your appointment or book a service in seconds. No account required.</p>
                </div>

                <!-- Success Section -->
                <div id="success-section">
                    <div class="success-screen card" style="max-width: 520px; margin: 0 auto;">
                        <div class="checkmark">✓</div>
                        <h2>Reservation Confirmed!</h2>
                        <p>Your booking has been successfully submitted.</p>
                        <div class="confirmation-code" id="success-code">ABCD1234</div>
                        <p style="font-size: 13px; color: var(--color-text-muted);">
                            Please save this code for reference. A confirmation email will be sent to you.
                        </p>
                        <a href="index.php" class="btn-new-booking">Make Another Reservation</a>
                    </div>
                </div>

                <!-- Form -->
                <div class="form-grid" id="form-section">
                    <!-- Left Column: Form -->
                    <div class="card">
                        <h2 class="card-title">Personal Information</h2>
                        <p class="card-subtitle">Fill in your details below to book a reservation.</p>

                        <div id="form-alerts"></div>

                        <form id="reservation-form" novalidate autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="first_name">First Name <span class="required">*</span></label>
                                    <input type="text" id="first_name" name="first_name" class="form-input"
                                           placeholder="John" required maxlength="100"
                                           autocomplete="given-name">
                                    <div class="form-error" id="first_name-error"></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="last_name">Last Name <span class="required">*</span></label>
                                    <input type="text" id="last_name" name="last_name" class="form-input"
                                           placeholder="Doe" required maxlength="100"
                                           autocomplete="family-name">
                                    <div class="form-error" id="last_name-error"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="email">Email Address <span class="required">*</span></label>
                                    <div class="form-input-icon-wrap">
                                        <span class="form-input-icon">✉</span>
                                        <input type="email" id="email" name="email" class="form-input"
                                               placeholder="john@example.com" required
                                               autocomplete="email">
                                    </div>
                                    <div class="form-error" id="email-error"></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="phone">Phone Number</label>
                                    <div class="form-input-icon-wrap">
                                        <span class="form-input-icon">📞</span>
                                        <input type="tel" id="phone" name="phone" class="form-input"
                                               placeholder="+966 55 123 4567"
                                               autocomplete="tel">
                                    </div>
                                    <div class="form-error" id="phone-error"></div>
                                </div>
                            </div>

                            <hr style="border: none; border-top: 1px solid var(--color-border); margin: 24px 0;">

                            <h2 class="card-title" style="font-size: 18px;">Reservation Details</h2>
                            <p class="card-subtitle">Select your preferred date, time, and service.</p>

                            <?php if (!empty($services)): ?>
                            <div class="form-group">
                                <label class="form-label" for="service_id">Service Type</label>
                                <select id="service_id" name="service_id" class="form-select">
                                    <option value="">— Select a service (optional) —</option>
                                    <?php foreach ($services as $svc): ?>
                                        <option value="<?= $svc['id'] ?>"
                                            data-price="<?= $svc['price'] ?>"
                                            data-duration="<?= $svc['duration_minutes'] ?>"
                                            data-capacity="<?= $svc['max_capacity'] ?>">
                                            <?= htmlspecialchars($svc['name']) ?>
                                            <?= $svc['price'] > 0 ? '— SAR ' . number_format($svc['price'], 2) : '— Free' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="reservation_date">Date <span class="required">*</span></label>
                                    <input type="date" id="reservation_date" name="reservation_date" class="form-input"
                                           required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                    <div class="form-error" id="reservation_date-error"></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="guests">Guests</label>
                                    <select id="guests" name="guests" class="form-select">
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?= $i ?>" <?= $i === 1 ? 'selected' : '' ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Available Time Slots</label>
                                <div id="slots-container">
                                    <div class="slots-loading">
                                        <span class="spinner"></span>
                                        Select a date to see available times
                                    </div>
                                </div>
                                <input type="hidden" name="reservation_time" id="reservation_time">
                                <div class="form-error" id="reservation_time-error"></div>
                            </div>

                            <div class="form-group full">
                                <label class="form-label" for="notes">Special Requests or Notes</label>
                                <textarea id="notes" name="notes" class="form-textarea"
                                          placeholder="Any special requirements, preferences, or information we should know..."
                                          maxlength="500"></textarea>
                            </div>

                            <button type="submit" class="btn-submit" id="submit-btn">
                                <span class="spinner"></span>
                                <span class="btn-text">Confirm Reservation</span>
                            </button>
                        </form>
                    </div>

                    <!-- Right Column: Summary -->
                    <div>
                        <div class="card summary-card">
                            <h2 class="card-title" style="font-size: 18px;">Booking Summary</h2>
                            <p class="card-subtitle">Review your reservation details</p>

                            <div class="summary-item">
                                <span class="summary-label">Date</span>
                                <span class="summary-value empty" id="summary-date">Not selected</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Time</span>
                                <span class="summary-value empty" id="summary-time">Not selected</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Guests</span>
                                <span class="summary-value" id="summary-guests">1</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Service</span>
                                <span class="summary-value empty" id="summary-service">—</span>
                            </div>
                            <div class="summary-item" style="border-bottom: none; padding-top: 16px;">
                                <span class="summary-label">Total</span>
                                <span class="summary-price" id="summary-price">
                                    <span class="currency">SAR</span> 0.00
                                </span>
                            </div>

                            <div class="summary-actions">
                                <div style="font-size: 12px; color: var(--color-text-muted); text-align: center; line-height: 1.6;">
                                    <p>🔒 Your information is secure</p>
                                    <p style="margin-top: 4px;">No credit card required to book</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            &copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.
        </footer>
    </div>

    <script>
        // ============================================================
        // Frontend Logic
        // ============================================================

        (function() {
            'use strict';

            const form = document.getElementById('reservation-form');
            const dateInput = document.getElementById('reservation_date');
            const timeInput = document.getElementById('reservation_time');
            const slotsContainer = document.getElementById('slots-container');
            const submitBtn = document.getElementById('submit-btn');
            const alertsContainer = document.getElementById('form-alerts');
            const guestsSelect = document.getElementById('guests');
            const serviceSelect = document.getElementById('service_id');

            // Summary elements
            const summaryDate = document.getElementById('summary-date');
            const summaryTime = document.getElementById('summary-time');
            const summaryGuests = document.getElementById('summary-guests');
            const summaryService = document.getElementById('summary-service');
            const summaryPrice = document.getElementById('summary-price');

            // ============================================================
            // Date input: Set min and default
            // ============================================================
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minDate = tomorrow.toISOString().split('T')[0];
            dateInput.setAttribute('min', minDate);
            dateInput.value = '';

            // ============================================================
            // Fetch available slots when date changes
            // ============================================================
            dateInput.addEventListener('change', function() {
                const date = this.value;
                if (!date) {
                    slotsContainer.innerHTML = `
                        <div class="slots-loading">
                            <span class="spinner"></span>
                            Select a date to see available times
                        </div>`;
                    updateSummary();
                    return;
                }

                // Show loading
                slotsContainer.innerHTML = `
                    <div class="slots-loading">
                        <span class="spinner"></span>
                        Loading available times...
                    </div>`;

                timeInput.value = '';
                updateSummary();

                // Fetch via AJAX
                fetch('api/slots.php?date=' + encodeURIComponent(date))
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            slotsContainer.innerHTML = `<div class="no-slots-message">${data.error}</div>`;
                            return;
                        }

                        if (!data.slots || data.slots.length === 0) {
                            slotsContainer.innerHTML = `<div class="no-slots-message">${data.message || 'No available slots for this date'}</div>`;
                            return;
                        }

                        let html = '<div class="slots-grid">';
                        data.slots.forEach(slot => {
                            const disabled = !slot.available ? 'disabled' : '';
                            const unavailableClass = !slot.available ? 'slot-unavailable' : '';
                            const remainingText = slot.remaining > 0 ? `<span class="slot-remaining">${slot.remaining} left</span>` : '';
                            html += `
                                <button type="button" class="slot-btn ${unavailableClass}" 
                                        data-time="${slot.time}" ${disabled}
                                        onclick="selectSlot(this, '${slot.time}')">
                                    ${slot.display}
                                    ${remainingText}
                                </button>`;
                        });
                        html += '</div>';
                        slotsContainer.innerHTML = html;
                    })
                    .catch(err => {
                        slotsContainer.innerHTML = `<div class="no-slots-message">Failed to load slots. Please try again.</div>`;
                        console.error('Slot fetch error:', err);
                    });
            });

            // ============================================================
            // Select time slot
            // ============================================================
            window.selectSlot = function(btn, time) {
                // Deselect all
                document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                timeInput.value = time;
                updateSummary();
            };

            // ============================================================
            // Update summary
            // ============================================================
            function updateSummary() {
                const date = dateInput.value;
                const time = timeInput.value;
                const guests = guestsSelect.value;
                const serviceOpt = serviceSelect.options[serviceSelect.selectedIndex];

                summaryDate.textContent = date ? new Date(date + 'T12:00:00').toLocaleDateString('en-US', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                }) : 'Not selected';
                summaryDate.className = date ? 'summary-value' : 'summary-value empty';

                summaryTime.textContent = time ? formatTime(time) : 'Not selected';
                summaryTime.className = time ? 'summary-value' : 'summary-value empty';

                summaryGuests.textContent = guests;

                if (serviceOpt && serviceOpt.value) {
                    summaryService.textContent = serviceOpt.text.split('—')[0].trim();
                    summaryService.className = 'summary-value';
                    const price = parseFloat(serviceOpt.dataset.price || 0);
                    summaryPrice.innerHTML = `<span class="currency">SAR</span> ${price.toFixed(2)}`;
                } else {
                    summaryService.textContent = '—';
                    summaryService.className = 'summary-value empty';
                    summaryPrice.innerHTML = `<span class="currency">SAR</span> 0.00`;
                }
            }

            function formatTime(time24) {
                const [h, m] = time24.split(':');
                const hour = parseInt(h);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const hour12 = hour % 12 || 12;
                return `${hour12}:${m} ${ampm}`;
            }

            // Listen for changes in guests and service
            guestsSelect.addEventListener('change', updateSummary);
            serviceSelect.addEventListener('change', updateSummary);

            // ============================================================
            // Show alert message
            // ============================================================
            function showAlert(type, message, errors) {
                let html = '';
                if (errors) {
                    let list = '';
                    Object.values(errors).forEach(msgs => {
                        if (Array.isArray(msgs)) {
                            msgs.forEach(m => { list += `<div>• ${m}</div>`; });
                        } else {
                            list += `<div>• ${msgs}</div>`;
                        }
                    });
                    html = `<div class="alert alert-${type} visible">${list}</div>`;
                } else {
                    html = `<div class="alert alert-${type} visible">${message}</div>`;
                }
                alertsContainer.innerHTML = html;
                alertsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            function clearAlerts() {
                alertsContainer.innerHTML = '';
                // Clear field errors
                document.querySelectorAll('.form-error').forEach(el => el.classList.remove('visible'));
                document.querySelectorAll('.form-input.error, .form-select.error').forEach(el => el.classList.remove('error'));
            }

            // ============================================================
            // Field-level error display
            // ============================================================
            function showFieldError(field, message) {
                const input = document.querySelector(`[name="${field}"]`);
                const errorEl = document.getElementById(`${field}-error`);
                if (input) input.classList.add('error');
                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.classList.add('visible');
                }
            }

            // ============================================================
            // Form Submission
            // ============================================================
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearAlerts();

                // Client-side validation
                const firstName = document.getElementById('first_name').value.trim();
                const lastName = document.getElementById('last_name').value.trim();
                const email = document.getElementById('email').value.trim();
                const date = dateInput.value;
                const time = timeInput.value;

                let hasError = false;

                if (!firstName) { showFieldError('first_name', 'First name is required'); hasError = true; }
                if (!lastName) { showFieldError('last_name', 'Last name is required'); hasError = true; }
                if (!email) { showFieldError('email', 'Email is required'); hasError = true; }
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showFieldError('email', 'Please enter a valid email'); hasError = true;
                }
                if (!date) { showFieldError('reservation_date', 'Please select a date'); hasError = true; }
                if (!time) {
                    showAlert('error', 'Please select a time slot.');
                    hasError = true;
                }

                if (hasError) return;

                // Submit
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');

                const formData = new FormData(form);

                fetch('api/submit.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');

                    if (data.success) {
                        document.getElementById('success-code').textContent = data.confirmation_code;
                        document.body.classList.add('success');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        if (data.errors) {
                            // Show field-level errors
                            Object.entries(data.errors).forEach(([field, msgs]) => {
                                if (Array.isArray(msgs)) {
                                    showFieldError(field, msgs[0]);
                                } else {
                                    showFieldError(field, msgs);
                                }
                            });
                            showAlert('error', data.message || 'Please correct the errors below.', data.errors);
                        } else {
                            showAlert('error', data.message || 'An error occurred. Please try again.');
                        }
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    showAlert('error', 'Network error. Please check your connection and try again.');
                    console.error('Submit error:', err);
                });
            });

            // ============================================================
            // Clear field errors on input
            // ============================================================
            document.querySelectorAll('.form-input, .form-select').forEach(el => {
                el.addEventListener('input', function() {
                    this.classList.remove('error');
                    const errorEl = document.getElementById(`${this.name}-error`);
                    if (errorEl) errorEl.classList.remove('visible');
                });
            });
        })();
    </script>
</body>
</html>
