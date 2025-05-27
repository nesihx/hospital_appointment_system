<?php
session_start();
include 'dbcon.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Get doctor's appointments
$query = "SELECT a.*, u.name as patient_name, u.email as patient_email 
          FROM appointments a 
          JOIN users u ON a.patient_id = u.id 
          WHERE a.doctor_id = ? 
          ORDER BY a.appointment_date DESC, a.appointment_time ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['doctor_id']);
$stmt->execute();
$result = $stmt->get_result();

// Get today's appointments
$today = date('Y-m-d');
$query_today = "SELECT COUNT(*) as count FROM appointments 
                WHERE doctor_id = ? AND appointment_date = ? AND status != 'cancelled'";
$stmt_today = $conn->prepare($query_today);
$stmt_today->bind_param("is", $_SESSION['doctor_id'], $today);
$stmt_today->execute();
$today_count = $stmt_today->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doktor Paneli - Hastane Randevu Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .appointment-card {
            transition: transform 0.2s;
        }
        .appointment-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            font-size: 0.8em;
        }
    </style>
</head>
<body data-bs-theme="dark">
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Doktor Paneli</h1>
                    <a href="logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt"></i> Çıkış Yap
                    </a>
                </div>

                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Bugünkü Randevular</h5>
                                <p class="card-text display-4"><?php echo $today_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointments List -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Randevularım</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                <?php while ($appointment = $result->fetch_assoc()): ?>
                                    <div class="col">
                                        <div class="card appointment-card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <?php echo htmlspecialchars($appointment['patient_name']); ?>
                                                </h5>
                                                <div class="mb-2">
                                                    <span class="badge bg-<?php 
                                                        echo $appointment['status'] === 'confirmed' ? 'success' : 
                                                            ($appointment['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                                    ?> status-badge">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </div>
                                                <p class="card-text">
                                                    <i class="fas fa-calendar"></i> 
                                                    <?php echo date('d.m.Y', strtotime($appointment['appointment_date'])); ?>
                                                </p>
                                                <p class="card-text">
                                                    <i class="fas fa-clock"></i> 
                                                    <?php echo date('H:i', strtotime($appointment['appointment_time'])); ?>
                                                </p>
                                                <p class="card-text">
                                                    <i class="fas fa-envelope"></i> 
                                                    <?php echo htmlspecialchars($appointment['patient_email']); ?>
                                                </p>
                                                <?php if ($appointment['notes']): ?>
                                                    <p class="card-text">
                                                        <i class="fas fa-sticky-note"></i> 
                                                        <?php echo htmlspecialchars($appointment['notes']); ?>
                                                    </p>
                                                <?php endif; ?>

                                                <div class="mt-3">
                                                    <?php if ($appointment['status'] === 'pending'): ?>
                                                        <form method="POST" action="update-appointment-status.php" class="d-inline">
                                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                            <input type="hidden" name="status" value="confirmed">
                                                            <button type="submit" class="btn btn-success btn-sm">
                                                                <i class="fas fa-check"></i> Onayla
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="update-appointment-status.php" class="d-inline">
                                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                            <input type="hidden" name="status" value="cancelled">
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-times"></i> İptal Et
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Henüz randevunuz bulunmamaktadır.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 