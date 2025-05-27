<?php
session_start();
include 'dbcon.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Check if required parameters are set
if (!isset($_POST['appointment_id']) || !isset($_POST['status'])) {
    $_SESSION['error'] = "Geçersiz istek!";
    header("Location: doctor-panel.php");
    exit();
}

$appointment_id = $_POST['appointment_id'];
$status = $_POST['status'];

// Validate status
if (!in_array($status, ['confirmed', 'cancelled'])) {
    $_SESSION['error'] = "Geçersiz durum!";
    header("Location: doctor-panel.php");
    exit();
}

// Update appointment status
$query = "UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $status, $appointment_id, $_SESSION['doctor_id']);

if ($stmt->execute()) {
    $_SESSION['success'] = "Randevu durumu başarıyla güncellendi.";
} else {
    $_SESSION['error'] = "Randevu durumu güncellenirken bir hata oluştu.";
}

header("Location: doctor-panel.php");
exit(); 