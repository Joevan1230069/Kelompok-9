<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_login();
require_permission($user, 'radiology.manage');
$pageTitle = 'Radiology';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['radiology_id'] ?? 0);
    $result = trim($_POST['result'] ?? '');
    if ($id && $result !== '') {
        db()->prepare("UPDATE radiology_orders SET status='Selesai', result=?, verified_by=?, verified_at=NOW() WHERE id=?")->execute([$result,$user['employee_id'],$id]);
        db()->prepare("UPDATE orders o JOIN radiology_orders ro ON ro.order_id=o.id SET o.status='Completed' WHERE ro.id=?")->execute([$id]);
        flash('success','Hasil radiologi berhasil disimpan.');
    }
    redirect('radiology.php');
}

$rows = db()->query("SELECT ro.id, ro.exam_name, ro.clinical_indication, ro.status, ro.result, o.order_no, p.full_name AS patient_name, p.medical_record_no, d.employee_code AS doctor_code, d.full_name AS doctor_name FROM radiology_orders ro JOIN orders o ON o.id=ro.order_id JOIN registrations r ON r.id=o.registration_id JOIN patients p ON p.id=r.patient_id JOIN employees d ON d.id=o.ordering_employee_id ORDER BY ro.id DESC")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Radiology Worklist</h1>
<p class="page-subtitle">Pemeriksaan radiologi muncul setelah dokter membuat order di CPOE.</p>
<div class="table-wrap"><table><thead><tr><th>Order</th><th>Pasien</th><th>Pemeriksaan</th><th>Dokter Peminta</th><th>Hasil</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><b><?= e($r['order_no']) ?></b></td><td><?= e($r['patient_name']) ?><br><small><?= e($r['medical_record_no']) ?></small></td><td><?= e($r['exam_name']) ?><br><small><?= e($r['clinical_indication']) ?></small></td><td><?= e($r['doctor_code'].' - '.$r['doctor_name']) ?></td><td><?php if($r['status']==='Selesai'): ?><span class="badge badge-green">Selesai</span><div class="note"><?= e($r['result']) ?></div><?php else: ?><form method="POST"><input type="hidden" name="radiology_id" value="<?= $r['id'] ?>"><textarea class="input" name="result" placeholder="Hasil / ekspertise singkat" required></textarea><button class="btn btn-primary" style="margin-top:6px">Simpan Hasil</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
