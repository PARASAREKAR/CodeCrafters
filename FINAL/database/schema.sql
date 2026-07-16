-- ============================================================
-- Online Event Registration System - Database Schema
-- ============================================================
-- Run this script in MySQL to set up the database and tables.
-- Default admin credentials: admin@eventreg.com / EventHub@Admin2026!
-- ============================================================

CREATE DATABASE IF NOT EXISTS event_registration_db;
USE event_registration_db;

-- -----------------------------------------------------------
-- Users table: stores all system users (Admin, Organizer, Participant)
-- -----------------------------------------------------------
CREATE TABLE users (
    User_ID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Mobile VARCHAR(15),
    Password VARCHAR(255) NOT NULL,
    Role VARCHAR(20) NOT NULL DEFAULT 'Participant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (Email),
    INDEX idx_role (Role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Events table: stores event details created by organizers/admins
-- -----------------------------------------------------------
CREATE TABLE events (
    Event_ID INT PRIMARY KEY AUTO_INCREMENT,
    Event_Name VARCHAR(100) NOT NULL,
    Description TEXT,
    Venue VARCHAR(100),
    Event_Date DATE NOT NULL,
    Event_Time TIME,
    Organizer VARCHAR(100),
    Capacity INT NOT NULL DEFAULT 100,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_date (Event_Date),
    INDEX idx_event_name (Event_Name),
    FOREIGN KEY (created_by) REFERENCES users(User_ID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Registrations table: links users to events they registered for
-- -----------------------------------------------------------
CREATE TABLE registrations (
    Registration_ID INT PRIMARY KEY AUTO_INCREMENT,
    User_ID INT NOT NULL,
    Event_ID INT NOT NULL,
    Registration_Date DATE NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'Confirmed',
    College_Organization VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (User_ID),
    INDEX idx_event (Event_ID),
    INDEX idx_status (Status),
    FOREIGN KEY (User_ID) REFERENCES users(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Event_ID) REFERENCES events(Event_ID) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (User_ID, Event_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Attendance table: tracks presence at registered events
-- -----------------------------------------------------------
CREATE TABLE attendance (
    Attendance_ID INT PRIMARY KEY AUTO_INCREMENT,
    Registration_ID INT NOT NULL,
    Status ENUM('Present', 'Absent') NOT NULL DEFAULT 'Absent',
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Registration_ID) REFERENCES registrations(Registration_ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- Insert default admin account
-- Password: EventHub@Admin2026! (bcrypt hashed)
-- -----------------------------------------------------------
INSERT INTO users (Name, Email, Mobile, Password, Role) VALUES
('Admin', 'admin@eventreg.com', '9999999999', '$2y$10$4Xe5pFi3CcR8fPwCKW/XH.zONGSMZXwg4a/AozP.GfaFjM7oEQR6y', 'Admin');
