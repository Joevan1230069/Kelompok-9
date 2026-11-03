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
    CONSTRAINT fk_prr_unit FOREIGN KEY (unit_id) REFERENCES units(id),
    CONSTRAINT fk_prr_doctor FOREIGN KEY (doctor_employee_id) REFERENCES employees(id),
    CONSTRAINT fk_prr_schedule FOREIGN KEY (schedule_id) REFERENCES doctor_schedules(id),
    CONSTRAINT fk_prr_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_prr_registration FOREIGN KEY (registration_id) REFERENCES registrations(id),
    CONSTRAINT fk_prr_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO menus (label,icon,section_name,page_file,permission_code,sort_order,is_active)
SELECT 'Registrasi Online','▦','TRANSAKSI','patient_registration_requests.php','registration.manage',19,1
WHERE NOT EXISTS (SELECT 1 FROM menus WHERE page_file='patient_registration_requests.php');
