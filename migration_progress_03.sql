USE simrs_permata_asri;

CREATE TABLE IF NOT EXISTS patient_registration_requests (
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
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (doctor_employee_id) REFERENCES employees(id),
    FOREIGN KEY (schedule_id) REFERENCES doctor_schedules(id),
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (registration_id) REFERENCES registrations(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO units (code,name,unit_type)
SELECT 'PB','Poli Bedah','POLI' WHERE NOT EXISTS (SELECT 1 FROM units WHERE code='PB');
INSERT INTO units (code,name,unit_type)
SELECT 'PK','Poli Kandungan','POLI' WHERE NOT EXISTS (SELECT 1 FROM units WHERE code='PK');

INSERT INTO employees (employee_code,full_name,profession,role_id,unit_id,is_active)
SELECT 'PEG-DOK-003','dr. Raka Mahendra','Dokter',r.id,u.id,1 FROM roles r, units u
WHERE r.code='dokter' AND u.code='PU' AND NOT EXISTS (SELECT 1 FROM employees WHERE employee_code='PEG-DOK-003');
INSERT INTO employees (employee_code,full_name,profession,role_id,unit_id,is_active)
SELECT 'PEG-DOK-004','dr. Nadia Putri, Sp.PD','Dokter Sp.PD',r.id,u.id,1 FROM roles r, units u
WHERE r.code='dokter' AND u.code='PD' AND NOT EXISTS (SELECT 1 FROM employees WHERE employee_code='PEG-DOK-004');
INSERT INTO employees (employee_code,full_name,profession,role_id,unit_id,is_active)
SELECT 'PEG-DOK-005','dr. Salsabila Rahman, Sp.A','Dokter Sp.A',r.id,u.id,1 FROM roles r, units u
WHERE r.code='dokter' AND u.code='PA' AND NOT EXISTS (SELECT 1 FROM employees WHERE employee_code='PEG-DOK-005');
INSERT INTO employees (employee_code,full_name,profession,role_id,unit_id,is_active)
SELECT 'PEG-DOK-006','dr. Bima Aditya, Sp.B','Dokter Sp.B',r.id,u.id,1 FROM roles r, units u
WHERE r.code='dokter' AND u.code='PB' AND NOT EXISTS (SELECT 1 FROM employees WHERE employee_code='PEG-DOK-006');
INSERT INTO employees (employee_code,full_name,profession,role_id,unit_id,is_active)
SELECT 'PEG-DOK-007','dr. Citra Maharani, Sp.OG','Dokter Sp.OG',r.id,u.id,1 FROM roles r, units u
WHERE r.code='dokter' AND u.code='PK' AND NOT EXISTS (SELECT 1 FROM employees WHERE employee_code='PEG-DOK-007');
