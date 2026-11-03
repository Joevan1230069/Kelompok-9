<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_login();
require_permission($user, 'registration.manage');
$pageTitle = 'Patient Registration';

$units = db()->query("SELECT id, code, name FROM units WHERE unit_type IN ('POLI','IGD') ORDER BY name")->fetchAll();
$doctors = db()->query("SELECT e.id, e.employee_code, e.full_name, e.unit_id FROM employees e JOIN roles r ON r.id=e.role_id WHERE r.code='dokter' AND e.is_active=1 ORDER BY e.full_name")->fetchAll();
$schedules = db()->query("SELECT ds.id, ds.employee_id, ds.unit_id, ds.day_name, TIME_FORMAT(ds.start_time,'%H:%i') AS start_time, TIME_FORMAT(ds.end_time,'%H:%i') AS end_time, e.full_name, un.name AS unit_name FROM doctor_schedules ds JOIN employees e ON e.id=ds.employee_id JOIN units un ON un.id=ds.unit_id ORDER BY ds.id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = db();
    $nik = trim($_POST['nik'] ?? '');
    $name = trim($_POST['full_name'] ?? '');
    $birth = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $payer = $_POST['payer'] ?? 'Umum';
    $unitId = (int) ($_POST['unit_id'] ?? 0);
    $doctorId = (int) ($_POST['doctor_employee_id'] ?? 0);
    $scheduleId = (int) ($_POST['schedule_id'] ?? 0);
    $visitDate = $_POST['visit_date'] ?? date('Y-m-d');
    $complaint = trim($_POST['complaint'] ?? '');

    if (!preg_match('/^\d{16}$/', $nik) || $name === '' || $birth === '' || !$unitId || !$doctorId || !$scheduleId) {
        flash('error', 'Lengkapi data pasien. NIK harus 16 digit dan jadwal dokter wajib dipilih.');
        redirect('patient_registration.php');
    }

    try {
        $pdo->beginTransaction();
        $find = $pdo->prepare('SELECT id, medical_record_no FROM patients WHERE nik = ? LIMIT 1');
        $find->execute([$nik]);
        $patient = $find->fetch();

        if (!$patient) {
            $stmt = $pdo->prepare('INSERT INTO patients (medical_record_no, nik, full_name, birth_date, gender, address, phone, email, payer) VALUES (NULL,?,?,?,?,?,?,?,?)');
            $stmt->execute([$nik,$name,$birth,$gender,$address,$phone,$email,$payer]);
            $patientId = (int) $pdo->lastInsertId();
            $mrn = 'RM-' . date('Y') . '-' . str_pad((string)$patientId, 6, '0', STR_PAD_LEFT);
            $pdo->prepare('UPDATE patients SET medical_record_no=? WHERE id=?')->execute([$mrn,$patientId]);
        } else {
            $patientId = (int) $patient['id'];
            $mrn = $patient['medical_record_no'];
        }

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE visit_date=? AND unit_id=?');
        $countStmt->execute([$visitDate,$unitId]);
        $queue = (int) $countStmt->fetchColumn() + 1;
        $unitCodeStmt = $pdo->prepare('SELECT code FROM units WHERE id=?');
        $unitCodeStmt->execute([$unitId]);
        $unitCode = (string) $unitCodeStmt->fetchColumn();
        $queueNo = substr($unitCode,0,2) . str_pad((string)$queue,3,'0',STR_PAD_LEFT);

        $stmt = $pdo->prepare("INSERT INTO registrations (registration_no, patient_id, unit_id, doctor_employee_id, schedule_id, visit_date, complaint, payer, status, queue_no, created_by) VALUES (NULL,?,?,?,?,?,?,?,'Menunggu',?,?)");
        $stmt->execute([$patientId,$unitId,$doctorId,$scheduleId,$visitDate,$complaint,$payer,$queueNo,$user['user_id']]);
        $registrationId = (int) $pdo->lastInsertId();
        $regNo = 'REG-' . date('Ymd') . '-' . str_pad((string)$registrationId,4,'0',STR_PAD_LEFT);
        $pdo->prepare('UPDATE registrations SET registration_no=? WHERE id=?')->execute([$regNo,$registrationId]);
        $pdo->commit();

        flash('success', "Registrasi berhasil. No. RM: $mrn · No. Registrasi: $regNo · Antrean: $queueNo");
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('error', 'Registrasi gagal. Periksa kembali data yang diinput.');
    }
    redirect('patient_registration.php');
}

$recent = db()->query("SELECT r.registration_no, r.visit_date, r.queue_no, r.status, p.medical_record_no, p.full_name, un.name AS unit_name, e.full_name AS doctor_name FROM registrations r JOIN patients p ON p.id=r.patient_id JOIN units un ON un.id=r.unit_id LEFT JOIN employees e ON e.id=r.doctor_employee_id ORDER BY r.id DESC LIMIT 10")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="actions" style="justify-content:space-between;align-items:flex-start">
  <div>
    <h1 class="page-title">Patient Registration</h1>
    <p class="page-subtitle">Pendaftaran pasien oleh petugas, poli/dokter tujuan, dan jadwal kunjungan tersimpan langsung ke database.</p>
  </div>
  <a class="btn btn-secondary" href="patient_registration_requests.php">Lihat Registrasi Online</a>
</div>
<div class="grid grid-2">
  <div class="card"><h2 class="section-title">Data Pasien & Kunjungan</h2><form method="POST">
    <div class="grid grid-2">
      <div class="field"><label>NIK</label><input class="input" name="nik" maxlength="16" required></div>
      <div class="field"><label>Nama Lengkap</label><input class="input" name="full_name" required></div>
      <div class="field"><label>Tanggal Lahir</label><input class="input" type="date" name="birth_date" required></div>
      <div class="field"><label>Jenis Kelamin</label><select class="input" name="gender"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
      <div class="field"><label>No. HP</label><input class="input" name="phone"></div>
      <div class="field"><label>Email</label><input class="input" type="email" name="email"></div>
    </div>
    <div class="field"><label>Alamat</label><textarea class="input" name="address"></textarea></div>
    <div class="grid grid-2">
      <div class="field"><label>Penjaminan</label><select class="input" name="payer"><option>Umum</option><option>BPJS</option><option>Asuransi</option></select></div>
      <div class="field"><label>Tanggal Kunjungan</label><input class="input" type="date" name="visit_date" value="<?= date('Y-m-d') ?>" required></div>
      <div class="field"><label>Poli / Unit Tujuan</label><select class="input" name="unit_id" required><option value="">-- Pilih Unit --</option><?php foreach($units as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['code'].' - '.$u['name']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Dokter Tujuan</label><select class="input" name="doctor_employee_id" required><option value="">-- Pilih Dokter --</option><?php foreach($doctors as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['employee_code'].' - '.$d['full_name']) ?></option><?php endforeach; ?></select></div>
    </div>
    <div class="field"><label>Jadwal Praktik</label><select class="input" name="schedule_id" required><option value="">-- Pilih Jadwal --</option><?php foreach($schedules as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['day_name'].' · '.$s['start_time'].'-'.$s['end_time'].' · '.$s['full_name'].' · '.$s['unit_name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Keluhan Utama</label><textarea class="input" name="complaint"></textarea></div>
    <button class="btn btn-primary">Daftarkan Pasien</button>
  </form></div>
  <div class="table-wrap"><table><thead><tr><th>Registrasi</th><th>Pasien</th><th>Tujuan</th><th>Status</th></tr></thead><tbody><?php foreach($recent as $r): ?><tr><td><?= e($r['registration_no']) ?><br><small><?= e($r['visit_date'].' · '.$r['queue_no']) ?></small></td><td><b><?= e($r['full_name']) ?></b><br><small><?= e($r['medical_record_no']) ?></small></td><td><?= e($r['unit_name']) ?><br><small><?= e($r['doctor_name'] ?? '-') ?></small></td><td><span class="badge badge-yellow"><?= e($r['status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
