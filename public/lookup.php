<?php
/**
 * Reservation Lookup — Check your reservation by confirmation code
 */

require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../src/config/config.php';

use App\Handlers\ReservationHandler;

$lookupResult = null;
$lookupError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['code'])) {
    $code = trim(strtoupper($_POST['code']));
    $reservation = ReservationHandler::getByCode($code);
    if ($reservation) {
        $lookupResult = $reservation;
    } else {
        $lookupError = 'No reservation found with code: ' . htmlspecialchars($code);
    }

    // Handle cancel
    if (!empty($_POST['action']) && $_POST['action'] === 'cancel' && $reservation) {
        if (in_array($reservation['status'], ['pending', 'confirmed'])) {
            $cancelResult = ReservationHandler::cancel($code);
            if ($cancelResult['success']) {
                $reservation['status'] = 'cancelled';
                $lookupResult = $reservation;
                $lookupMessage = 'Your reservation has been cancelled.';
            }
        } else {
            $lookupError = 'This reservation cannot be cancelled (status: ' . $reservation['status'] . ').';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lookup Reservation — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc; color: #0f172a;
            min-height: 100vh; display: flex; flex-direction: column;
        }
        .header {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 16px 24px;
        }
        .header-inner { max-width: 800px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; }
        .logo { font-size: 20px; font-weight: 700; color: #0f172a; text-decoration: none; }
        .logo span { color: #2563eb; }
        .main { flex: 1; padding: 60px 24px; }
        .container { max-width: 600px; margin: 0 auto; }
        h1 { font-size: 32px; font-weight: 800; margin-bottom: 8px; text-align: center; }
        .subtitle { text-align: center; color: #64748b; margin-bottom: 32px; font-size: 16px; }
        .card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 32px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; }
        .input-wrap { display: flex; gap: 8px; }
        input[type="text"] {
            flex: 1; padding: 11px 14px; font-size: 15px; font-family: 'Inter', sans-serif;
            border: 1.5px solid #e2e8f0; border-radius: 8px; outline: none; transition: all 0.2s;
            text-transform: uppercase; letter-spacing: 2px; text-align: center;
        }
        input[type="text"]:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        button {
            padding: 11px 24px; font-size: 15px; font-weight: 600; font-family: 'Inter', sans-serif;
            background: #2563eb; color: #fff; border: none; border-radius: 8px; cursor: pointer;
            transition: all 0.2s; white-space: nowrap;
        }
        button:hover { background: #1d4ed8; }
        .error-message {
            background: #fef2f2; color: #dc2626; padding: 12px 16px; border-radius: 8px;
            font-size: 14px; font-weight: 500; margin-bottom: 20px;
        }
        .success-message {
            background: #ecfdf5; color: #059669; padding: 12px 16px; border-radius: 8px;
            font-size: 14px; font-weight: 500; margin-bottom: 20px;
        }
        .result-card {
            margin-top: 24px;
        }
        .result-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px;
        }
        .status-badge {
            display: inline-block; padding: 4px 12px; border-radius: 100px;
            font-size: 12px; font-weight: 600; text-transform: uppercase;
        }
        .status-pending { background: #fffbeb; color: #d97706; }
        .status-confirmed { background: #ecfdf5; color: #059669; }
        .status-completed { background: #eff6ff; color: #2563eb; }
        .status-cancelled { background: #fef2f2; color: #dc2626; }
        .status-no_show { background: #f1f5f9; color: #64748b; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .info-item { }
        .info-label { font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 500; }
        .info-value { font-size: 16px; font-weight: 600; margin-top: 2px; }
        .info-value.code { font-family: 'Courier New', monospace; color: #2563eb; letter-spacing: 2px; }
        .cancel-form { margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; }
        .cancel-btn {
            background: none; border: 1.5px solid #dc2626; color: #dc2626;
            padding: 10px 24px; font-size: 14px;
        }
        .cancel-btn:hover { background: #dc2626; color: white; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #64748b; font-size: 14px; }
        .back-link a { color: #2563eb; text-decoration: none; font-weight: 500; }
        .back-link a:hover { text-decoration: underline; }
        .footer { text-align: center; padding: 24px; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <a href="index.php" class="logo"><span>Reservation</span>Pro</a>
            <a href="index.php" style="font-size:14px;color:#64748b;text-decoration:none;">← Back to booking</a>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <h1>Check Your Reservation</h1>
            <p class="subtitle">Enter your confirmation code to view or manage your booking.</p>

            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label for="code">Confirmation Code</label>
                        <div class="input-wrap">
                            <input type="text" id="code" name="code" placeholder="e.g. A1B2C3D4" 
                                   maxlength="8" required
                                   value="<?= htmlspecialchars($_POST['code'] ?? '') ?>">
                            <button type="submit">Search</button>
                        </div>
                    </div>
                </form>

                <?php if ($lookupError): ?>
                    <div class="error-message"><?= $lookupError ?></div>
                <?php endif; ?>

                <?php if (!empty($lookupMessage)): ?>
                    <div class="success-message"><?= $lookupMessage ?></div>
                <?php endif; ?>
            </div>

            <?php if ($lookupResult): ?>
            <div class="card result-card">
                <div class="result-header">
                    <h2 style="font-size:18px;font-weight:700;">Reservation Details</h2>
                    <span class="status-badge status-<?= $lookupResult['status'] ?>">
                        <?= str_replace('_', ' ', $lookupResult['status']) ?>
                    </span>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Confirmation Code</div>
                        <div class="info-value code"><?= htmlspecialchars($lookupResult['confirmation_code']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date</div>
                        <div class="info-value"><?= date('F j, Y', strtotime($lookupResult['reservation_date'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Time</div>
                        <div class="info-value"><?= date('h:i A', strtotime($lookupResult['reservation_time'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Guests</div>
                        <div class="info-value"><?= (int)$lookupResult['guests'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Name</div>
                        <div class="info-value"><?= htmlspecialchars($lookupResult['first_name'] . ' ' . $lookupResult['last_name']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value" style="font-weight:400;font-size:14px;"><?= htmlspecialchars($lookupResult['email']) ?></div>
                    </div>
                    <?php if ($lookupResult['service_name']): ?>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">Service</div>
                        <div class="info-value"><?= htmlspecialchars($lookupResult['service_name']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($lookupResult['notes']): ?>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">Notes</div>
                        <div class="info-value" style="font-weight:400;font-size:14px;"><?= nl2br(htmlspecialchars($lookupResult['notes'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (in_array($lookupResult['status'], ['pending', 'confirmed'])): ?>
                <div class="cancel-form">
                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                        <input type="hidden" name="code" value="<?= htmlspecialchars($lookupResult['confirmation_code']) ?>">
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="cancel-btn">Cancel Reservation</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        &copy; <?= date('Y') ?> ReservationPro. All rights reserved.
    </footer>
</body>
</html>
