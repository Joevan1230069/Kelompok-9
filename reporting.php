<?php
require_once __DIR__ . '/includes/functions.php';
$user=require_login();
require_permission($user,'reporting.view');
$pageTitle='Laporan Operasional';
$rows=db()->query("SELECT DATE(r.created_at) AS tanggal, COUNT(*) AS kunjungan, SUM(r.status='Rawat Inap') AS rawat_inap FROM registrations r GROUP BY DATE(r.created_at) ORDER BY tanggal DESC LIMIT 14")->fetchAll();
require __DIR__.'/includes/header.php';
?>
<h1 class="page-title">Laporan Operasional</h1><p class="page-subtitle">Laporan dibuat per aktivitas operasional rumah sakit tanpa kolom dokter.</p>
<div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Jumlah Kunjungan</th><th>Masuk Rawat Inap</th><th>Order Lab</th><th>Order Radiologi</th></tr></thead><tbody><?php foreach($rows as $r): $d=$r['tanggal']; $q1=db()->prepare('SELECT COUNT(*) FROM lab_orders l JOIN orders o ON o.id=l.order_id WHERE DATE(o.created_at)=?');$q1->execute([$d]);$lab=$q1->fetchColumn();$q2=db()->prepare('SELECT COUNT(*) FROM radiology_orders ro JOIN orders o ON o.id=ro.order_id WHERE DATE(o.created_at)=?');$q2->execute([$d]);$rad=$q2->fetchColumn(); ?><tr><td><?= e($d) ?></td><td><?= (int)$r['kunjungan'] ?></td><td><?= (int)$r['rawat_inap'] ?></td><td><?= (int)$lab ?></td><td><?= (int)$rad ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php require __DIR__.'/includes/footer.php'; ?>
