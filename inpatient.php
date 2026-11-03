<?php
require_once __DIR__ . '/includes/functions.php';
$user=require_login();
require_permission($user,'inpatient.manage');
$pageTitle='Rawat Inap';
$registrations=db()->query("SELECT r.id,r.registration_no,p.full_name,p.medical_record_no FROM registrations r JOIN patients p ON p.id=r.patient_id ORDER BY r.id DESC LIMIT 50")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $reg=(int)($_POST['registration_id']??0); $room=trim($_POST['room']??''); $bed=trim($_POST['bed']??'');
  if($reg && $room!=='' && $bed!==''){
    db()->prepare("INSERT INTO inpatient_admissions (registration_id,ordered_by,room,bed,status,admitted_at) VALUES (?,?,?,?,'Dirawat',NOW())")->execute([$reg,$user['employee_id'],$room,$bed]);
    db()->prepare("UPDATE registrations SET status='Rawat Inap' WHERE id=?")->execute([$reg]);
    flash('success','Data rawat inap berhasil disimpan.');
  } else flash('error','Pilih pasien, ruang dan bed.');
  redirect('inpatient.php');
}
$rows=db()->query("SELECT ia.*,p.full_name,p.medical_record_no,e.employee_code,e.full_name AS ordered_name FROM inpatient_admissions ia JOIN registrations r ON r.id=ia.registration_id JOIN patients p ON p.id=r.patient_id JOIN employees e ON e.id=ia.ordered_by ORDER BY ia.id DESC")->fetchAll();
require __DIR__.'/includes/header.php';
?>
<h1 class="page-title">Rawat Inap</h1><p class="page-subtitle">Admission rawat inap dapat disimpan ke database. Bed management detail akan dikembangkan pada update berikutnya.</p>
<div class="grid grid-2"><div class="card"><form method="POST"><div class="field"><label>Pasien</label><select class="input" name="registration_id" required><option value="">-- Pilih Kunjungan --</option><?php foreach($registrations as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['registration_no'].' · '.$r['medical_record_no'].' · '.$r['full_name']) ?></option><?php endforeach; ?></select></div><div class="field"><label>Ruang</label><input class="input" name="room" placeholder="Melati / Anggrek" required></div><div class="field"><label>Bed</label><input class="input" name="bed" placeholder="A-101 / Bed 01" required></div><button class="btn btn-primary">Save Rawat Inap</button></form></div><div class="table-wrap"><table><thead><tr><th>Pasien</th><th>Ruang / Bed</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= e($r['full_name']) ?><br><small><?= e($r['medical_record_no']) ?></small></td><td><?= e($r['room'].' · '.$r['bed']) ?></td><td><span class="badge badge-green"><?= e($r['status']) ?></span><br><small>oleh <?= e($r['employee_code']) ?></small></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require __DIR__.'/includes/footer.php'; ?>
