<?php
/**
 * Admin Panel — Dashboard
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

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['update_status'])) {
    $allowedStatuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];
    $newStatus = $_POST['new_status'] ?? '';
    $reservationId = (int)($_POST['reservation_id'] ?? 0);

    if (in_array($newStatus, $allowedStatuses) && $reservationId > 0) {
        Database::query(
            "UPDATE reservations SET status = ? WHERE id = ?",
            [$newStatus, $reservationId]
        );
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$searchQuery = $_GET['q'] ?? '';

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];

if ($statusFilter) {
    $where[] = "r.status = ?";
    $params[] = $statusFilter;
}

if ($dateFilter && $dateFilter !== '') {
    $where[] = "r.reservation_date = ?";
    $params[] = $dateFilter;
}

if ($searchQuery) {
    $where[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR r.confirmation_code LIKE ?)";
    $searchTerm = "%{$searchQuery}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$totalCount = Database::query(
    "SELECT COUNT(*) FROM reservations r 
     JOIN customers c ON r.customer_id = c.id 
     {$whereClause}",
    $params
)->fetchColumn();

$totalPages = ceil($totalCount / $perPage);

// Get reservations
$reservations = Database::query(
    "SELECT r.*, c.first_name, c.last_name, c.email, c.phone, s.name AS service_name
     FROM reservations r
     JOIN customers c ON r.customer_id = c.id
     LEFT JOIN services s ON r.service_id = s.id
     {$whereClause}
     ORDER BY r.created_at DESC
     LIMIT ? OFFSET ?",
    array_merge($params, [$perPage, $offset])
)->fetchAll();

// Stats for quick overview
$stats = Database::query(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN DATE(reservation_date) = CURDATE() THEN 1 ELSE 0 END) as today
     FROM reservations"
)->fetch();

$todayCount = Database::query(
    "SELECT COUNT(*) FROM reservations WHERE reservation_date = CURDATE() AND status NOT IN ('cancelled', 'no_show')"
)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?= APP_NAME ?> Admin</title>
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

        .main { margin-left: 240px; padding: 32px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .stat-card {
            background: #1e293b; border: 1px solid #334155; border-radius: 12px;
            padding: 20px; text-align: center;
        }
        .stat-number { font-size: 28px; font-weight: 800; }
        .stat-label { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; }
        .stat-today .stat-number { color: #3b82f6; }
        .stat-pending .stat-number { color: #f59e0b; }
        .stat-confirmed .stat-number { color: #10b981; }
        .stat-cancelled .stat-number { color: #ef4444; }

        .filters { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
        .filters input, .filters select {
            padding: 8px 12px; font-size: 14px; font-family: 'Inter', sans-serif;
            background: #1e293b; border: 1.5px solid #334155; border-radius: 8px;
            color: #f8fafc; outline: none;
        }
        .filters input:focus, .filters select:focus { border-color: #3b82f6; }
        .filters button {
            padding: 8px 16px; font-size: 13px; font-weight: 600; font-family: 'Inter', sans-serif;
            background: #3b82f6; color: #fff; border: none; border-radius: 8px; cursor: pointer;
            transition: all 0.2s;
        }
        .filters button:hover { background: #2563eb; }

        table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 12px; overflow: hidden; }
        th { text-align: left; padding: 12px 16px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #334155; background: #0f172a; }
        td { padding: 12px 16px; font-size: 14px; border-bottom: 1px solid #1e293b; }
        tr:hover td { background: #1a2332; }
        .status-selector {
            padding: 4px 8px; font-size: 12px; font-family: 'Inter', sans-serif;
            background: #0f172a; border: 1px solid #334155; border-radius: 6px;
            color: #f8fafc; cursor: pointer;
        }
        .btn-small {
            padding: 4px 10px; font-size: 11px; font-weight: 600; font-family: 'Inter', sans-serif;
            background: #3b82f6; color: #fff; border: none; border-radius: 6px; cursor: pointer;
        }
        .btn-small:hover { background: #2563eb; }

        .pagination { display: flex; justify-content: center; gap: 4px; margin-top: 24px; }
        .pagination a {
            padding: 8px 14px; border-radius: 8px; font-size: 14px;
            color: #94a3b8; text-decoration: none; transition: all 0.2s;
        }
        .pagination a:hover { background: #1e293b; color: #f8fafc; }
        .pagination a.active { background: #3b82f6; color: #fff; }

        .code-cell { font-family: 'Courier New', monospace; color: #60a5fa; letter-spacing: 1px; font-size: 12px; }
        .name-cell { font-weight: 500; }
        .empty-state { text-align: center; padding: 60px; color: #64748b; }

        @media (max-width: 768px) {
            .sidebar { position: static; width: 100%; padding: 16px; }
            .main { margin-left: 0; padding: 16px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            table { font-size: 13px; }
            th, td { padding: 8px 10px; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><span>Reservation</span>Pro</h2>
        <div class="user">Logged in as <?= htmlspecialchars($_SESSION['admin_username']) ?></div>
        <a href="dashboard.php" class="nav-item active">📊 Dashboard</a>
        <a href="dashboard.php?date=<?= date('Y-m-d') ?>" class="nav-item">📅 Today's Bookings</a>
        <a href="dashboard.php?status=pending" class="nav-item">⏳ Pending</a>
        <a href="dashboard.php" class="nav-item">⚙️ Settings</a>
        <a href="?action=logout" class="nav-item logout">🚪 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <h1 style="font-size:24px;font-weight:800;margin-bottom:24px;">Dashboard</h1>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card stat-today">
                <div class="stat-number"><?= number_format($todayCount) ?></div>
                <div class="stat-label">Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total'] ?? 0) ?></div>
                <div class="stat-label">Total Reservations</div>
            </div>
            <div class="stat-card stat-pending">
                <div class="stat-number"><?= number_format($stats['pending'] ?? 0) ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card stat-confirmed">
                <div class="stat-number"><?= number_format($stats['confirmed'] ?? 0) ?></div>
                <div class="stat-label">Confirmed</div>
            </div>
            <div class="stat-card stat-cancelled">
                <div class="stat-number"><?= number_format($stats['cancelled'] ?? 0) ?></div>
                <div class="stat-label">Cancelled</div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="filters">
            <input type="text" name="q" placeholder="Search name, email, code..." 
                   value="<?= htmlspecialchars($searchQuery) ?>">
            <input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>">
            <select name="status">
                <option value="">All Status</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                <option value="no_show" <?= $statusFilter === 'no_show' ? 'selected' : '' ?>>No Show</option>
            </select>
            <button type="submit">Filter</button>
        </form>

        <!-- Table -->
        <?php if (count($reservations) > 0): ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Guests</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td class="code-cell"><?= htmlspecialchars($r['confirmation_code']) ?></td>
                        <td>
                            <div class="name-cell"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></div>
                            <div style="font-size:12px;color:#64748b;"><?= htmlspecialchars($r['email']) ?></div>
                        </td>
                        <td><?= date('M j, Y', strtotime($r['reservation_date'])) ?></td>
                        <td><?= date('h:i A', strtotime($r['reservation_time'])) ?></td>
                        <td><?= (int)$r['guests'] ?></td>
                        <td style="font-size:13px;color:#94a3b8;"><?= htmlspecialchars($r['service_name'] ?? '—') ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                                <select name="new_status" class="status-selector" onchange="this.form.submit()">
                                    <?php
                                    $statuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];
                                    foreach ($statuses as $s):
                                    ?>
                                        <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>>
                                            <?= ucfirst(str_replace('_', ' ', $s)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;" 
                                  onsubmit="return confirm('Mark as completed?')">
                                <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                                <input type="hidden" name="new_status" value="completed">
                                <input type="hidden" name="update_status" value="1">
                                <button type="submit" class="btn-small">✓ Done</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&status=<?= urlencode($statusFilter) ?>&date=<?= urlencode($dateFilter) ?>&q=<?= urlencode($searchQuery) ?>"
                   class="<?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <div style="font-size:48px;margin-bottom:12px;">📋</div>
                <p>No reservations found</p>
                <p style="font-size:13px;margin-top:4px;">Try adjusting your filters or check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
