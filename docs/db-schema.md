-- ============================================================
-- Tipan — Hospital Appointment + Records System
-- Complete Database Schema (PostgreSQL / Neon)
-- ============================================================

-- ----------------------------
-- Lookup Tables
-- ----------------------------

CREATE TABLE role (
    id           SERIAL      PRIMARY KEY,
    name         VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO role (name, display_name) VALUES
    ('patient', 'Patient'),
    ('doctor',  'Doctor'),
    ('admin',   'Admin');

CREATE TABLE gender (
    id           SERIAL      PRIMARY KEY,
    name         VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO gender (name, display_name) VALUES
    ('male',              'Male'),
    ('female',            'Female'),
    ('other',             'Other'),
    ('prefer_not_to_say', 'Prefer not to say');

CREATE TABLE specialization (
    id           SERIAL       PRIMARY KEY,
    name         VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL UNIQUE
);

INSERT INTO specialization (name, display_name) VALUES
    ('general_practice', 'General Practice'),
    ('cardiology',       'Cardiology'),
    ('dermatology',      'Dermatology'),
    ('neurology',        'Neurology'),
    ('orthopedics',      'Orthopedics'),
    ('pediatrics',       'Pediatrics'),
    ('psychiatry',       'Psychiatry'),
    ('radiology',        'Radiology'),
    ('surgery',          'Surgery'),
    ('oncology',         'Oncology');

CREATE TABLE status (
    id           SERIAL      PRIMARY KEY,
    name         VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO status (name, display_name) VALUES
    ('scheduled', 'Scheduled'),
    ('completed', 'Completed'),
    ('cancelled', 'Cancelled'),
    ('missed',    'Missed');

-- ----------------------------
-- Accounts
-- ----------------------------

CREATE TABLE "user" (
    id            UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash TEXT         NOT NULL,
    role_id       INT          NOT NULL REFERENCES role(id),
    created_at    TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE TABLE patient (
    id            UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id       UUID         NOT NULL UNIQUE REFERENCES "user"(id) ON DELETE CASCADE,
    first_name    VARCHAR(100) NOT NULL,
    last_name     VARCHAR(100) NOT NULL,
    date_of_birth DATE         NOT NULL,
    gender_id     INT          REFERENCES gender(id),
    phone         VARCHAR(20),
    address       TEXT
);

CREATE TABLE doctor (
    id                UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id           UUID         NOT NULL UNIQUE REFERENCES "user"(id) ON DELETE CASCADE,
    first_name        VARCHAR(100) NOT NULL,
    last_name         VARCHAR(100) NOT NULL,
    specialization_id INT          NOT NULL REFERENCES specialization(id),
    license_number    VARCHAR(100) NOT NULL UNIQUE,
    phone             VARCHAR(20)
);

CREATE TABLE admin (
    id         UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id    UUID         NOT NULL UNIQUE REFERENCES "user"(id) ON DELETE CASCADE,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL
);

-- ----------------------------
-- Scheduling
-- ----------------------------

CREATE TABLE schedule (
    id             UUID    PRIMARY KEY DEFAULT gen_random_uuid(),
    doctor_id      UUID    NOT NULL REFERENCES doctor(id) ON DELETE CASCADE,
    available_date DATE    NOT NULL,
    slot_start     TIME    NOT NULL,
    slot_end       TIME    NOT NULL,
    is_booked      BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT slot_order         CHECK (slot_end > slot_start),
    CONSTRAINT unique_doctor_slot UNIQUE (doctor_id, available_date, slot_start)
);

-- ----------------------------
-- Appointments
-- ----------------------------

CREATE TABLE appointment (
    id             UUID        PRIMARY KEY DEFAULT gen_random_uuid(),
    patient_id     UUID        NOT NULL REFERENCES patient(id)  ON DELETE RESTRICT,
    doctor_id      UUID        NOT NULL REFERENCES doctor(id)   ON DELETE RESTRICT,
    schedule_id    UUID        NOT NULL UNIQUE REFERENCES schedule(id) ON DELETE RESTRICT,
    appointment_dt TIMESTAMPTZ NOT NULL,
    status_id      INT         NOT NULL REFERENCES status(id)   DEFAULT 1,
    reason         TEXT,
    created_at     TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ----------------------------
-- Clinical Records
-- ----------------------------

CREATE TABLE medical_record (
    id             UUID        PRIMARY KEY DEFAULT gen_random_uuid(),
    patient_id     UUID        NOT NULL REFERENCES patient(id)     ON DELETE RESTRICT,
    appointment_id UUID        UNIQUE       REFERENCES appointment(id) ON DELETE SET NULL,
    doctor_id      UUID        NOT NULL REFERENCES doctor(id)      ON DELETE RESTRICT,
    diagnosis      TEXT,
    notes          TEXT,
    recorded_at    TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE prescription (
    id                UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    medical_record_id UUID         NOT NULL REFERENCES medical_record(id) ON DELETE CASCADE,
    medication_name   VARCHAR(255) NOT NULL,
    dosage            VARCHAR(100) NOT NULL,
    frequency         VARCHAR(100) NOT NULL,
    duration_days     INT          CHECK (duration_days > 0),
    instructions      TEXT
);