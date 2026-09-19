<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!current_user() || is_admin()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$title = 'Dashboard Pelanggan';
include __DIR__ . '/../includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ?");
$stmt->execute([current_user()['id']]);
$customer = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM bills WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$customer['id']]);
$bills = $stmt->fetchAll();

$totalBills = count($bills);
$paidBills = 0; $unpaidBills = 0; $totalUnpaid = 0;
foreach ($bills as $bill) {
    if ($bill['status'] === 'paid') $paidBills++;
    else { $unpaidBills++; $totalUnpaid += $bill['total']; }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-3 text-primary">Dashboard Pelanggan</h2>
        <p class="text-muted">Halo, <strong><?= esc(current_user()['name']) ?></strong>.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="stat-card scrollFadeIn" data-stagger="1">
            <div class="d-flex align-items-center">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #34D399);"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="stat-value"><?= $totalBills ?></div>
                    <div class="stat-label">Total Tagihan</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="stat-card scrollFadeIn" data-stagger="2">
            <div class="d-flex align-items-center">
                <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #FDBB36);"><i class="bi bi-clock-history"></i></div>
                <div>
                    <div class="stat-value text-warning"><?= $unpaidBills ?></div>
                    <div class="stat-label">Belum Bayar</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="stat-card scrollFadeIn" data-stagger="3">
            <div class="d-flex align-items-center">
                <div class="stat-icon" style="background: linear-gradient(135deg, #34D37A, #22C55E);"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="stat-value text-success"><?= $paidBills ?></div>
                    <div class="stat-label">Lunas</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <a href="<?= BASE_URL ?>/customer/bills.php" class="btn btn-primary me-2 btn-ripple"><i class="bi bi-file-earmark-text me-1"></i>Tagihan</a>
        <a href="<?= BASE_URL ?>/customer/profile.php" class="btn btn-outline-primary btn-ripple"><i class="bi bi-person me-1"></i>Profil</a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Tagihan Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if ($bills): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Periode</th><th>Penggunaan</th><th>Total</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($bills, 0, 5) as $bill): ?>
                            <tr class="table-row">
                                <td><strong><?= bulan_indo($bill['period']) ?></strong></td>
                                <td><?= $bill['usage_m3'] ?> m³</td>
                                <td class="fw-bold"><?= rupiah($bill['total']) ?></td>
                                <td>
                                    <?php if ($bill['status'] === 'paid'): ?>
                                        <span class="badge badge-completed">Lunas</span>
                                    <?php else: ?>
                                        <span class="badge badge-unpaid">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-center text-muted py-4"><i class="bi bi-info-circle fs-1 mb-2"></i><br>Belum ada tagihan</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>