-- sql/schema.sql
-- Software Engineer Job Application Management System
-- Target: MariaDB 10.4 (bundled with XAMPP)

CREATE DATABASE IF NOT EXISTS swe_job_applications_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE swe_job_applications_db;

CREATE TABLE IF NOT EXISTS swe_applicants (
    id               INT          NOT NULL AUTO_INCREMENT,
    first_name       VARCHAR(100) NOT NULL,
    last_name        VARCHAR(100) NOT NULL,
    email            VARCHAR(150) NOT NULL,
    phone            VARCHAR(30)  NOT NULL,
    education        VARCHAR(150) NOT NULL,
    technical_skills TEXT         NOT NULL,
    experience_level VARCHAR(50)  NOT NULL,
    portfolio_url    VARCHAR(255) NULL DEFAULT NULL,
    github_url       VARCHAR(255) NOT NULL,
    date_added       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_swe_applicants_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data (fictional). Import this file once.
INSERT INTO swe_applicants
    (first_name, last_name, email, phone, education, technical_skills,
     experience_level, portfolio_url, github_url)
VALUES
    ('Maria', 'Santos', 'maria.santos@example.com', '+63 917 123 4567',
     'BS Computer Science, 4th Year',
     'PHP, MySQL, JavaScript, HTML/CSS, Git',
     'Internship', 'https://example.com/maria-santos',
     'https://github.com/sample-maria-santos'),
    ('Juan', 'Dela Cruz', 'juan.delacruz@example.com', '0918 765 4321',
     'BS Information Technology (Graduate)',
     'Java, Spring Boot, PostgreSQL, Docker',
     'Entry-Level', NULL,
     'https://github.com/sample-juan-delacruz'),
    ('Angela', 'Reyes', 'angela.reyes@example.com', '+63 920 555 0188',
     'MS Software Engineering',
     'Python, Django, AWS, CI/CD, REST API design',
     'Experienced', 'https://example.com/angela-reyes',
     'https://github.com/sample-angela-reyes');
