<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$title = 'Pembayaran';
include __DIR__ . '/includes/header.php';

$isAdmin = is_admin();
$user = current_user();

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bill_id'])) {
    $bill_id = (int)$_POST['bill_id'];
    $amount = (int)$_POST['amount'];
    $method = $_POST['method'] ?? 'transfer';
    
    $customer_id = $user['customer_id'];
    if ($isAdmin) {
        $customer_id = $_POST['customer_id'] ?? null;
    }
    
    if ($customer_id && $amount > 0) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO payments (bill_id, customer_id, amount, method) VALUES (?, ?, ?, ?)")->execute([$bill_id, $customer_id, $amount, $method]);
            $pdo->prepare("UPDATE bills SET status = 'paid' WHERE id = ?")->execute([$bill_id]);
            $pdo->commit();
            set_flash('success', 'Pembayaran berhasil diproses');
        } catch (Exception $e) {
            $pdo->rollback();
            set_flash('danger', 'Gagal: ' . $e->getMessage());
        }
    } else {
        set_flash('danger', 'Jumlah pembayaran tidak valid');
    }
    header('Location: ' . BASE_URL . '/payments.php' . ($customerId ? '?customer=' . $customerId : ''));
    exit;
}

// Get payments
if ($isAdmin) {
    $customerFilter = $_GET['customer'] ?? null;
    $stmt = $pdo->prepare("
        SELECT p.*, b.period, c.name as customer_name, c.meter_number 
        FROM payments p 
        JOIN bills b ON p.bill_id = b.id 
        JOIN customers c ON p.customer_id = c.id
        " . ($customerFilter ? "WHERE c.id = ?" : "") . "
        ORDER BY p.payment_date DESC
    ");
    $stmt->execute($customerFilter ? [$customerFilter] : []);
} else {
    $stmt = $pdo->prepare("
        SELECT p.*, b.period, c.name as customer_name 
        FROM payments p 
        JOIN bills b ON p.bill_id = b.id 
        JOIN customers c ON p.customer_id = c.id
        WHERE p.customer_id = ?
        ORDER BY p.payment_date DESC
    ");
    $stmt->execute([$user['customer_id']]);
}
$payments = $stmt->fetchAll();

$totalPayments = count($payments);
$totalAmount = 0;
foreach ($payments as $p) $totalAmount += $p['amount'];

// Get bills for payment (pending)
if ($isAdmin) {
    $stmt = $pdo->query("SELECT b.*, c.name as customer_name, c.meter_number FROM bills b JOIN customers c ON b.customer_id = c.id WHERE status = 'unpaid' ORDER BY b.created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT b.*, c.name as customer_name FROM bills b JOIN customers c ON b.customer_id = c.id WHERE b.customer_id = ? AND status = 'unpaid' ORDER BY b.created_at DESC");
    $stmt->execute([$user['customer_id']]);
}
$pendingBills = $stmt->fetchAll();

if ($isAdmin) {
    $stmt = $pdo->query("SELECT id, name FROM customers ORDER BY name");
    $customers = $stmt->fetchAll();
}
?>

<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Pembayaran</h2>
        <a href="<?= BASE_URL ?>/<?= $isAdmin ? 'admin' : 'customer' ?>/index.php" class="btn btn-outline-primary btn-ripple">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>
    
    <?php if ($isAdmin && $customers): ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <form method="GET" class="d-flex">
                <select name="customer" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Pelanggan</option>
                    <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (isset($_GET['customer']) && $_GET['customer'] == $c['id']) ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row g-4 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="stat-card scrollFadeIn" data-stagger="1">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #34D399);"><i class="bi bi-cash-coin"></i></div>
                    <div>
                        <div class="stat-value"><?= rupiah($totalAmount) ?></div>
                        <div class="stat-label">Total Diterima</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card scrollFadeIn" data-stagger="2">
                <div class="d-flex align-items-center">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #6366F1, #4F46E5);"><i class="bi bi-receipt"></i></div>
                    <div>
                        <div class="stat-value"><?= $totalPayments ?></div>
                        <div class="stat-label">Transaksi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($pendingBills): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0"><i class="bi bi-exclamation-diamond me-2"></i>Pelanggan Perlu Dibayar</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Periode</th>
                            <th>Total</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingBills as $bill): ?>
                        <tr class="table-row">
                            <td><strong><?= esc($bill['customer_name']) ?></strong><br><small><code><?= esc($bill['meter_number']) ?></code></small></td>
                            <td><?= bulan_indo($bill['period']) ?></td>
                            <td class="fw-bold"><?= rupiah($bill['total']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary btn-ripple" data-bs-toggle="modal" data-bs-target="paymentModal<?= $bill['id'] ?>">
                                    <i class="bi bi-cash-coin"></i> Bayar
                                </button>
                                
                                <div class="modal fade" id="paymentModal<?= $bill['id'] ?>" tabindex="-1">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Pembayaran Tagihan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                                            <?php if (!$isAdmin): ?>
                                            <input type="hidden" name="customer_id" value="<?= $user['customer_id'] ?>">
                                            <?php endif; ?>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Jumlah Bayar</label>
                                                    <input type="number" name="amount" class="form-control" value="<?= $bill['total'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Metode Pembayaran</label>
                                                    <select name="method" class="form-select">
                                                        <option value="transfer">Transfer Bank</option>
                                                        <option value="cash">Cash</option>
                                                        <option value="ewallet">E-Wallet</option>
                                                    </select>
                                                </div>
                                                <div class="alert alert-info">
                                                    Tagihan: <?= rupiah($bill['total']) ?><br>
                                                    Periode: <?= bulman_indo($bill['period']) ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary btn-ripple">Konfirmasi Pembayaran</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Histori Pembayaran (<?= $totalPayments ?> transaksi)</h5>
        </div>
        <div class="card-body">
            <?php if ($payments): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Periode</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                        <tr class="table-row">
                            <td><?= date('d/m/Y H:i', strtotime($p['payment_date'])) ?></td>
                            <td><?= esc($p['customer_name']) ?></td>
                            <td><?= bulan_indo($p['period']) ?></td>
                            <td class="fw-bold text-success"><?= rupiah($p['amount']) ?></td>
                            <td>
                                <?php if ($p['method'] === 'transfer'): ?>
                                    <span class="badge badge-unpaid">Transfer</span>
                                <?php elseif ($p['method'] === 'cash'): ?>
                                    <span class="badge badge-completed">Cash</span>
                                <?php else: ?>
                                    <span class="badge">E-Wallet</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-center text-muted py-4">Belum ada transaksi pembayaran</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>