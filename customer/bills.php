<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$title = 'Tagihan Saya';
include __DIR__ . '/../includes/header.php';

$user = current_user();
$customerId = $user['customer_id'] ?? null;

if (!$customerId) {
    set_flash('danger', 'Akun pelanggan belum terdaftar');
    header('Location: ' . BASE_URL . '/customer/profile.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM bills WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$customerId]);
$bills = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

$paidCount = 0; $unpaidCount = 0; $totalPaid = 0; $totalUnpaid = 0;
foreach ($bills as $b) {
    if ($b['status'] === 'paid') { $paidCount++; $totalPaid += $b['total']; }
    else { $unpaidCount++; $totalUnpaid += $b['total']; }
}
?>

<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-receipt me-2"></i>Tagihan Saya</h2>
        <a href="<?= BASE_URL ?>/customer/index.php" class="btn btn-outline-primary btn-ripple">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Info Pelanggan</h5>
                </div>
                <div class="card-body text-center">
                    <i class="bi bi-person-circle bi-2x text-primary mb-2"></i>
                    <p><strong>Nama:</strong> <?= esc($customer['name']) ?></p>
                    <p><strong>No. Meter:</strong> <code><?= esc($customer['meter_number']) ?></code></p>
                    <p><strong>Alamat:</strong> <br><small><?= esc($customer['address']) ?></small></p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Statistik Tagihan</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="stat-card mb-3">
                                <div class="stat-value"><strong><?= $paidCount ?></strong></div>
                                <div class="stat-label">Lunas</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-card mb-3">
                                <div class="stat-value text-warning"><strong><?= $unpaidCount ?></strong></div>
                                <div class="stat-label">Belum Bayar</div>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col">
                            <div class="stat-card mb-3">
                                <div class="stat-value text-success"><strong><?= rupiah($totalPaid) ?></strong></div>
                                <div class="stat-label">Total Dibayar</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-card mb-3">
                                <div class="stat-value text-danger"><strong><?= rupiah($totalUnpaid) ?></strong></div>
                                <div class="stat-label">Total Piutang</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Daftar Tagihan (<?= count($bills) ?> tagihan)</h5>
        </div>
        <div class="card-body">
            <?php if ($bills): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Periode</th>
                            <th>Meter Awal</th>
                            <th>Meter Akhir</th>
                            <th>Penggunaan</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $bill): ?>
                        <tr class="table-row">
                            <td><?= date('d/m/Y', strtotime($bill['created_at'])) ?></td>
                            <td><strong><?= bulan_indo($bill['period']) ?></strong></td>
                            <td><?= $bill['previous_reading'] ?> m³</td>
                            <td><?= $bill['current_reading'] ?> m³</td>
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
            <p class="text-center text-muted py-4">Belum ada tagihan</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>