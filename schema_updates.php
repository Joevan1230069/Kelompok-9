<?php

declare(strict_types=1);

function ensure_progress03_schema(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS patient_registration_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            request_no VARCHAR(40) NULL UNIQUE,
            nik VARCHAR(16) NOT NULL,
            full_name VARCHAR(150) NOT NULL,
            birth_date DATE NOT NULL,
            gender ENUM('L','P') NOT NULL,
            address TEXT NULL,
            phone VARCHAR(30) NOT NULL,
            email VARCHAR(120) NULL,
            payer VARCHAR(40) NOT NULL DEFAULT 'Umum',
            unit_id INT NOT NULL,
            doctor_employee_id INT NOT NULL,
            schedule_id INT NOT NULL,
            visit_date DATE NOT NULL,
            complaint TEXT NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'Menunggu Verifikasi',
            admin_note TEXT NULL,
            patient_id INT NULL,
            registration_id INT NULL,
            reviewed_by INT NULL,
            reviewed_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_prr_status (status),
            INDEX idx_prr_visit_date (visit_date),
            CONSTRAINT fk_prr_unit FOREIGN KEY (unit_id) REFERENCES units(id),
            CONSTRAINT fk_prr_doctor FOREIGN KEY (doctor_employee_id) REFERENCES employees(id),
            CONSTRAINT fk_prr_schedule FOREIGN KEY (schedule_id) REFERENCES doctor_schedules(id),
            CONSTRAINT fk_prr_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
            CONSTRAINT fk_prr_registration FOREIGN KEY (registration_id) REFERENCES registrations(id),
            CONSTRAINT fk_prr_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id)
        ) ENGINE=InnoDB"
    );

    $menuCheck = $pdo->prepare('SELECT id FROM menus WHERE page_file=? LIMIT 1');
    $menuCheck->execute(['patient_registration_requests.php']);
    if (!$menuCheck->fetchColumn()) {
        $menuInsert = $pdo->prepare(
            "INSERT INTO menus (label, icon, section_name, page_file, permission_code, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, 1)"
        );
        $menuInsert->execute([
            'Registrasi Online',
            '▦',
            'TRANSAKSI',
            'patient_registration_requests.php',
            'registration.manage',
            19,
        ]);
    }

    $units = [
        ['PB', 'Poli Bedah', 'POLI'],
        ['PK', 'Poli Kandungan', 'POLI'],
    ];
    $unitInsert = $pdo->prepare(
        'INSERT INTO units (code, name, unit_type)
         SELECT ?, ?, ?
         WHERE NOT EXISTS (SELECT 1 FROM units WHERE code=?)'
    );
    foreach ($units as [$code, $name, $type]) {
        $unitInsert->execute([$code, $name, $type, $code]);
    }

    $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE code='dokter' LIMIT 1");
    $roleStmt->execute();
    $doctorRoleId = (int) $roleStmt->fetchColumn();

    if ($doctorRoleId > 0) {
        $doctorSeeds = [
            ['PEG-DOK-003', 'dr. Raka Mahendra', 'Dokter', 'PU'],
            ['PEG-DOK-004', 'dr. Nadia Putri, Sp.PD', 'Dokter Sp.PD', 'PD'],
            ['PEG-DOK-005', 'dr. Salsabila Rahman, Sp.A', 'Dokter Sp.A', 'PA'],
            ['PEG-DOK-006', 'dr. Bima Aditya, Sp.B', 'Dokter Sp.B', 'PB'],
            ['PEG-DOK-007', 'dr. Citra Maharani, Sp.OG', 'Dokter Sp.OG', 'PK'],
        ];

        $unitIdStmt = $pdo->prepare('SELECT id FROM units WHERE code=? LIMIT 1');
        $employeeExists = $pdo->prepare('SELECT id FROM employees WHERE employee_code=? LIMIT 1');
        $employeeInsert = $pdo->prepare(
            'INSERT INTO employees (employee_code, full_name, profession, role_id, unit_id, is_active)
             VALUES (?, ?, ?, ?, ?, 1)'
        );

        foreach ($doctorSeeds as [$employeeCode, $name, $profession, $unitCode]) {
            $employeeExists->execute([$employeeCode]);
            if ($employeeExists->fetchColumn()) {
                continue;
            }

            $unitIdStmt->execute([$unitCode]);
            $unitId = (int) $unitIdStmt->fetchColumn();
            if ($unitId > 0) {
                $employeeInsert->execute([$employeeCode, $name, $profession, $doctorRoleId, $unitId]);
            }
        }

        $scheduleSeeds = [
            ['PEG-DOK-003', 'PU', 'Selasa', '08:00:00', '12:00:00'],
            ['PEG-DOK-003', 'PU', 'Jumat', '08:00:00', '11:00:00'],
            ['PEG-DOK-004', 'PD', 'Senin', '13:00:00', '16:00:00'],
            ['PEG-DOK-004', 'PD', 'Jumat', '09:00:00', '12:00:00'],
            ['PEG-DOK-005', 'PA', 'Senin', '09:00:00', '12:00:00'],
            ['PEG-DOK-005', 'PA', 'Kamis', '13:00:00', '16:00:00'],
            ['PEG-DOK-006', 'PB', 'Selasa', '13:00:00', '16:00:00'],
            ['PEG-DOK-006', 'PB', 'Jumat', '13:00:00', '16:00:00'],
            ['PEG-DOK-007', 'PK', 'Rabu', '09:00:00', '12:00:00'],
            ['PEG-DOK-007', 'PK', 'Sabtu', '08:00:00', '11:00:00'],
        ];

        $employeeIdStmt = $pdo->prepare('SELECT id FROM employees WHERE employee_code=? LIMIT 1');
        $scheduleExists = $pdo->prepare(
            'SELECT id FROM doctor_schedules
             WHERE employee_id=? AND unit_id=? AND day_name=? AND start_time=? AND end_time=? LIMIT 1'
        );
        $scheduleInsert = $pdo->prepare(
            'INSERT INTO doctor_schedules (employee_id, unit_id, day_name, start_time, end_time)
             VALUES (?, ?, ?, ?, ?)'
        );

        foreach ($scheduleSeeds as [$employeeCode, $unitCode, $dayName, $startTime, $endTime]) {
            $employeeIdStmt->execute([$employeeCode]);
            $employeeId = (int) $employeeIdStmt->fetchColumn();
            $unitIdStmt->execute([$unitCode]);
            $unitId = (int) $unitIdStmt->fetchColumn();
            if (!$employeeId || !$unitId) {
                continue;
            }
            $scheduleExists->execute([$employeeId, $unitId, $dayName, $startTime, $endTime]);
            if (!$scheduleExists->fetchColumn()) {
                $scheduleInsert->execute([$employeeId, $unitId, $dayName, $startTime, $endTime]);
            }
        }
    }
}
