<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$title = 'Dashboard Admin';
include __DIR__ . '/../includes/header.php';

// Statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM customers");
$totalCustomers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM bills WHERE status = 'unpaid'");
$pendingBills = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(total) FROM bills WHERE status = 'unpaid'");
$outstandingAmount = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$monthlyPayments = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$monthlyRevenue = $stmt->fetchColumn() ?? 0;

// Recent bills
$stmt = $pdo->query("
    SELECT b.*, c.name as customer_name, c.meter_number 
    FROM bills b 
    JOIN customers c ON b.customer_id = c.id 
    ORDER BY b.created_at DESC 
    LIMIT 10
");
$recentBills = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-3 text-primary">Dashboard Admin</h2>
        <p class="text-muted">Halo, <strong><?= esc($u['name']) ?></strong>. Ringkasan bulan ini:</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card scrollFadeIn" data-stagger="1">
            <div class="d-flex align-items-center">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="stat-value"><?= $totalCustomers ?></div>
                    <div class="stat-label">Total Pelanggan</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card scrollFadeIn" data-stagger="2">
            <div class="d-flex align-items-center">
                <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #FDBB36);"><i class="bi bi-clock-history"></i></div>
                <div>
                    <div class="stat-value"><?= $pendingBills ?></div>
                    <div class="stat-label">Tagihan Pending</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card scrollFadeIn" data-stagger="3">
            <div class="d-flex align-items-center">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #34D399);"><i class="bi bi-cash-coin"></i></div>
                <div>
                    <div class="stat-value">Rp <?= number_format($outstandingAmount, 0, ',', '.') ?></div>
                    <div class="stat-label">Total Tagihan</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stat-card scrollFadeIn" data-stagger="4">
            <div class="d-flex align-items-center">
                <div class="stat-icon" style="background: linear-gradient(135deg, #8B5CF6, #A78BFA);"><i class="bi bi-graph-up"></i></div>
                <div>
                    <div class="stat-value">Rp <?= number_format($monthlyRevenue, 0, ',', '.') ?></div>
                    <div class="stat-label">Pemasukan 30hri</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <a href="<?= BASE_URL ?>/admin/customers.php" class="btn btn-primary me-2 btn-ripple"><i class="bi bi-people me-1"></i>Pelanggan</a>
        <a href="<?= BASE_URL ?>/bills.php" class="btn btn-outline-primary me-2 btn-ripple"><i class="bi bi-receipt me-1"></i>Tagihan</a>
        <a href="<?= BASE_URL ?>/readings.php" class="btn btn-outline-secondary btn-ripple"><i class="bi bi-speedometer me-1"></i>Meter</a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Tagihan Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th><th>Pelanggan</th><th>Meter</th><th>Jumlah</th><th>Status</th><th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBills as $bill): ?>
                            <tr class="table-row">
                                <td><?= $bill['id'] ?></td>
                                <td><?= esc($bill['customer_name']) ?></td>
                                <td><code><?= esc($bill['meter_number']) ?></code></td>
                                <td>Rp <?= number_format($bill['total'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if ($bill['status'] == 'unpaid'): ?>
                                        <span class="badge badge-unpaid">Pending</span>
                                    <?php else: ?>
                                        <span class="badge badge-completed">Lunas</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/payments.php?bill_id=<?= $bill['id'] ?>" class="btn btn-sm btn-primary btn-ripple">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>