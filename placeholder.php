<?php
require_once __DIR__ . '/includes/functions.php';
$user=require_login();
$pageTitle='Modul Pengembangan Berikutnya';
require __DIR__.'/includes/header.php';
?>
<h1 class="page-title">Modul Pengembangan Berikutnya</h1>
<p class="page-subtitle">Halaman ini sengaja belum dilengkapi pada update minggu ini agar pengembangan tetap bertahap.</p>
<div class="card"><p class="note">Fitur detail Pharmacy/Obat (provider & expiry), inventory lanjutan, nursing, billing dan penyempurnaan laporan akan dilanjutkan pada progres berikutnya.</p></div>
<?php require __DIR__.'/includes/footer.php'; ?>
