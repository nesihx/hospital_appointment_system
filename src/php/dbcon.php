<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "hastane_randevu";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    tc VARCHAR(11) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    role ENUM('patient', 'doctor', 'admin') NOT NULL DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($sql);

// Create doctors table
$sql = "CREATE TABLE IF NOT EXISTS doctors (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    department VARCHAR(50) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    education VARCHAR(255) NOT NULL,
    experience_years INT(11) NOT NULL,
    working_hours_start TIME NOT NULL,
    working_hours_end TIME NOT NULL,
    room_number VARCHAR(10) NOT NULL,
    image_url VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

$conn->query($sql);

// Create appointments table
$sql = "CREATE TABLE IF NOT EXISTS appointments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    doctor_id INT(11) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    notes TEXT,
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    patient_name VARCHAR(50) NOT NULL,
    patient_surname VARCHAR(50) NOT NULL,
    patient_tc VARCHAR(11) NOT NULL,
    patient_phone VARCHAR(15) NOT NULL,
    patient_email VARCHAR(100) NOT NULL,
    patient_birthdate DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
)";

$conn->query($sql);

// Insert default admin user if not exists
$admin_email = "admin@memorial.com";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$admin_tc = "99999999999";

$check_admin = "SELECT * FROM users WHERE email = ? OR tc = ?";
$stmt = $conn->prepare($check_admin);
$stmt->bind_param("ss", $admin_email, $admin_tc);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $insert_admin = "INSERT INTO users (name, surname, email, password, tc, phone, role) 
                    VALUES ('Admin', 'User', ?, ?, ?, '5555555555', 'admin')";
    $stmt = $conn->prepare($insert_admin);
    $stmt->bind_param("sss", $admin_email, $admin_password, $admin_tc);
    $stmt->execute();
}
?>