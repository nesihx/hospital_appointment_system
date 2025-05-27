<?php
session_start();
include 'dbcon.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Collect and sanitize input data
$patient_id = $_SESSION['user_id'];
$doctor_id = filter_input(INPUT_POST, 'doctor', FILTER_SANITIZE_NUMBER_INT);
$appointment_date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
$appointment_time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
$notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

// Basic validation
if (empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
    $_SESSION['error'] = "Lütfen tüm gerekli alanları doldurun.";
    header("Location: appointment-booking.php");
    exit();
}

// Validate date format (YYYY-MM-DD)
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $appointment_date)) {
    $_SESSION['error'] = "Geçersiz tarih formatı.";
    header("Location: appointment-booking.php");
    exit();
}

// Validate time format (HH:MM)
if (!preg_match("/^\d{2}:\d{2}$/", $appointment_time)) {
    $appointment_time .= ':00';
    if (!preg_match("/^\d{2}:\d{2}:\d{2}$/", $appointment_time)) {
        $_SESSION['error'] = "Geçersiz saat formatı.";
        header("Location: appointment-booking.php");
        exit();
    }
}

// Check if the doctor exists and belongs to the selected department
$query = "SELECT working_hours_start, working_hours_end, working_days FROM doctors WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    $_SESSION['error'] = "Seçilen doktor bulunamadı.";
    header("Location: appointment-booking.php");
    exit();
}

// Check if the doctor is working on the selected date
$appointment_time_obj = new DateTime($appointment_time);
$start_time = new DateTime($doctor['working_hours_start']);
$end_time = new DateTime($doctor['working_hours_end']);

if ($appointment_time_obj < $start_time || $appointment_time_obj > $end_time) {
    $_SESSION['error'] = "Seçilen saat doktorun çalışma saatleri dışında.";
    header("Location: appointment-booking.php");
    exit();
}

// Check for existing appointment at the same time for the same doctor
$query = "SELECT id FROM appointments 
          WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? 
          AND status != 'cancelled' LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $doctor_id, $appointment_date, $appointment_time);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "Bu saatte doktorun başka bir randevusu bulunmaktadır. Lütfen farklı bir saat seçin.";
    header("Location: appointment-booking.php");
    exit();
}

// Insert the new appointment
$query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, notes, status) 
          VALUES (?, ?, ?, ?, ?, 'pending')";

$stmt = $conn->prepare($query);
$stmt->bind_param("iisss", $patient_id, $doctor_id, $appointment_date, $appointment_time, $notes);

if ($stmt->execute()) {
    // Send confirmation email (requires mail server setup)
    // $to = $patient_email; // You would need to fetch patient email
    // $subject = "Randevu Onayı";
    // $message_body = "Randevunuz başarıyla oluşturuldu. Detaylar: ...";
    // mail($to, $subject, $message_body);

    $_SESSION['success'] = "Randevunuz başarıyla oluşturuldu!";
    header("Location: all-appointments.php");
    exit();
} else {
    $_SESSION['error'] = "Randevu oluşturulurken bir hata oluştu: " . $stmt->error;
    header("Location: appointment-booking.php");
    exit();
}

$stmt->close();
$conn->close();
?> 