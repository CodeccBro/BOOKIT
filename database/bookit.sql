CREATE DATABASE IF NOT EXISTS bookit_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bookit_db;

CREATE TABLE IF NOT EXISTS bookings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(190) NULL,
    service VARCHAR(80) NOT NULL,
    notes TEXT NULL,
    booking_date DATE NOT NULL,
    booking_time VARCHAR(20) NOT NULL,
    status ENUM('upcoming','completed','cancelled') NOT NULL DEFAULT 'upcoming',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    active_slot VARCHAR(40)
      GENERATED ALWAYS AS (
        CASE WHEN status = 'cancelled' THEN NULL
        ELSE CONCAT(booking_date, ' ', booking_time) END
      ) STORED,
    UNIQUE KEY unique_active_booking_slot (active_slot),
    INDEX idx_booking_date (booking_date),
    INDEX idx_booking_status (status)
) ENGINE=InnoDB;
