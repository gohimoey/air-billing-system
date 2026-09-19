<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();

$title = 'Laporan';
include __DIR__ . '/includes/header.php';

// Date filters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

// Get stats
$stmt = $pdo->prepare("
    SELECT SUM(amount) as total, COUNT(*) as count 
    FROM payments 
    WHERE payment_date BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$payments = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT SUM(total) as total, COUNT(*) as count 
    FROM bills 
    WHERE created_at BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$bills = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM customers 
    WHERE created_at BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$newCustomers = $stmt->fetchColumn();
?>

<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Laporan Keuangan</h2>
        <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-primary btn-ripple"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
    </div>
    
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <form class="card p-3">
                <h5 class="mb-3">Filter Tanggal</h5>
                <div class="mb-3">
                    <label class="form-label">Dari</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>" onchange="this.form.submit()">
                </div>
                <div class="mb-3">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>" onchange="this.form.submit()">
                </div>
                <button class="btn btn-primary w-100 btn-ripple">Filter</button>
            </form>
        </div>
        
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Ringkasan Periode: <?= date('d F Y', strtotime($startDate)) ?> - <?= date('d F Y', strtotime($endDate)) ?></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="stat-card scrollFadeIn" data-stagger="1">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #34D399);"><i class="bi bi-cash-coin"></i></div>
                                <div>
                                    <div class="stat-value"><?= rupiah($payments['total'] ?? 0) ?></div>
                                    <div class="stat-label">Total Pembayaran</div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-card scrollFadeIn" data-stagger="2">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #6366F1, #4F46E5);"><i class="bi bi-receipt"></i></div>
                                <div>
                                    <div class="stat-value"><?= rupiah($bills['total'] ?? 0) ?></div>
                                    <div class="stat-label">Total Tagihan</div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-card scrollFadeIn" data-stagger="3">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #8B5CF6, #A78BFA);"><i class="bi bi-person-plus"></i></div>
                                <div>
                                    <div class="stat-value"><?= $newCustomers ?></div>
                                    <div class="stat-label">Pelanggan Baru</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pembayaran per Bulan</h5>
                    <button class="btn btn-sm btn-outline-primary btn-ripple" onclick="window.print()">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Jumlah Tagihan</th>
                                <th>Pembayaran</th>
                                <th>Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT DATE_FORMAT(b.created_at, '%Y-%m') as period, 
                                       SUM(b.total) as bill_total, 
                                       SUM(p.amount) as paid_total
                                FROM bills b
                                LEFT JOIN payments p ON b.id = p.bill_id
                                WHERE b.created_at BETWEEN ? AND ?
                                GROUP BY DATE_FORMAT(b.created_at, '%Y-%m')
                                ORDER BY period DESC
                            ");
                            $stmt->execute([$startDate, $endDate]);
                            $monthlyData = $stmt->fetchAll();
                            
                            foreach ($monthlyData as $row):
                                $billTotal = $row['bill_total'] ?? 0;
                                $paidTotal = $row['paid_total'] ?? 0;
                            ?>
                            <tr class="table-row">
                                <td><strong><?= bulan_indo($row['period']) ?></strong></td>
                                <td><?= rupiah($billTotal) ?></td>
                                <td class="text-success"><?= rupiah($paidTotal) ?></td>
                                <td class="<?= ($billTotal - $paidTotal) > 0 ? 'text-danger' : 'text-success' ?>"><?= rupiah($billTotal - $paidTotal) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>