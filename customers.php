<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
$title = 'Kelola Pelanggan';
include __DIR__ . '/../includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $meter_number = $_POST['meter_number'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $user_id = $_POST['user_id'] ?? null;
    
    try {
        if ($user_id) {
            $pdo->prepare("UPDATE customers SET name=?, address=?, meter_number=?, phone=? WHERE id=?")->execute([$name, $address, $meter_number, $phone, $user_id]);
            set_flash('success', 'Pelanggan berhasil diupdate');
        } else {
            $pdo->prepare("INSERT INTO customers (user_id, name, address, meter_number, phone) VALUES (?, ?, ?, ?, ?)")->execute([null, $name, $address, $meter_number, $phone]);
            set_flash('success', 'Pelanggan baru ditambahkan');
        }
    } catch (Exception $e) {
        set_flash('danger', 'Gagal: ' . $e->getMessage());
    }
    header('Location: ' . BASE_URL . '/customers.php');
    exit;
}

// Get all customers
$stmt = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC");
$customers = $stmt->fetchAll();

// Get customer for edit (if editing)
$edit_customer = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_customer = $stmt->fetch();
}
?>

<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-people me-2"></i>Kelola Pelanggan</h2>
        <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-outline-primary btn-ripple">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>
    
    <button class="btn btn-primary mb-3 btn-ripple" data-bs-toggle="modal" data-bs-target="#customerModal">
        <i class="bi bi-plus-circle me-1"></i>Tambah Pelanggan
    </button>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Daftar Pelanggan (<?= count($customers) ?> pelanggan)</h5>
        </div>
        <div class="card-body">
            <?php if ($customers): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Meter</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Tanggal Daftar</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $c): ?>
                        <tr class="table-row">
                            <td><code><?= esc($c['meter_number']) ?></code></td>
                            <td><strong><?= esc($c['name']) ?></strong></td>
                            <td><small><?= esc($c['address']) ?></small></td>
                            <td><?= esc($c['phone'] ?? '-') ?></td>
                            <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/customers.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-primary btn-ripple" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-center text-muted py-4"><i class="bi bi-people fs-1 mb-2"></i><br>Belum ada pelanggan</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i><?= $edit_customer ? 'Edit' : 'Tambah' ?> Pelanggan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $edit_customer['id'] ?? '' ?>">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" required value="<?= esc($edit_customer['name'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="3" required><?= esc($edit_customer['address'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. Meter</label>
                    <input type="text" name="meter_number" class="form-control" required value="<?= esc($edit_customer['meter_number'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">No. HP (Opsional)</label>
                    <input type="text" name="phone" class="form-control" value="<?= esc($edit_customer['phone'] ?? '') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-ripple"><?= $edit_customer ? 'Update' : 'Simpan' ?></button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>