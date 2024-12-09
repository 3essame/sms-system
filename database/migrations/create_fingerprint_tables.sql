-- جدول FINGERPRINT_SHIFTS
CREATE TABLE fingerprint_shifts (
    shift_type VARCHAR(1),
    datetime_in DATETIME,
    time_in VARCHAR(11),
    date_out DATE,
    time_out VARCHAR(11),
    shift VARCHAR(1),
    UNIQUE KEY unique_shift_datetime (shift, datetime_in),
    CONSTRAINT check_shift CHECK (shift IN ('A', 'B', 'C', 'D'))
);

-- جدول FINGERPRINT24
CREATE TABLE fingerprint24 (
    id BIGINT PRIMARY KEY,
    print_type VARCHAR(26) NOT NULL,
    print_time VARCHAR(26) NOT NULL,
    print_date DATE NOT NULL,
    civil_id BIGINT NOT NULL,
    master_id BIGINT,
    user_id BIGINT,
    cr_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT check_print_type CHECK (print_type IN ('f1', 'f2'))
);

-- جدول EMPINFO
CREATE TABLE empinfo (
    civil_id BIGINT PRIMARY KEY,
    fil_no BIGINT,
    emp_name VARCHAR(100) NOT NULL,
    sec_id BIGINT NOT NULL,
    hire_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sex VARCHAR(8),
    card INT DEFAULT 1,
    emp_id BIGINT NOT NULL
);
