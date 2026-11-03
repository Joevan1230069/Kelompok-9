<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE username = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$username]);
    $account = $stmt->fetch();

    if ($account && password_verify($password, $account['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $account['id'];
        redirect('dashboard.php');
    }

    $error = 'Username atau password tidak sesuai.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Rumah Sakit Permata Asri</title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
  <div class="login-shell">
    <div class="login-card">
      <div class="brand-row">
        <div class="brand-mark">+</div>
        <div>
          <div class="brand-name">Rumah Sakit Permata Asri</div>
          <div class="brand-sub">Sistem Informasi Manajemen Rumah Sakit</div>
        </div>
      </div>
      <h1>Login</h1>
      <p>Masuk menggunakan akun pegawai yang sudah terdaftar.</p>

      <?php if ($error): ?>
        <div class="login-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Username</label>
          <input class="form-control" name="username" required autocomplete="username">
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input class="form-control" type="password" name="password" required autocomplete="current-password">
        </div>
        <div class="login-actions">
          <button class="btn btn-primary" type="submit">Submit</button>
          <button class="btn btn-secondary" type="reset">Cancel</button>
        </div>
      </form>

      <div class="patient-public-entry">
        <div>
          <strong>Pasien belum terdaftar?</strong>
          <small>Daftar kunjungan tanpa login pegawai.</small>
        </div>
        <a class="btn btn-secondary" href="patient_self_registration.php">Daftar sebagai Pasien</a>
      </div>

      <div class="demo-box">
        <strong>Akun uji</strong>
        <small>Dokter: dokter / dokter123 · Lab: lab / lab123 · Admin: novella / permata123</small>
      </div>
    </div>
  </div>
</body>
</html>
