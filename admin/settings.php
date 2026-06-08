<?php
/**
 * Admin Panel — Settings
 * Manage business settings, services, operating hours, and holidays
 */

session_start();
require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../src/config/config.php';

use App\Config\Database;

// Check auth
if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$messageType = '';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// ============================
// Handle General Settings Save
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_general'])) {
    $updates = [
        'business_name' => trim($_POST['business_name'] ?? ''),
        'business_email' => trim($_POST['business_email'] ?? ''),
        'business_phone' => trim($_POST['business_phone'] ?? ''),
        'max_advance_days' => (int)($_POST['max_advance_days'] ?? 90),
        'min_notice_hours' => (int)($_POST['min_notice_hours'] ?? 2),
        'time_slot_interval' => (int)($_POST['time_slot_interval'] ?? 30),
        'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? 'true' : 'false',
    ];

    // Operating hours
    $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
    $hours = [];
    foreach ($days as $day) {
        $isClosed = isset($_POST["closed_{$day}"]);
        $hours[$day] = [
            'open' => $isClosed ? 'closed' : ($_POST["open_{$day}"] ?? '09:00'),
            'close' => $isClosed ? 'closed' : ($_POST["close_{$day}"] ?? '18:00'),
        ];
    }
    $updates['operating_hours'] = json_encode($hours);

 foreach ($updates as $key => $value) {
        Database::query(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            [$key, $value]
        );
    }

    $message = 'General settings saved successfully!';
    $messageType = 'success';
}

// ============================
// Handle Service Add / Edit / Delete
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service'])) {
        $name = trim($_POST['service_name'] ?? '');
        $desc = trim($_POST['service_description'] ?? '');
        $price = (float)($_POST['service_price'] ?? 0);
        $duration = (int)($_POST['service_duration'] ?? 60);
        $capacity = (int)($_POST['service_capacity'] ?? 1);
        $category = trim($_POST['service_category'] ?? 'General');

        if ($name) {
            Database::query(
                "INSERT INTO services (name, description, category, duration_minutes, max_capacity, price) VALUES (?, ?, ?, ?, ?, ?)",
                [$name, $desc, $category, $duration, $capacity, $price]
            );
            $message = 'Service added successfully!';
            $messageType = 'success';
        }
    }

    if (isset($_POST['edit_service'])) {
        $id = (int)($_POST['service_id'] ?? 0);
        $name = trim($_POST['service_name'] ?? '');
        $desc = trim($_POST['service_description'] ?? '');
        $price = (float)($_POST['service_price'] ?? 0);
        $duration = (int)($_POST['service_duration'] ?? 60);
        $capacity = (int)($_POST['service_capacity'] ?? 1);
        $category = trim($_POST['service_category'] ?? 'General');
        $active = isset($_POST['service_active']) ? 1 : 0;

        if ($id && $name) {
            Database::query(
                "UPDATE services SET name=?, description=?, category=?, duration_minutes=?, max_capacity=?, price=?, is_active=? WHERE id=?",
                [$name, $desc, $category, $duration, $capacity, $price, $active, $id]
            );
            $message = 'Service updated successfully!';
            $messageType = 'success';
        }
    }

    if (isset($_POST['delete_service'])) {
        $id = (int)($_POST['service_id'] ?? 0);
        if ($id) {
            Database::query("DELETE FROM services WHERE id = ?", [$id]);
            $message = 'Service deleted!';
            $messageType = 'success';
        }
    }
}

// ============================
// Handle Holiday / Exception Add / Delete
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_exception'])) {
        $exceptionDate = $_POST['exception_date'] ?? '';
        $reason = trim($_POST['exception_reason'] ?? '');
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;
        $startTime = $_POST['start_time'] ?? null;
        $endTime = $_POST['end_time'] ?? null;
        $serviceId = !empty($_POST['exception_service_id']) ? (int)$_POST['exception_service_id'] : null;

        if ($exceptionDate) {
            Database::query(
                "INSERT INTO availability_exceptions (service_id, exception_date, start_time, end_time, is_available, reason) VALUES (?, ?, ?, ?, ?, ?)",
                [$serviceId, $exceptionDate, $startTime, $endTime, $isAvailable, $reason]
            );
            $message = 'Date exception added!';
            $messageType = 'success';
        }
    }

    if (isset($_POST['delete_exception'])) {
        $exceptionId = (int)($_POST['exception_id'] ?? 0);
        if ($exceptionId) {
            Database::query("DELETE FROM availability_exceptions WHERE id = ?", [$exceptionId]);
            $message = 'Exception removed!';
            $messageType = 'success';
        }
    }
}

// ============================
// Load Data
// ============================

// Load settings from DB
$settings = [];
$rows = Database::query("SELECT * FROM settings")->fetchAll();
foreach ($rows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$businessName = $settings['business_name'] ?? 'Your Business';
$businessEmail = $settings['business_email'] ?? '';
$businessPhone = $settings['business_phone'] ?? '';
$maxAdvanceDays = (int)($settings['max_advance_days'] ?? 90);
$minNoticeHours = (int)($settings['min_notice_hours'] ?? 2);
$slotInterval = (int)($settings['time_slot_interval'] ?? 30);
$emailNotifications = ($settings['enable_email_notifications'] ?? 'true') === 'true';
$operatingHours = json_decode($settings['operating_hours'] ?? '{}', true);

// Load services
$services = Database::query("SELECT * FROM services ORDER BY name ASC")->fetchAll();

// Load exceptions
$exceptions = Database::query(
    "SELECT e.*, s.name as service_name FROM availability_exceptions e
     LEFT JOIN services s ON e.service_id = s.id
     ORDER BY e.exception_date DESC LIMIT 50"
)->fetchAll();

$dayNames = [
    'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
    'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — <?= APP_NAME ?> Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif; background: #0f172a; color: #f8fafc;
            min-height: 100vh;
        }
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0; width: 240px;
            background: #1e293b; border-right: 1px solid #334155; padding: 24px;
            overflow-y: auto;
        }
        .sidebar h2 { font-size: 18px; font-weight: 700; margin-bottom: 24px; }
        .sidebar h2 span { color: #3b82f6; }
        .sidebar .user { font-size: 13px; color: #64748b; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #334155; }
        .nav-item {
            display: block; padding: 10px 12px; border-radius: 8px;
            font-size: 14px; color: #94a3b8; text-decoration: none; margin-bottom: 4px;
            transition: all 0.2s;
        }
        .nav-item:hover, .nav-item.active { background: #334155; color: #f8fafc; }
        .nav-item.active { color: #3b82f6; font-weight: 600; }
        .nav-item.logout { margin-top: 24px; color: #ef4444; }
        .nav-item.logout:hover { background: #7f1d1d; color: #fca5a5; }

        .main { margin-left: 240px; padding: 32px; max-width: 1100px; }

        .page-title { font-size: 24px; font-weight: 700; margin-bottom: 24px; }

        .section {
            background: #1e293b; border: 1px solid #334155; border-radius: 12px;
            padding: 24px; margin-bottom: 24px;
        }
        .section h3 {
            font-size: 16px; font-weight: 600; margin-bottom: 16px;
            padding-bottom: 12px; border-bottom: 1px solid #334155;
        }
        .section h3 small { font-weight: 400; font-size: 13px; color: #64748b; margin-left: 8px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-grid.three { grid-template-columns: 1fr 1fr 1fr; }
        .form-group { margin-bottom: 16px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label {
            display: block; font-size: 13px; font-weight: 500;
            color: #94a3b8; margin-bottom: 6px; text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px 14px; border-radius: 8px;
            background: #0f172a; border: 1px solid #334155; color: #f8fafc;
            font-size: 14px; font-family: 'Inter', sans-serif;
            transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #3b82f6;
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-group .hint { font-size: 12px; color: #64748b; margin-top: 4px; }

        .btn {
            padding: 10px 20px; border-radius: 8px; border: none;
            font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: all 0.2s; display: inline-block;
        }
        .btn-primary { background: #3b82f6; color: #fff; }
        .btn-primary:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: #22c55e; color: #fff; }
        .btn-success:hover { background: #16a34a; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-outline {
            background: transparent; border: 1px solid #334155; color: #94a3b8;
        }
        .btn-outline:hover { border-color: #3b82f6; color: #3b82f6; }

        .alert {
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-success { background: #14532d; border: 1px solid #22c55e; color: #bbf7d0; }
        .alert-error { background: #7f1d1d; border: 1px solid #ef4444; color: #fecaca; }

        .day-row {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 0; border-bottom: 1px solid #1e293b;
        }
        .day-row:last-child { border-bottom: none; }
        .day-name { width: 100px; font-weight: 600; font-size: 14px; }
        .day-toggle { display: flex; align-items: center; gap: 8px; }
        .day-toggle input[type="checkbox"] { width: auto; accent-color: #3b82f6; }
        .day-times { display: flex; align-items: center; gap: 8px; }
        .day-times input[type="time"] { width: 120px; padding: 6px 10px; border-radius: 6px;
            background: #0f172a; border: 1px solid #334155; color: #f8fafc; font-size: 13px; }
        .day-times .separator { color: #64748b; }
        .day-closed { color: #ef4444; font-size: 13px; font-weight: 500; margin-left: 8px; }

        .toggle-switch {
            position: relative; display: inline-block; width: 44px; height: 24px;
        }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background: #334155; border-radius: 24px; transition: 0.3s;
        }
        .toggle-slider:before {
            content: ""; position: absolute; height: 18px; width: 18px;
            left: 3px; bottom: 3px; background: #f8fafc; border-radius: 50%; transition: 0.3s;
        }
        .toggle-switch input:checked + .toggle-slider { background: #22c55e; }
        .toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }

        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #334155; }
        th { color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge {
            display: inline-block; padding: 2px 8px; border-radius: 4px;
            font-size: 11px; font-weight: 600;
        }
        .badge-active { background: #14532d; color: #4ade80; }
        .badge-inactive { background: #7f1d1d; color: #fca5a5; }
        .badge-price { background: #1e3a5f; color: #60a5fa; }

        .status-indicator {
            display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px;
        }
        .status-open { background: #22c55e; }
        .status-closed { background: #ef4444; }

        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #1e293b; border: 1px solid #334155; border-radius: 12px;
            padding: 24px; width: 100%; max-width: 500px; max-height: 80vh; overflow-y: auto;
        }
        .modal h3 { margin-bottom: 16px; border-bottom: 1px solid #334155; padding-bottom: 12px; }
        .modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px; }

        .mt-16 { margin-top: 16px; }
        .flex { display: flex; }
        .gap-8 { gap: 8px; }
        .items-center { align-items: center; }
        .text-right { text-align: right; }
        .text-muted { color: #64748b; }
        .text-sm { font-size: 13px; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; padding: 16px; }
            .form-grid { grid-template-columns: 1fr; }
            .form-grid.three { grid-template-columns: 1fr; }
            .day-row { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2><span>R</span>eservationPro</h2>
    <div class="user">Logged in as <?= htmlspecialchars($_SESSION['admin_username'] ?? 'admin') ?></div>
    <a href="dashboard.php" class="nav-item">📊 Dashboard</a>
    <a href="dashboard.php?date=<?= date('Y-m-d') ?>" class="nav-item">📅 Today's Bookings</a>
    <a href="dashboard.php?status=pending" class="nav-item">⏳ Pending</a>
    <a href="settings.php" class="nav-item active">⚙️ Settings</a>
    <a href="?action=logout" class="nav-item logout">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main">
    <h1 class="page-title">⚙️ Settings</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- SECTION 1: General Business Settings -->
    <!-- ============================================================ -->
    <form method="POST">
        <div class="section">
            <h3>🏢 Business Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Business Name</label>
                    <input type="text" name="business_name" value="<?= htmlspecialchars($businessName) ?>" placeholder="e.g. Saudi Elite Conference">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="business_email" value="<?= htmlspecialchars($businessEmail) ?>" placeholder="info@example.com">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="business_phone" value="<?= htmlspecialchars($businessPhone) ?>" placeholder="+966 55 000 0000">
                </div>
                <div class="form-group three">
            <h3>⏰ Booking Rules</h3>
            <div class="form-grid three" style="margin-top:16px;">
                <div class="form-group">
                    <label>Max Days Ahead</label>
                    <input type="number" name="max_advance_days" value="<?= $maxAdvanceDays ?>" min="1" max="365">
                    <div class="hint">How far in advance can customers book?</div>
                </div>
                <div class="form-group">
                    <label>Min Notice (hours)</label>
                    <input type="number" name="min_notice_hours" value="<?= $minNoticeHours ?>" min="0" max="168">
                    <div class="hint">Must book at least X hours before</div>
                </div>
                <div class="form-group">
                    <label>Slot Interval (min)</label>
                    <input type="number" name="time_slot_interval" value="<?= $slotInterval ?>" min="15" max="120" step="15">
                    <div class="hint">Time between available slots</div>
                </div>
            </div>

            <div class="form-group" style="margin-top:12px;">
                <label style="display:flex;align-items:center;gap:10px;text-transform:none;font-size:14px;">
                    <label class="toggle-switch">
                        <input type="checkbox" name="enable_email_notifications" <?= $emailNotifications ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                    Enable Email Notifications
                </label>
                <div class="hint" style="margin-left:54px;">Send confirmation emails when customers book</div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 2: Operating Hours -->
        <!-- ============================================================ -->
        <div class="section">
            <h3>🕐 Operating Hours <small>Set open/close times for each day of the week</small></h3>
            <?php foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $day): 
                $dayHours = $operatingHours[$day] ?? ['open' => '09:00', 'close' => '18:00'];
                $isClosed = ($dayHours['open'] ?? '') === 'closed';
                $openVal = $isClosed ? '09:00' : ($dayHours['open'] ?? '09:00');
                $closeVal = $isClosed ? '18:00' : ($dayHours['close'] ?? '18:00');
            ?>
            <div class="day-row">
                <div class="day-name">
                    <span class="status-indicator <?= $isClosed ? 'status-closed' : 'status-open' ?>"></span>
                    <?= $dayNames[$day] ?>
                </div>
                <div class="day-toggle">
                    <input type="checkbox" id="closed_<?= $day ?>" name="closed_<?= $day ?>" <?= $isClosed ? 'checked' : '' ?>>
                    <label for="closed_<?= $day ?>" style="font-size:13px;color:#94a3b8;cursor:pointer;">Closed</label>
                </div>
                <div class="day-times" id="times_<?= $day ?>">
                    <input type="time" name="open_<?= $day ?>" value="<?= $openVal ?>">
                    <span class="separator">→</span>
                    <input type="time" name="close_<?= $day ?>" value="<?= $closeVal ?>">
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-right" style="margin-bottom:24px;">
            <button type="submit" name="save_general" class="btn btn-primary">💾 Save All Settings</button>
        </div>
    </form>

    <!-- ============================================================ -->
    <!-- SECTION 3: Services Management -->
    <!-- ============================================================ -->
    <div class="section">
        <div class="flex items-center gap-8" style="margin-bottom:16px;">
            <h3 style="border:none;padding:0;margin:0;flex:1;">📋 Services & Pricing</h3>
            <button class="btn btn-success btn-sm" onclick="document.getElementById('addServiceModal').classList.add('active')">+ Add Service</button>
        </div>

        <?php if (count($services) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Duration</th>
                    <th>Capacity</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $svc): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($svc['name']) ?></strong></td>
                    <td><?= htmlspecialchars($svc['category'] ?? '-') ?></td>
                    <td><?= $svc['duration_minutes'] ?> min</td>
                    <td><?= $svc['max_capacity'] ?> guests</td>
                    <td><span class="badge badge-price">SAR <?= number_format($svc['price'], 2) ?></span></td>
                    <td>
                        <span class="badge <?= $svc['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $svc['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-outline btn-sm" 
                            onclick="editService(<?= $svc['id'] ?>, '<?= htmlspecialchars(addslashes($svc['name'])) ?>', '<?= htmlspecialchars(addslashes($svc['description'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($svc['category'] ?? '')) ?>', <?= $svc['duration_minutes'] ?>, <?= $svc['max_capacity'] ?>, <?= $svc['price'] ?>, <?= $svc['is_active'] ?>)">
                            ✏️ Edit
                        </button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this service?')">
                            <input type="hidden" name="service_id" value="<?= $svc['id'] ?>">
                            <button type="submit" name="delete_service" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted text-sm">No services yet. Add your first service above.</p>
        <?php endif; ?>
    </div>

    <!-- ============================================================ -->
    <!-- SECTION 4: Holidays & Exceptions -->
    <!-- ============================================================ -->
    <div class="section">
        <div class="flex items-center gap-8" style="margin-bottom:16px;">
            <h3 style="border:none;padding:0;margin:0;flex:1;">📅 Holidays & Date Exceptions</h3>
            <button class="btn btn-success btn-sm" onclick="document.getElementById('addExceptionModal').classList.add('active')">+ Add Exception</button>
        </div>

        <?php if (count($exceptions) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Service</th>
                    <th>Time</th>
                    <th>Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exceptions as $exc): ?>
                <tr>
                    <td><?= date('M j, Y', strtotime($exc['exception_date'])) ?></td>
                    <td>
                        <span class="badge <?= $exc['is_available'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $exc['is_available'] ? 'Available' : 'Blocked' ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($exc['service_name'] ?? 'All services') ?></td>
                    <td>
                        <?php if ($exc['start_time'] && $exc['end_time']): ?>
                            <?= substr($exc['start_time'], 0, 5) ?> → <?= substr($exc['end_time'], 0, 5) ?>
                        <?php else: ?>
                            All day
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($exc['reason'] ?? '-') ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Remove this exception?')">
                            <input type="hidden" name="exception_id" value="<?= $exc['id'] ?>">
                            <button type="submit" name="delete_exception" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted text-sm">No exceptions set. Add holidays or special availability here.</p>
        <?php endif; ?>
    </div>

</div>

<!-- ============================================================ -->
<!-- MODAL: Add Service -->
<!-- ============================================================ -->
<div class="modal-overlay" id="addServiceModal" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="modal">
        <h3>➕ Add New Service</h3>
        <form method="POST">
            <div class="form-group">
                <label>Service Name *</label>
                <input type="text" name="service_name" required placeholder="e.g. VIP Room Booking">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="service_description" placeholder="Brief description of this service"></textarea>
            </div>
            <div class="form-grid three">
                <div class="form-group">
                    <label>Category</label>
                    <select name="service_category">
                        <option value="General">General</option>
                        <option value="Consultation">Consultation</option>
                        <option value="Room">Room</option>
                        <option value="VIP">VIP</option>
                        <option value="Service">Service</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Duration (min)</label>
                    <input type="number" name="service_duration" value="60" min="15" step="15">
                </div>
                <div class="form-group">
                    <label>Max Capacity</label>
                    <input type="number" name="service_capacity" value="1" min="1">
                </div>
            </div>
            <div class="form-group">
                <label>Price (SAR)</label>
                <input type="number" name="service_price" value="0.00" step="0.50" min="0">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addServiceModal').classList.remove('active')">Cancel</button>
                <button type="submit" name="add_service" class="btn btn-primary">Add Service</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: Edit Service -->
<!-- ============================================================ -->
<div class="modal-overlay" id="editServiceModal" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="modal">
        <h3>✏️ Edit Service</h3>
        <form method="POST">
            <input type="hidden" name="service_id" id="edit_service_id">
            <div class="form-group">
                <label>Service Name *</label>
                <input type="text" name="service_name" id="edit_service_name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="service_description" id="edit_service_description"></textarea>
            </div>
            <div class="form-grid three">
                <div class="form-group">
                    <label>Category</label>
                    <select name="service_category" id="edit_service_category">
                        <option value="General">General</option>
                        <option value="Consultation">Consultation</option>
                        <option value="Room">Room</option>
                        <option value="VIP">VIP</option>
                        <option value="Service">Service</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Duration (min)</label>
                    <input type="number" name="service_duration" id="edit_service_duration" min="15" step="15">
                </div>
                <div class="form-group">
                    <label>Max Capacity</label>
                    <input type="number" name="service_capacity" id="edit_service_capacity" min="1">
                </div>
            </div>
            <div class="form-group">
                <label>Price (SAR)</label>
                <input type="number" name="service_price" id="edit_service_price" step="0.50" min="0">
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:10px;text-transform:none;font-size:14px;">
                    <label class="toggle-switch">
                        <input type="checkbox" name="service_active" id="edit_service_active" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    Active (visible on booking form)
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editServiceModal').classList.remove('active')">Cancel</button>
                <button type="submit" name="edit_service" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: Add Exception -->
<!-- ============================================================ -->
<div class="modal-overlay" id="addExceptionModal" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="modal">
        <h3>📅 Add Date Exception</h3>
        <form method="POST">
            <div class="form-group">
                <label>Date *</label>
                <input type="date" name="exception_date" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="is_available" onchange="document.getElementById('exception_time_fields').style.display = this.value == '1' ? 'flex' : 'none'">
                    <option value="0">🔴 Block this date (holiday / closed)</option>
                    <option value="1">🟢 Make available (if normally closed)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Apply to specific service (optional)</label>
                <select name="exception_service_id">
                    <option value="">— All services —</option>
                    <?php foreach ($services as $svc): ?>
                    <option value="<?= $svc['id'] ?>"><?= htmlspecialchars($svc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="exception_time_fields" style="display:none;" class="flex gap-8">
                <div class="form-group" style="flex:1;">
                    <label>Start Time</label>
                    <input type="time" name="start_time">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>End Time</label>
                    <input type="time" name="end_time">
                </div>
            </div>
            <div class="form-group">
                <label>Reason</label>
                <input type="text" name="exception_reason" placeholder="e.g. National Holiday, Maintenance">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addExceptionModal').classList.remove('active')">Cancel</button>
                <button type="submit" name="add_exception" class="btn btn-primary">Add Exception</button>
            </div>
        </form>
    </div>
</div>

<script>
function editService(id, name, desc, cat, duration, capacity, price, active) {
    document.getElementById('edit_service_id').value = id;
    document.getElementById('edit_service_name').value = name;
    document.getElementById('edit_service_description').value = desc;
    document.getElementById('edit_service_category').value = cat;
    document.getElementById('edit_service_duration').value = duration;
    document.getElementById('edit_service_capacity').value = capacity;
    document.getElementById('edit_service_price').value = price;
    document.getElementById('edit_service_active').checked = active == 1;
    document.getElementById('editServiceModal').classList.add('active');
}

// Close modals on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
    }
});

// Toggle time fields on closed checkbox
document.querySelectorAll('[id^="closed_"]').forEach(cb => {
    cb.addEventListener('change', function() {
        const day = this.id.replace('closed_', '');
        const timeDiv = document.getElementById('times_' + day);
        const inputs = timeDiv.querySelectorAll('input[type="time"]');
        inputs.forEach(inp => inp.disabled = this.checked);
        timeDiv.style.opacity = this.checked ? '0.4' : '1';
    });
    // Run on load
    cb.dispatchEvent(new Event('change'));
});
</script>

</body>
</html>
