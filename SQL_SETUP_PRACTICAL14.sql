-- Select the existing Studenthub database before running this file.
-- Run ALTER statements only if those columns do not already exist.

ALTER TABLE Stu_Register ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'student';
ALTER TABLE Stu_Register ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS Events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(150) NOT NULL,
    organization VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    fees DECIMAL(10,2) NOT NULL DEFAULT 0,
    venue VARCHAR(200) NOT NULL
);

CREATE TABLE IF NOT EXISTS Event_Registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    event_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    details VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- If you already created Events with only event_name, event_date and venue,
-- run these separately only if organization and fees do not exist:
-- ALTER TABLE Events ADD COLUMN organization VARCHAR(150) NOT NULL DEFAULT 'StudentHub';
-- ALTER TABLE Events ADD COLUMN fees DECIMAL(10,2) NOT NULL DEFAULT 0;
