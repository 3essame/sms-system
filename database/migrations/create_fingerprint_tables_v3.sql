-- Create fingerprint_shifts table
CREATE TABLE fingerprint_shifts (
    shift_type VARCHAR(1),
    datetime_in DATETIME,
    time_in VARCHAR(11),
    date_out DATE,
    time_out VARCHAR(11),
    shift VARCHAR(1),
    CONSTRAINT fingerprint_shifts_uk1 UNIQUE (shift, datetime_in),
    CONSTRAINT check_shift_value CHECK (shift IN ('A', 'B', 'C', 'D'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create fingerprint24 table
CREATE TABLE fingerprint24 (
    print_type VARCHAR(26) NOT NULL,
    print_time VARCHAR(26) NOT NULL,
    print_date DATE NOT NULL,
    civil_id DECIMAL(14,0) NOT NULL,
    id DECIMAL(12,0) NOT NULL,
    master_id DECIMAL(10,0),
    user_id DECIMAL(10,0),
    cr_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fingerprint24_pk PRIMARY KEY (id),
    CONSTRAINT check_print_type CHECK (print_type IN ('f1', 'f2'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create empinfo table
CREATE TABLE empinfo (
    civil_id DECIMAL(10,0) NOT NULL,
    fil_no DECIMAL(10,0),
    emp_name VARCHAR(100) NOT NULL,
    sec_id DECIMAL(10,0) NOT NULL,
    hire_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sex VARCHAR(8),
    card DECIMAL(10,0) DEFAULT 1,
    emp_id DECIMAL(10,0) NOT NULL,
    CONSTRAINT empinfo_pk PRIMARY KEY (civil_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
