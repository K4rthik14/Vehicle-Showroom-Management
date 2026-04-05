-- ============================================================
-- VSMS - Vehicle Showroom Management System
-- Database Setup Script
-- Run this in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS vsms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vsms;

-- Users (for login)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    emp_id VARCHAR(10) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (emp_id) REFERENCES STAFF(Emp_ID) ON DELETE SET NULL
) ENGINE=InnoDB;

-- CUSTOMER
CREATE TABLE IF NOT EXISTS CUSTOMER (
    Customer_ID INT AUTO_INCREMENT PRIMARY KEY,
    Fname VARCHAR(50) NOT NULL,
    Lname VARCHAR(50) NOT NULL,
    Phone VARCHAR(15) NOT NULL,
    Email VARCHAR(100),
    Address TEXT
) ENGINE=InnoDB;

-- STAFF
CREATE TABLE IF NOT EXISTS STAFF (
    Emp_ID VARCHAR(10) PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Designation VARCHAR(50),
    Contact VARCHAR(15),
    DOJ DATE
) ENGINE=InnoDB;

-- SUPPLIER
CREATE TABLE IF NOT EXISTS SUPPLIER (
    Supplier_ID INT AUTO_INCREMENT PRIMARY KEY,
    S_Name VARCHAR(100) NOT NULL,
    Phone_No VARCHAR(15),
    Email VARCHAR(100)
) ENGINE=InnoDB;

-- VEHICLE
CREATE TABLE IF NOT EXISTS VEHICLE (
    VIN VARCHAR(20) PRIMARY KEY,
    Model_Name VARCHAR(100) NOT NULL,
    Status ENUM('Available','Sold') DEFAULT 'Available',
    Fuel_type ENUM('Petrol','Diesel','Electric','Hybrid','CNG') NOT NULL,
    Stock_Quantity INT DEFAULT 0,
    Supplier_ID INT,
    FOREIGN KEY (Supplier_ID) REFERENCES SUPPLIER(Supplier_ID) ON DELETE SET NULL
) ENGINE=InnoDB;

-- SALE
CREATE TABLE IF NOT EXISTS SALE (
    Sale_ID INT AUTO_INCREMENT PRIMARY KEY,
    VIN VARCHAR(20),
    Emp_ID VARCHAR(10),
    Customer_ID INT,
    Sale_Date DATE NOT NULL,
    Amount DECIMAL(12,2) NOT NULL,
    Payment_Method ENUM('Cash','Card','UPI') NOT NULL,
    FOREIGN KEY (VIN) REFERENCES VEHICLE(VIN) ON DELETE SET NULL,
    FOREIGN KEY (Emp_ID) REFERENCES STAFF(Emp_ID) ON DELETE SET NULL,
    FOREIGN KEY (Customer_ID) REFERENCES CUSTOMER(Customer_ID) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

INSERT INTO SUPPLIER (S_Name, Phone_No, Email) VALUES
('Maruti Suzuki India Ltd', '9400000001', 'supply@maruti.in'),
('Hyundai Motor India', '9400000002', 'supply@hyundai.in'),
('Tata Motors Ltd', '9400000003', 'supply@tata.in');

INSERT INTO VEHICLE (VIN, Model_Name, Status, Fuel_type, Stock_Quantity, Supplier_ID) VALUES
('WDB1234567890001', 'Maruti Swift', 'Available', 'Petrol', 5, 1),
('WDB1234567890002', 'Hyundai i20', 'Available', 'Petrol', 3, 2),
('WDB1234567890003', 'Tata Nexon EV', 'Available', 'Electric', 2, 3),
('WDB1234567890004', 'Maruti Baleno', 'Sold', 'Petrol', 0, 1),
('WDB1234567890005', 'Hyundai Creta', 'Available', 'Diesel', 4, 2);

INSERT INTO STAFF (Emp_ID, Name, Designation, Contact, DOJ) VALUES
('EMP001', 'Rahul Menon', 'Sales Manager', '9876543210', '2023-06-01'),
('EMP002', 'Priya Nair', 'Sales Executive', '9876543211', '2024-01-15'),
('EMP003', 'Arjun Kumar', 'Accounts Officer', '9876543212', '2023-09-10');

INSERT INTO CUSTOMER (Fname, Lname, Phone, Email, Address) VALUES
('Anil', 'Sharma', '9123456701', 'anil@email.com', '12, MG Road, Kannur'),
('Sreeja', 'Pillai', '9123456702', 'sreeja@email.com', '45, Beach Road, Kozhikode'),
('Mohammed', 'Rafiq', '9123456703', 'rafiq@email.com', '7, Civil Lines, Thrissur');

INSERT INTO SALE (VIN, Emp_ID, Customer_ID, Sale_Date, Amount, Payment_Method) VALUES
('WDB1234567890004', 'EMP001', 1, '2026-01-10', 750000.00, 'Card'),
('WDB1234567890002', 'EMP002', 2, '2026-02-14', 920000.00, 'UPI');

-- Admin user (password: admin123)
INSERT INTO users (password, role, emp_id) VALUES
('$2y$12$J1IAVBB8xWG8qZc1ub81Leanm3f7HNtLo3uPGzHA2Wi1CiB4fUCqq', 'admin', 'EMP001');
-- Staff user (password: staff123)
INSERT INTO users (password, role, emp_id) VALUES
('$2y$12$/k3BAHrw18PS5NQQMRF3WuaVV.vI.jV1wg5GGZBapd/AtDr1NxQ/m', 'staff', 'EMP002');

DELIMITER //
CREATE TRIGGER before_staff_insert
BEFORE INSERT ON STAFF
FOR EACH ROW
BEGIN
  IF NEW.Emp_ID IS NULL OR NEW.Emp_ID = '' THEN
    SET NEW.Emp_ID = CONCAT('EMP', LPAD((SELECT COUNT(*)+1 FROM STAFF), 3, '0'));
  END IF;
END;
//
DELIMITER ;
