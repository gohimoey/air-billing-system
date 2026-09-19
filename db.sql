-- ============================================================
-- Database: air_bill  (Sistem Pembayaran Tagihan Air)
-- ============================================================

CREATE DATABASE IF NOT EXISTS air_bill CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE air_bill;

-- TABEL USERS
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
  customer_id INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- TABEL CUSTOMERS
CREATE TABLE customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  name VARCHAR(100) NOT NULL,
  address TEXT NOT NULL,
  meter_number VARCHAR(50) NOT NULL UNIQUE,
  phone VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- TABEL METER READINGS
CREATE TABLE meter_readings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  reading_date DATE NOT NULL,
  reading_value INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABEL BILLS
CREATE TABLE bills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  period VARCHAR(7) NOT NULL,
  previous_reading INT NOT NULL DEFAULT 0,
  current_reading INT NOT NULL,
  usage_m3 INT NOT NULL,
  rate INT NOT NULL DEFAULT 5000,
  total INT NOT NULL,
  status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_customer_period (customer_id, period),
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABEL PAYMENTS
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bill_id INT NOT NULL,
  customer_id INT NOT NULL,
  amount INT NOT NULL,
  method ENUM('transfer','cash','ewallet') NOT NULL DEFAULT 'transfer',
  payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DATA AWAL
INSERT INTO users (username, password, name, role) VALUES
('admin', '$2y$12$GeNoBlYCjHGCxxEGb/fUzejW.CnWjxZF7T4/Yi.boNW3kXT3aop0S', 'Administrator', 'admin');

INSERT INTO customers (user_id, name, address, meter_number, phone) VALUES
(NULL, 'Budi Santoso', 'Jl. Melati No. 1, Jakarta', 'MB-001', '081234567890'),
(NULL, 'Siti Aminah', 'Jl. Anggrek No. 5, Jakarta', 'MB-002', '081298765432');

INSERT INTO users (username, password, name, role, customer_id) VALUES
('budi', '$2y$12$MNi/EptKM6JmsfUwu1cxo.wkTSYcdocBpsU.3SUpQ3X0th0k9TD7S', 'Budi Santoso', 'customer', 1);

INSERT INTO meter_readings (customer_id, reading_date, reading_value) VALUES
(1, '2026-07-05', 120),
(2, '2026-07-06', 85);

INSERT INTO bills (customer_id, period, previous_reading, current_reading, usage_m3, rate, total, status) VALUES
(1, '2026-07', 100, 120, 20, 5000, 100000, 'unpaid'),
(2, '2026-07', 70, 85, 15, 5000, 75000, 'unpaid');