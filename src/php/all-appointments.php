<?php
session_start();
include 'dbcon.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get appointments for the logged-in user
// Join with users table to get patient name
$query = "SELECT a.*, u.name as patient_name, u.surname as patient_surname, 
          u.tc as patient_tc, u.phone as patient_phone, u.email as patient_email, 
          d.name as doctor_name, d.surname as doctor_surname, d.department as doctor_department
          FROM appointments a 
          JOIN users u ON a.patient_id = u.id 
          JOIN doctors d ON a.doctor_id = d.id
          WHERE a.patient_id = ? 
          ORDER BY a.appointment_date DESC, a.appointment_time ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle success or error messages
$message = '';
$message_type = '';

if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $message_type = 'success';
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $message_type = 'danger';
    unset($_SESSION['error']);
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevularım - Hastane Randevu Sistemi</title>
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
                    <h1>Randevularım</h1>
                    <a href="home.php" class="btn btn-outline-secondary me-2">
                         <i class="fas fa-arrow-left"></i> Geri Dön
                    </a>
                    <a href="logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt"></i> Çıkış Yap
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Randevu Listesi</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                <?php while ($appointment = $result->fetch_assoc()): ?>
                                    <div class="col">
                                        <div class="card appointment-card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <?php echo htmlspecialchars($appointment['doctor_name'] . ' ' . $appointment['doctor_surname']); ?>
                                                    <small class="text-muted">(<?php echo htmlspecialchars($appointment['doctor_department']); ?>)</small>
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
                                                
                                                <button type="button" class="btn btn-info btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#contactModal<?php echo $appointment['id']; ?>">
                                                    <i class="fas fa-user"></i> Hasta Bilgileri
                                                </button>

                                                <!-- Contact Info Modal -->
                                                <div class="modal fade" id="contactModal<?php echo $appointment['id']; ?>" tabindex="-1" aria-labelledby="contactModalLabel<?php echo $appointment['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="contactModalLabel<?php echo $appointment['id']; ?>">Hasta Bilgileri</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($appointment['patient_name'] . ' ' . $appointment['patient_surname']); ?></p>
                                                                <p><strong>T.C. Kimlik No:</strong> <?php echo htmlspecialchars($appointment['patient_tc']); ?></p>
                                                                <p><strong>Telefon:</strong> <?php echo htmlspecialchars($appointment['patient_phone']); ?></p>
                                                                <p><strong>E-posta:</strong> <?php echo htmlspecialchars($appointment['patient_email']); ?></p>
                                                                <?php if ($appointment['notes']): ?>
                                                                    <p><strong>Notlar:</strong> <?php echo htmlspecialchars($appointment['notes']); ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-3">
                                                    <a href="update.php?id=<?php echo $appointment['id']; ?>" class="btn btn-warning btn-sm me-2">
                                                        <i class="fas fa-edit"></i> Düzenle
                                                    </a>
                                                    <a href="delete.php?id=<?php echo $appointment['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu randevuyu silmek istediğinizden emin misiniz?');">
                                                        <i class="fas fa-trash-alt"></i> Sil
                                                    </a>
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