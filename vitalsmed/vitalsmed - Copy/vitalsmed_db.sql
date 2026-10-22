-- ============================================================
-- VitalsMed Database Schema
-- Import: mysql -u root vitalsmed_db < vitalsmed_db.sql
-- Or via phpMyAdmin: create DB first, then import this file
-- ============================================================

CREATE DATABASE IF NOT EXISTS vitalsmed_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vitalsmed_db;

-- ============================================================
-- M1: Users (Secure Access & RBAC)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,           -- bcrypt hash
    role        ENUM('admin','provider') NOT NULL DEFAULT 'provider',
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- M2: Patients (Patient Management System)
-- ============================================================
CREATE TABLE IF NOT EXISTS patients (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    patient_code    VARCHAR(20)  NOT NULL UNIQUE,
    full_name       VARCHAR(100) NOT NULL,
    date_of_birth   DATE         NOT NULL,
    gender          ENUM('Male','Female','Other') NOT NULL,
    contact_number  VARCHAR(20)  DEFAULT NULL,
    address         TEXT         DEFAULT NULL,
    medical_history TEXT         DEFAULT NULL,
    assigned_to     INT          DEFAULT NULL,
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- M3: Vital Signs (Logic Engine)
-- ============================================================
CREATE TABLE IF NOT EXISTS vital_signs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    patient_id      INT          NOT NULL,
    recorded_by     INT          NOT NULL,
    temperature     DECIMAL(4,1) NOT NULL,
    systolic_bp     SMALLINT     NOT NULL,
    diastolic_bp    SMALLINT     NOT NULL,
    heart_rate      SMALLINT     NOT NULL,
    oxygen_sat      DECIMAL(4,1) DEFAULT NULL,
    alert_flag      TINYINT(1)   NOT NULL DEFAULT 0,
    alert_reason    VARCHAR(255) DEFAULT NULL,
    notes           TEXT         DEFAULT NULL,
    recorded_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- ============================================================
-- M4: Medication Schedules & Adherence
-- ============================================================
CREATE TABLE IF NOT EXISTS medication_schedules (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    patient_id      INT          NOT NULL,
    medication_name VARCHAR(150) NOT NULL,
    dosage          VARCHAR(100) NOT NULL,
    scheduled_time  TIME         NOT NULL,
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS medication_adherence (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id        INT          NOT NULL,
    patient_id         INT          NOT NULL,
    confirmed_by       INT          NOT NULL,
    scheduled_datetime DATETIME     NOT NULL,
    confirmed_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status             ENUM('taken','late','missed') NOT NULL,
    notes              TEXT         DEFAULT NULL,
    FOREIGN KEY (schedule_id)  REFERENCES medication_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id)   REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmed_by) REFERENCES users(id)
);

-- ============================================================
-- M5: Incident Reports & Analytics
-- ============================================================
CREATE TABLE IF NOT EXISTS incident_reports (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    patient_id      INT          NOT NULL,
    reported_by     INT          NOT NULL,
    severity        ENUM('low','moderate','high','critical') NOT NULL,
    description     TEXT         NOT NULL,
    action_taken    TEXT         DEFAULT NULL,
    resolved        TINYINT(1)   NOT NULL DEFAULT 0,
    reported_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at     DATETIME     DEFAULT NULL,
    FOREIGN KEY (patient_id)  REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id)
);