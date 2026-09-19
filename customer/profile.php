<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$title = 'Profil Saya';
include __DIR__ . '/../includes/header.php';

$user = current_user();
$customerId = $user['customer_id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();
?>

<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-person me-2"></i>Profil Pelanggan</h2>
        <a href="<?= BASE_URL ?>/customer/index.php" class="btn btn-outline-primary btn-ripple">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Pelanggan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <i class="bi bi-person-circle bi-3x text-primary"></i>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="fw-bold"><i class="bi bi-person me-2"></i>Nama Lengkap</td>
                                    <td class="text-end"><?= esc($customer['name'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="bi bi-phone me-2"></i>No. HP</td>
                                    <td class="text-end"><?= esc($customer['phone'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="bi bi-speedometer me-2"></i>No. Meter</td>
                                    <td class="text-end"><code><?= esc($customer['meter_number'] ?? '-') ?></code></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold"><i class="bi bi-house-door me-2"></i>Alamat</td>
                                    <td class="text-end"><?= nl2br(esc($customer['address'] ?? '-')) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Akun Login</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="fw-bold"><i class="bi bi-identity me-2"></i>Username</td>
                            <td class="text-end"><code><?= esc($user['username']) ?></code></td>
                        </tr>
                        <tr>
                            <td class="fw-bold"><i class="bi bi-clock-history me-2"></i>Bergabung</td>
                            <td class="text-end"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>