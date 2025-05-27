<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
include 'dbcon.php';

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    // Delete the appointment for the logged-in user
    $sql = "DELETE FROM appointments WHERE id = ? AND patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Randevu başarıyla silindi.";
        header("Location: all-appointments.php");
    } else {
        $_SESSION['error'] = "Randevu silinirken bir hata oluştu: " . $stmt->error;
        header("Location: all-appointments.php");
    }
} else {
    $_SESSION['error'] = "Silinecek randevu belirtilmedi.";
    header("Location: all-appointments.php");
}
exit();