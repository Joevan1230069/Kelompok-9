<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_login();
require_permission($user, 'cpoe.manage');
$pageTitle = 'CPOE / Order Management';

$registrations = db()->query("SELECT r.id, r.registration_no, p.medical_record_no, p.full_name FROM registrations r JOIN patients p ON p.id=r.patient_id ORDER BY r.id DESC LIMIT 50")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registrationId = (int) ($_POST['registration_id'] ?? 0);
    $type = $_POST['order_type'] ?? '';
    $detail = trim($_POST['detail'] ?? '');
    $priority = $_POST['priority'] ?? 'Rutin';
    $indication = trim($_POST['indication'] ?? '');

    if (!$registrationId || !in_array($type, ['Laboratorium','Radiologi','Obat','Konsultasi','Rawat Inap'], true) || $detail === '') {
        flash('error', 'Data order belum lengkap.');
        redirect('cpoe.php');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO orders (order_no, registration_id, ordering_employee_id, order_type, detail, clinical_indication, priority, status, created_at) VALUES (NULL,?,?,?,?,?,?,'Ordered',NOW())");
        $stmt->execute([$registrationId,$user['employee_id'],$type,$detail,$indication,$priority]);
        $orderId = (int) $pdo->lastInsertId();
        $orderNo = 'ORD-' . date('Ymd') . '-' . str_pad((string)$orderId,4,'0',STR_PAD_LEFT);
        $pdo->prepare('UPDATE orders SET order_no=? WHERE id=?')->execute([$orderNo,$orderId]);

        if ($type === 'Laboratorium') {
            $pdo->prepare("INSERT INTO lab_orders (order_id, test_name, specimen, status) VALUES (?,?,NULL,'Menunggu')")->execute([$orderId,$detail]);
        }
        if ($type === 'Radiologi') {
            $pdo->prepare("INSERT INTO radiology_orders (order_id, exam_name, clinical_indication, status) VALUES (?,?,?,'Menunggu')")->execute([$orderId,$detail,$indication]);
        }
        $pdo->commit();
        flash('success', "$type berhasil diorder oleh {$user['employee_code']} - {$user['full_name']}. No. Order: $orderNo");
    } catch(Throwable $e) {
        $pdo->rollBack();
        flash('error', 'Order gagal disimpan.');
    }
    redirect('cpoe.php');
}

$orders = db()->query("SELECT o.order_no, o.order_type, o.detail, o.priority, o.status, o.created_at, p.full_name AS patient_name, p.medical_record_no, e.employee_code, e.full_name AS doctor_name, l.status AS lab_status, l.result AS lab_result, l.verified_at AS lab_verified_at, lv.employee_code AS lab_verifier_code, lv.full_name AS lab_verifier_name, ro.status AS radiology_status, ro.result AS radiology_result FROM orders o JOIN registrations r ON r.id=o.registration_id JOIN patients p ON p.id=r.patient_id JOIN employees e ON e.id=o.ordering_employee_id LEFT JOIN lab_orders l ON l.order_id=o.id LEFT JOIN employees lv ON lv.id=l.verified_by LEFT JOIN radiology_orders ro ON ro.order_id=o.id ORDER BY o.id DESC LIMIT 20")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">CPOE / Order Management</h1>
<p class="page-subtitle">Order Laboratory dan Radiology hanya terbentuk dari perintah dokter dan menyimpan ID dokter peminta.</p>
<div class="grid grid-2">
  <div class="card"><h2 class="section-title">Buat Order Dokter</h2><form method="POST">
    <div class="field"><label>Pasien / Kunjungan</label><select class="input" name="registration_id" required><option value="">-- Pilih Pasien --</option><?php foreach($registrations as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['registration_no'].' · '.$r['medical_record_no'].' · '.$r['full_name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Jenis Order</label><select class="input" name="order_type" required><option>Laboratorium</option><option>Radiologi</option><option>Obat</option><option>Konsultasi</option><option>Rawat Inap</option></select></div>
    <div class="field"><label>Perintah / Pemeriksaan</label><input class="input" name="detail" placeholder="Contoh: Darah lengkap / X-Ray Thorax" required></div>
    <div class="field"><label>Indikasi Klinis</label><textarea class="input" name="indication"></textarea></div>
    <div class="field"><label>Prioritas</label><select class="input" name="priority"><option>Rutin</option><option>CITO</option></select></div>
    <button class="btn btn-primary">Kirim Order</button>
  </form></div>
  <div class="table-wrap"><table><thead><tr><th>Order</th><th>Pasien</th><th>Perintah</th><th>Dokter Peminta</th><th>Status / Hasil</th></tr></thead><tbody><?php foreach($orders as $o): ?><tr><td><b><?= e($o['order_no']) ?></b><br><span class="badge badge-blue"><?= e($o['order_type']) ?></span></td><td><?= e($o['patient_name']) ?><br><small><?= e($o['medical_record_no']) ?></small></td><td><?= e($o['detail']) ?><br><small><?= e($o['priority'].' · '.$o['status']) ?></small></td><td><?= e($o['employee_code']) ?><br><small><?= e($o['doctor_name']) ?></small></td><td><?php if($o['order_type']==='Laboratorium'): ?><span class="badge <?= $o['lab_status']==='Tervalidasi'?'badge-green':($o['lab_status']==='In Process'?'badge-blue':'badge-yellow') ?>"><?= e($o['lab_status'] ?? 'Menunggu') ?></span><br><small><?= $o['lab_result'] ? e($o['lab_result']) : 'Hasil belum tersedia' ?></small><?php if($o['lab_verified_at']): ?><br><small>Verifikasi: <?= e(($o['lab_verifier_code'] ?? '-').' - '.($o['lab_verifier_name'] ?? '-')) ?></small><?php endif; ?><?php elseif($o['order_type']==='Radiologi'): ?><span class="badge badge-blue"><?= e($o['radiology_status'] ?? $o['status']) ?></span><br><small><?= $o['radiology_result'] ? e($o['radiology_result']) : 'Hasil belum tersedia' ?></small><?php else: ?><span class="badge badge-blue"><?= e($o['status']) ?></span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
