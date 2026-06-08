-- ============================================================
-- PHP Professional Reservation System - Database Schema
-- Version: 1.0.0
-- Compatible with: MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS reservation_system
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE reservation_system;

-- ------------------------------------------------------------
-- Services / Resources table (what can be reserved)
-- E.g. "Conference Room A", "Dr. Smith - Consultation", 
--      "VIP Suite", "Hotel Room 101", etc.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS services (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    description     TEXT,
    category        VARCHAR(100) DEFAULT NULL,
    duration_minutes INT UNSIGNED DEFAULT 60,
    max_capacity    INT UNSIGNED DEFAULT 1,
    price           DECIMAL(10,2) DEFAULT 0.00,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Customers / Clients table
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS customers (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(255) NOT NULL,
    phone           VARCHAR(50) DEFAULT NULL,
    company         VARCHAR(255) DEFAULT NULL,
    notes           TEXT DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_customers_email ON customers(email);

-- ------------------------------------------------------------
-- Reservations table (core)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reservations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id     INT UNSIGNED NOT NULL,
    service_id      INT UNSIGNED DEFAULT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    end_time        TIME DEFAULT NULL,
    guests          INT UNSIGNED DEFAULT 1,
    status          ENUM('pending','confirmed','completed','cancelled','no_show') 
                    NOT NULL DEFAULT 'pending',
    notes           TEXT DEFAULT NULL,
    confirmation_code VARCHAR(32) DEFAULT NULL,
    source          VARCHAR(50) DEFAULT 'web',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_reservations_date ON reservations(reservation_date);
CREATE INDEX idx_reservations_status ON reservations(status);
CREATE INDEX idx_reservations_code ON reservations(confirmation_code);

-- ------------------------------------------------------------
-- Availability exceptions (blocked dates / time slots)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS availability_exceptions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id      INT UNSIGNED DEFAULT NULL,
    exception_date  DATE NOT NULL,
    start_time      TIME DEFAULT NULL,
    end_time        TIME DEFAULT NULL,
    is_available    TINYINT(1) NOT NULL DEFAULT 0,
    reason          VARCHAR(255) DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (service_id) REFERENCES services(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Settings table
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_key     VARCHAR(100) PRIMARY KEY,
    setting_value   TEXT NOT NULL,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Admin users table (simple auth)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(100) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    email           VARCHAR(255) DEFAULT NULL,
    role            ENUM('admin','super_admin') NOT NULL DEFAULT 'admin',
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    last_login      TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Sample Data
-- ============================================================

INSERT INTO services (name, description, category, duration_minutes, max_capacity, price) VALUES
('Standard Consultation', 'General consultation session with our expert team', 'Consultation', 30, 1, 0.00),
('Premium Consultation', 'In-depth consultation with senior specialist', 'Consultation', 60, 1, 150.00),
('Conference Room A', 'Fully equipped conference room with projector and video conferencing', 'Room', 60, 12, 200.00),
('Meeting Room B', 'Private meeting room ideal for small teams', 'Room', 60, 6, 100.00),
('VIP Lounge', 'Premium VIP lounge experience with full amenities', 'VIP', 120, 4, 500.00);

INSERT INTO settings (setting_key, setting_value) VALUES
('business_name', 'Your Business Name'),
('business_email', 'notifications@example.com'),
('business_phone', '+966 55 123 4567'),
('operating_hours', '{"mon":{"open":"09:00","close":"18:00"},"tue":{"open":"09:00","close":"18:00"},"wed":{"open":"09:00","close":"18:00"},"thu":{"open":"09:00","close":"18:00"},"fri":{"open":"10:00","close":"16:00"},"sat":{"open":"10:00","close":"14:00"},"sun":{"open":"closed","close":"closed"}}'),
('time_slot_interval', '30'),
('max_advance_days', '90'),
('min_notice_hours', '2'),
('enable_email_notifications', 'true');

-- Default admin: admin / admin123 (CHANGE IN PRODUCTION!)
INSERT INTO admin_users (username, password_hash, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'super_admin');
-- Password hash above is for "password" — change immediately
