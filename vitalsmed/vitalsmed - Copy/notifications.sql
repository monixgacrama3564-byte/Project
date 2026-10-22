-- ============================================================
-- VitalsMed — Notifications Module
-- Run this in phpMyAdmin or via MySQL CLI:
-- mysql -u root vitalsmed_db < notifications.sql
-- ============================================================

USE vitalsmed_db;

CREATE TABLE IF NOT EXISTS notifications (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT           DEFAULT NULL,        -- NULL = broadcast to all users
    type         ENUM('vital_alert','missed_medication','incident') NOT NULL,
    title        VARCHAR(150)  NOT NULL,
    message      TEXT          NOT NULL,
    patient_id   INT           DEFAULT NULL,
    ref_id       INT           DEFAULT NULL,        -- vital_signs.id or medication_adherence.id
    is_read      TINYINT(1)    NOT NULL DEFAULT 0,
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_notifications_user_read
    ON notifications(user_id, is_read, created_at);