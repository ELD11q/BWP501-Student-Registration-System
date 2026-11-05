-- BWP501 - Student Registration System
-- Creates the database and the two required tables.
-- NOTE (online hosting): on hosts where the database is already created for you
-- (InfinityFree, Aiven, ...), delete the two lines CREATE DATABASE / USE and
-- import the rest into your existing database.

CREATE DATABASE IF NOT EXISTS student_registration_system
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_registration_system;

-- Users table (authentication)
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(100) NOT NULL UNIQUE,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- password_hash() result
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(100) NOT NULL,
    email            VARCHAR(100) NOT NULL,
    phone            VARCHAR(15)  NOT NULL,
    address          TEXT         NOT NULL,
    profile_picture  VARCHAR(255) NULL,          -- optional, path to the uploaded image
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
