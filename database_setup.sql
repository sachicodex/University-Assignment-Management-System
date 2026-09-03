-- Run this in phpMyAdmin before using the app.
CREATE TABLE IF NOT EXISTS lecturers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    lecturer_email VARCHAR(150) NULL,
    deadline DATE NOT NULL
);

CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_email VARCHAR(150) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    submitted_at DATETIME NOT NULL,
    UNIQUE KEY one_submission_per_student (assignment_id, student_email)
);

-- Example manual inserts:
-- INSERT INTO lecturers (email, password) VALUES ('lecturer@school.com', '123456');
-- INSERT INTO students (email, password) VALUES ('student@school.com', '123456');
-- For hashed passwords, use: INSERT INTO lecturers (email, password) VALUES ('lecturer@school.com', '$2y$10$...');

-- For older databases that already have the assignments table but are missing lecturer_email:
-- ALTER TABLE assignments ADD COLUMN lecturer_email VARCHAR(150) NULL AFTER file_path;
