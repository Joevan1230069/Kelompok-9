RUMAH SAKIT PERMATA ASRI — SIMRS XAMPP (PROGRESS VERSION)

1. Extract folder ini.
2. Pastikan nama folder menjadi: rs_permata_asri
3. Pindahkan folder tersebut ke:
   C:\xampp\htdocs\rs_permata_asri
4. Buka XAMPP Control Panel.
5. Start Apache dan MySQL.
6. Buka browser: http://localhost/phpmyadmin
7. Pilih menu Import, lalu import file:
   C:\xampp\htdocs\rs_permata_asri\database\simrs_permata_asri.sql
   File SQL otomatis membuat database `simrs_permata_asri`.
8. Cek koneksi:
   http://localhost/rs_permata_asri/setup_check.php
9. Jika berhasil, buka:
   http://localhost/rs_permata_asri/

KONFIGURASI DATABASE DEFAULT
Host     : 127.0.0.1
Database : simrs_permata_asri
User     : root
Password : kosong

Jika user root MySQL XAMPP Anda memiliki password, edit:
config/database.php

AKUN UJI
Admin      : novella / permata123
Dokter     : dokter / dokter123
Perawat    : perawat / perawat123
Petugas IGD: triage / triage123
Laboratorium: lab / lab123
Radiologi  : radiologi / rad123
Admisi     : admisi / admisi123
Rekam Medis: rekammedis / rm123

UPDATE MINGGU INI
- Migrasi prototype dari localStorage ke PHP + MariaDB.
- Login dan RBAC dibaca dari database.
- Sidebar menu berasal dari tabel menus dan role_permissions.
- Konfirmasi logout Ya/Tidak.
- User Registration.
- Data Dokter diganti Data Pegawai dengan ID pegawai, profesi, role, unit.
- Patient Registration lengkap + poli/dokter/jadwal/antrean.
- EMR menyimpan pemeriksa berdasarkan ID pegawai dan aktivitas.
- CPOE membuat order dokter ke Laboratory/Radiology.
- Save Triage, Laboratory result, dan Rawat Inap ke database.
- Laporan operasional tanpa kolom dokter.

BELUM DISELESAIKAN SENGAJA UNTUK PROGRESS BERIKUTNYA
- Master obat + provider + expiry.
- Pharmacy dispensing penuh.
- Inventory lanjutan.
- Nursing lanjutan.
- Bed management lengkap.
- Billing lanjutan.
- Audit trail lengkap.

UPDATE REGISTRASI PASIEN ONLINE
-------------------------------
Jika database lama sudah pernah di-import, JANGAN reset database.
Setelah mengganti folder aplikasi dengan versi terbaru, buka:
http://localhost/rs_permata_asri/update_database.php
Klik "Jalankan Update" satu kali.

Halaman publik pasien:
http://localhost/rs_permata_asri/patient_self_registration.php

Setelah pasien mengirim pendaftaran, login sebagai admin/admisi lalu buka menu:
Registrasi Online
Petugas dapat Setujui atau Tolak. Jika disetujui, sistem otomatis membuat/menemukan pasien lalu membuat registrasi rumah sakit.
