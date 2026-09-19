<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$user = current_user();
$isAdmin = $user['role'] === 'admin';
$customerId = $isAdmin ? null : ($user['customer_id'] ?? null);

$title = $isAdmin ? 'Manajemen Tagihan' : 'Tagihan Saya';
include __DIR__ . '/includes/header.php';

// Handle status update (admin only)
if ($isAdmin && isset($_GET['pay'])) {
    $billId = (int)$_GET['pay'];
    $pdo->prepare("UPDATE bills SET status = 'paid' WHERE id = ?")->execute([$billId]);
    set_flash('success', 'Tagihan ditandai lunas');
    header('Location: ' . BASE_URL . '/bills.php');
    exit;
}

if ($isAdmin && isset($_GET['unpay'])) {
    $billId = (int)$_GET['unpay'];
    $pdo->prepare("UPDATE bills SET status = 'unpaid' WHERE id = ?")->execute([$billId]);
    set_flash('success', 'Status tagihan diubah');
    header('Location: ' . BASE_URL . '/bills.php');
    exit;
}

// Filters
$statusFilter = $isAdmin ? ($_GET['status'] ?? 'all') : 'all';
$searchQuery = $isAdmin ? ($_GET['search'] ?? '') : '';

$whereClause = '';
$params = [];

if ($isAdmin) {
    if ($statusFilter !== 'all') {
        $whereClause = "WHERE b.status = ?";
        $params[] = $statusFilter;
    }
    if (!empty($searchQuery)) {
        $whereClause .= $whereClause ? " AND " : "WHERE ";
        $whereClause .= "(c.name LIKE ? OR c.meter_number LIKE ? OR b.period LIKE ?)";
        $params = array_merge($params, ["%$searchQuery%", "%$searchQuery%", "%$searchQuery%"]);
    }
} else {
    $whereClause = "WHERE b.customer_id = ?";
    $params[] = $customerId;
}

$stmt = $pdo->prepare("
    SELECT b.id, b.period, b.previous_reading, b.current_reading, b.usage_m3, b.total, b.status, b.created_at,
           c.name as customer_name, c.meter_number
    FROM bills b
    JOIN customers c ON b.customer_id = c.id
    $whereClause
    ORDER BY b.created_at DESC
");
$stmt->execute($params);
$bills = $stmt->fetchAll();

if ($isAdmin) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM bills WHERE status = 'unpaid'");
    $unpaidCount = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM bills WHERE status = 'paid'");
    $paidCount = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM bills WHERE status = 'paid'");
    $paidTotal = $stmt->fetchColumn();
} else {
    $unpaidCount = 0; $paidCount = 0; $paidTotal = 0;
    $totalUnpaid = 0;
    foreach ($bills as $b) {
        if ($b['status'] === 'paid') { $paidCount++; $paidTotal += $b['total']; }
        else { $unpaidCount++; $totalUnpaid += $b['total']; }
    }
}
?>

<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-receipt me-2"></i><?= $isAdmin ? 'Manajemen Tagihan' : 'Tagihan Saya' ?></h2>
        <a href="<?= BASE_URL ?>/<?= $isAdmin ? 'admin' : 'customer' ?>/index.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>
    
    <?php if ($isAdmin): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari pelanggan..." value="<?= esc($searchQuery) ?>">
            </form>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Semua Status</option>
                <option value="unpaid" <?= $statusFilter === 'unpaid' ? 'selected' : '' ?>>Belum Bayar</option>
                <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : '' ?>>Lunas</option>
            </select>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Memuat <?= count($bills) ?> tagihan</h5>
            <?php if ($isAdmin): ?>
            <a href="<?= BASE_URL ?>/bills.php?status=unpaid" class="btn btn-sm btn-warning btn-ripple">
                <i class="bi bi-exclamation-diamond"></i> Pending (<?= $unpaidCount ?>)
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if ($bills): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <?php if ($isAdmin): ?>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Meter</th>
                            <?php endif; ?>
                            <th>Periode</th>
                            <?php if (!$isAdmin): ?>
                            <th>Meter Awal</th>
                            <th>Meter Akhir</th>
                            <?php endif; ?>
                            <th>Penggunaan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <?php if ($isAdmin): ?>
                            <th width="150">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $bill): ?>
                        <tr class="table-row">
                            <?php if ($isAdmin): ?>
                            <td><?= date('d/m/Y', strtotime($bill['created_at'])) ?></td>
                            <td><strong><?= esc($bill['customer_name']) ?></strong></td>
                            <td><code><?= esc($bill['meter_number']) ?></code></td>
                            <?php endif; ?>
                            <td><strong><?= bulan_indo($bill['period']) ?></strong></td>
                            <?php if (!$isAdmin): ?>
                            <td><?= $bill['previous_reading'] ?> m³</td>
                            <td><?= $bill['current_reading'] ?> m³</td>
                            <?php endif; ?>
                            <td><?= $bill['usage_m3'] ?> m³</td>
                            <td class="fw-bold"><?= rupiah($bill['total']) ?></td>
                            <td>
                                <?php if ($bill['status'] === 'paid'): ?>
                                    <span class="badge badge-completed">Lunas</span>
                                <?php else: ?>
                                    <span class="badge badge-unpaid">Pending</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($isAdmin): ?>
                            <td>
                                <?php if ($bill['status'] === 'unpaid'): ?>
                                    <a href="<?= BASE_URL ?>/bills.php?pay=<?= $bill['id'] ?>" class="btn btn-sm btn-success btn-ripple" onclick="return confirm('Tandai lunas?')">
                                        <i class="bi bi-check-circle"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/bills.php?unpay=<?= $bill['id'] ?>" class="btn btn-sm btn-outline-warning btn-ripple" onclick="return confirm('Kembalikan pending?')">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/payments.php?bill=<?= $bill['id'] ?>" class="btn btn-sm btn-primary btn-ripple">
                                    <i class="bi bi-cash-coin"></i>
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-center text-muted py-4"><i class="bi bi-info-circle fs-1 mb-2"></i><br>Tidak ada tagihan</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>