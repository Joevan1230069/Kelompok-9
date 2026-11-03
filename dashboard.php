<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_login();
require_permission($user, 'dashboard.view');
$pageTitle = 'Dashboard';

$stats = [
    'patients' => (int) db()->query('SELECT COUNT(*) FROM patients')->fetchColumn(),
    'today' => (int) db()->query('SELECT COUNT(*) FROM registrations WHERE visit_date = CURDATE()')->fetchColumn(),
    'lab_pending' => (int) db()->query("SELECT COUNT(*) FROM lab_orders WHERE status IN ('Menunggu','In Process')")->fetchColumn(),
    'inpatient' => (int) db()->query("SELECT COUNT(*) FROM inpatient_admissions WHERE status = 'Dirawat'")->fetchColumn(),
];

$doctorLabResults = [];
if ($user['role_code'] === 'dokter') {
    $stmt = db()->prepare(
        "SELECT o.order_no, p.full_name AS patient_name, p.medical_record_no,
                l.test_name, l.status, l.result, l.verified_at,
                ver.employee_code AS verifier_code, ver.full_name AS verifier_name
         FROM lab_orders l
         JOIN orders o ON o.id=l.order_id
         JOIN registrations r ON r.id=o.registration_id
         JOIN patients p ON p.id=r.patient_id
         LEFT JOIN employees ver ON ver.id=l.verified_by
         WHERE o.ordering_employee_id=?
         ORDER BY l.id DESC LIMIT 5"
    );
    $stmt->execute([(int) $user['employee_id']]);
    $doctorLabResults = $stmt->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Dashboard</h1>
<p class="page-subtitle">Ringkasan operasional SIMRS berdasarkan data yang tersimpan di MariaDB.</p>
<div class="grid grid-4">
  <div class="card stat"><small>Total Pasien</small><b><?= $stats['patients'] ?></b></div>
  <div class="card stat"><small>Kunjungan Hari Ini</small><b><?= $stats['today'] ?></b></div>
  <div class="card stat"><small>Order Lab Aktif</small><b><?= $stats['lab_pending'] ?></b></div>
  <div class="card stat"><small>Pasien Rawat Inap</small><b><?= $stats['inpatient'] ?></b></div>
</div>

<?php if ($user['role_code'] === 'dokter'): ?>
<div class="card" style="margin-top:18px">
  <h2 class="section-title">Hasil Laboratorium Terbaru</h2>
  <p class="note">Menampilkan order laboratorium yang dibuat oleh dokter yang sedang login.</p>
  <div class="table-wrap" style="margin-top:12px">
    <table>
      <thead><tr><th>Order</th><th>Pasien</th><th>Pemeriksaan</th><th>Status</th><th>Hasil</th></tr></thead>
      <tbody>
      <?php if (!$doctorLabResults): ?>
        <tr><td colspan="5" style="text-align:center;padding:20px;color:#667085">Belum ada order laboratorium.</td></tr>
      <?php endif; ?>
      <?php foreach ($doctorLabResults as $lab): ?>
        <tr>
          <td><b><?= e($lab['order_no']) ?></b></td>
          <td><?= e($lab['patient_name']) ?><br><small><?= e($lab['medical_record_no']) ?></small></td>
          <td><?= e($lab['test_name']) ?></td>
          <td><span class="badge <?= $lab['status']==='Tervalidasi'?'badge-green':($lab['status']==='In Process'?'badge-blue':'badge-yellow') ?>"><?= e($lab['status']) ?></span></td>
          <td>
            <?= $lab['result'] ? nl2br(e($lab['result'])) : '<span class="note">Belum tersedia</span>' ?>
            <?php if ($lab['verified_at']): ?><br><small>Verifikasi: <?= e(($lab['verifier_code'] ?? '-') . ' - ' . ($lab['verifier_name'] ?? '-')) ?></small><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="card" style="margin-top:18px">
  <h2 class="section-title">Progress versi XAMPP</h2>
  <p class="note">Login, RBAC, Data Pegawai, Patient Registration, EMR, CPOE, Triase, Laboratory, Radiology dan Rawat Inap pada versi ini sudah membaca/menulis data melalui MariaDB. Beberapa modul lain masih dipertahankan sebagai tahap pengembangan berikutnya.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
