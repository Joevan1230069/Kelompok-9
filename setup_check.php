<?php
require_once __DIR__ . '/config/database.php';
$ok=true; $message=''; $tables=[];
try {
  $pdo=db();
  $tables=$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
  $message='Koneksi database berhasil.';
} catch(Throwable $e){$ok=false;$message=$e->getMessage();}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Setup Check</title><style>body{font-family:Arial;background:#f4f7fc;padding:35px;color:#172033}.box{max-width:760px;margin:auto;background:white;padding:24px;border-radius:14px;border:1px solid #e6eaf0}.ok{color:#15803d}.bad{color:#b91c1c}code{background:#f1f5f9;padding:2px 5px;border-radius:5px}</style></head><body><div class="box"><h2>RS Permata Asri — XAMPP Setup Check</h2><p class="<?= $ok?'ok':'bad' ?>"><b><?= htmlspecialchars($message) ?></b></p><?php if($ok): ?><p>Database: <code><?= DB_NAME ?></code></p><p>Jumlah tabel terdeteksi: <b><?= count($tables) ?></b></p><p><?= implode(', ',array_map('htmlspecialchars',$tables)) ?></p><p><a href="login.php">Buka Login SIMRS</a></p><?php else: ?><p>Pastikan MySQL/MariaDB aktif, database sudah diimport, dan pengaturan di <code>config/database.php</code> sesuai.</p><?php endif; ?></div></body></html>
