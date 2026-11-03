RS PERMATA ASRI SIMRS - FIX 03

Perbaikan versi ini:
1. Registrasi pasien online tidak lagi fatal error jika tabel patient_registration_requests belum dibuat.
2. Tabel registrasi online dapat dibuat otomatis oleh aplikasi dan juga melalui update_database.php.
3. Menambah pilihan dokter/poli/jadwal untuk registrasi pasien.
4. Dokter dapat melihat status dan hasil laboratorium terverifikasi di Dashboard.
5. Dokter juga dapat melihat hasil laboratorium di halaman EMR dan CPOE.
6. Hasil menampilkan ID/nama petugas laboratorium yang memverifikasi.

CARA UPDATE PADA FOLDER YANG SUDAH AKTIF
- Backup folder website lama.
- Ganti isi folder website aktif dengan isi folder rs_permata_asri_baru dari ZIP ini.
- JANGAN hapus database simrs_permata_asri.
- Start Apache dan MySQL di XAMPP.
- Buka URL sesuai folder yang kamu pakai. Contoh dari instalasi yang terlihat pada screenshot:
  http://localhost/RS_Permata_asri/rs_permata_asri_baru/update_database.php
- Klik Jalankan Update.
- Setelah berhasil buka:
  http://localhost/RS_Permata_asri/rs_permata_asri_baru/patient_self_registration.php

Walaupun update_database.php belum dijalankan, halaman registrasi publik sekarang mencoba memastikan struktur tabel update tersedia sebelum mengakses data.

TEST HASIL LAB:
1. Login dokter (dokter / dokter123).
2. CPOE -> buat order Laboratorium untuk pasien.
3. Logout -> login lab (lab / lab123).
4. Laboratory -> Terima Spesimen -> isi hasil -> Simpan & Validasi.
5. Logout -> login dokter kembali.
6. Dashboard akan menampilkan Hasil Laboratorium Terbaru.
7. Halaman EMR juga menampilkan Hasil Laboratorium dari Order Dokter.
