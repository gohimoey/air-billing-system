<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_admin();

$title = 'Input Meter';
include __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)$_POST['customer_id'];
    $reading_date = $_POST['reading_date'];
    $reading_value = (int)$_POST['reading_value'];
    
    $stmt = $pdo->prepare("SELECT * FROM meter_readings WHERE customer_id = ? ORDER BY reading_date DESC LIMIT 1");
    $stmt->execute([$customer_id]);
    $last = $stmt->fetch();
    $prev_reading = $last ? $last['reading_value'] : 0;
    
    $usage = max(0, $reading_value - $prev_reading);
    $total = $usage * TARIFF;
    
    try {
        $pdo->prepare("INSERT INTO meter_readings (customer_id, reading_date, reading_value) VALUES (?, ?, ?)")->execute([$customer_id, $reading_date, $reading_value]);
        $pdo->prepare("INSERT INTO bills (customer_id, period, previous_reading, current_reading, usage_m3, rate, total, status) VALUES (?, DATE_FORMAT(?, '%Y-%m'), ?, ?, ?, ?, ?, 'unpaid')")->execute([$customer_id, $reading_date, $prev_reading, $reading_value, $usage, TARIFF, $total]);
        set_flash('success', 'Meter ditambahkan & tagihan dibuat');
    } catch (Exception $e) {
        set_flash('danger', 'Gagal: ' . $e->getMessage());
    }
    header('Location: ' . BASE_URL . '/readings.php');
    exit;
}

// Get all customers
$stmt = $pdo->query("SELECT c.*, u.username FROM customers c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.name");
$customers = $stmt->fetchAll();

// Get recent readings
$stmt = $pdo->query("
    SELECT mr.*, c.name, c.meter_number 
    FROM meter_readings mr 
    JOIN customers c ON mr.customer_id = c.id 
    ORDER BY mr.reading_date DESC, mr.id DESC 
    LIMIT 20
");
$readings = $stmt->fetchAll();
?>

<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-speedometer me-2"></i>Input Meter & Generate Tagihan</h2>
        <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-primary btn-ripple"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Input Pembacaan Meter</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Pelanggan</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">Pilih Pelanggan</option>
                                <?php foreach ($customers as $c): ?>
                                <option value="<?= $c['id'] ?>" data-prev="<?= $c['last_reading'] ?? 0 ?>">
                                    <?= esc($c['name']) ?> (<?= esc($c['meter_number']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Baca</label>
                            <input type="date" name="reading_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nilai Meter (m³)</label>
                            <input type="number" name="reading_value" class="form-control" required min="0">
                            <div class="form-text">Masukkan nilai meter terbaru</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-ripple w-100"><i class="bi bi-plus-circle me-1"></i>Simpan & Buat Tagihan</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Pembacaan</h5>
                    <a href="<?= BASE_URL ?>/bills.php" class="btn btn-sm btn-outline-primary">Lihat Tagihan</a>
                </div>
                <div class="card-body">
                    <?php if ($readings): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Meter</th>
                                    <th>Nilai Baca</th>
                                    <th>Penggunaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($readings as $r): ?>
                                <tr class="table-row">
                                    <td><?= date('d/m/Y', strtotime($r['reading_date'])) ?></td>
                                    <td><?= esc($r['name']) ?></td>
                                    <td><code><?= esc($r['meter_number']) ?></code></td>
                                    <td><?= $r['reading_value'] ?> m³</td>
                                    <td><?= $r['usage_m3'] ?? 0 ?> m³</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-center text-muted py-4">Belum ada pembacaan meter</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>