<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    header('Location: ' . ($user['role'] === 'admin' ? BASE_URL . '/admin/index.php' : BASE_URL . '/customer/index.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        session_regenerate_id(true);
        header('Location: ' . ($user['role'] === 'admin' ? BASE_URL . '/admin/index.php' : BASE_URL . '/customer/index.php'));
        exit;
    } else {
        set_flash('danger', 'Username atau password salah.');
    }
}

$title = 'Login';
include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card shadow-lg overflow-hidden">
            <div class="row g-0">
                <div class="col-12">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-droplet-fill fs-2"></i>
                            </div>
                            <h3 class="mb-1">Selamat Datang</h3>
                            <p class="text-muted mb-4">Silakan login untuk sistem tagihan air</p>
                        </div>
                        
                        <form method="POST" class="scrollFadeIn" data-stagger="1">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" required autofocus">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 btn-ripple">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-0 text-muted">Hubungi <strong>admin</strong> bila lupa password</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var inputs = document.querySelectorAll('input');
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].style.animationDelay = (i * 0.1) + 's';
        inputs[i].style.animation = 'staggerFadeIn 0.6s ease-out forwards';
        inputs[i].style.opacity = '0';
        inputs[i].style.transform = 'translateY(20px)';
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>