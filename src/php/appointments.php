<?php
include 'dbcon.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle appointment cancellation
if (isset($_POST['cancel_appointment'])) {
    $appointment_id = $_POST['appointment_id'];
    $query = "UPDATE appointments SET status = 'cancelled' WHERE id = ? AND patient_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $appointment_id, $user_id);
    $stmt->execute();
}

// Get all appointments
$query = "SELECT a.*, d.name as doctor_name, d.surname as doctor_surname, d.department, d.specialization, d.image_url 
          FROM appointments a 
          JOIN doctors d ON a.doctor_id = d.id 
          WHERE a.patient_id = ? 
          ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$appointments = $stmt->get_result();

// Get appointment statistics
$query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
          FROM appointments 
          WHERE patient_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Randevularım - Memorial Sağlık Grubu</title>
    <link rel="icon" type="image/x-icon" href="../../src/img/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a5276;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
        }

        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        .nav-link.active {
            color: white !important;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') center/cover;
            opacity: 0.1;
        }

        .page-header-content {
            position: relative;
            z-index: 1;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .appointment-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border-left: 5px solid var(--secondary-color);
        }

        .appointment-card.pending {
            border-left-color: var(--warning-color);
        }

        .appointment-card.confirmed {
            border-left-color: var(--success-color);
        }

        .appointment-card.cancelled {
            border-left-color: var(--danger-color);
        }

        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .doctor-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 3px solid white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-badge.pending {
            background-color: var(--warning-color);
            color: #000;
        }

        .status-badge.confirmed {
            background-color: var(--success-color);
            color: white;
        }

        .status-badge.cancelled {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-cancel {
            background-color: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        .section-title {
            position: relative;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--secondary-color);
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .filter-btn {
            background: var(--light-bg);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .filter-btn:hover, .filter-btn.active {
            background: var(--secondary-color);
            color: white;
        }

        .no-appointments {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .no-appointments i {
            font-size: 4rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="home.php">
                <i class="fas fa-hospital"></i> Memorial Sağlık
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="doctors.php">Doktorlarımız</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="appointments.php">Randevularım</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container page-header-content">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 mb-3">Randevularım</h1>
                    <p class="lead mb-0">Tüm randevularınızı buradan görüntüleyebilir ve yönetebilirsiniz.</p>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#appointmentModal">
                        <i class="fas fa-calendar-plus"></i> Yeni Randevu Al
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-calendar-alt stats-icon text-primary"></i>
                    <h3><?php echo $stats['total']; ?></h3>
                    <p class="text-muted">Toplam Randevu</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-clock stats-icon text-warning"></i>
                    <h3><?php echo $stats['pending']; ?></h3>
                    <p class="text-muted">Bekleyen Randevu</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-check-circle stats-icon text-success"></i>
                    <h3><?php echo $stats['confirmed']; ?></h3>
                    <p class="text-muted">Onaylanan Randevu</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-times-circle stats-icon text-danger"></i>
                    <h3><?php echo $stats['cancelled']; ?></h3>
                    <p class="text-muted">İptal Edilen Randevu</p>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5 class="mb-3">Randevu Filtrele</h5>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">Tümü</button>
                <button class="filter-btn" data-filter="pending">Bekleyen</button>
                <button class="filter-btn" data-filter="confirmed">Onaylanan</button>
                <button class="filter-btn" data-filter="cancelled">İptal Edilen</button>
            </div>
        </div>

        <!-- Appointments List -->
        <div class="appointments-list">
            <?php if ($appointments->num_rows > 0): ?>
                <?php while ($appointment = $appointments->fetch_assoc()): ?>
                    <div class="appointment-card <?php echo $appointment['status']; ?>" data-status="<?php echo $appointment['status']; ?>">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="<?php echo $appointment['image_url'] ?: 'https://via.placeholder.com/60'; ?>" 
                                     alt="Doctor" class="doctor-image">
                            </div>
                            <div class="col">
                                <h5 class="mb-2">Dr. <?php echo htmlspecialchars($appointment['doctor_name'] . ' ' . $appointment['doctor_surname']); ?></h5>
                                <p class="mb-1">
                                    <i class="fas fa-stethoscope"></i> <?php echo htmlspecialchars($appointment['department']); ?> - 
                                    <?php echo htmlspecialchars($appointment['specialization']); ?>
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-calendar"></i> <?php echo date('d.m.Y', strtotime($appointment['appointment_date'])); ?> - 
                                    <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($appointment['appointment_time'])); ?>
                                </p>
                                <?php if ($appointment['notes']): ?>
                                    <p class="mb-0 text-muted">
                                        <i class="fas fa-comment"></i> <?php echo htmlspecialchars($appointment['notes']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <span class="status-badge <?php echo $appointment['status']; ?>">
                                    <?php
                                    switch($appointment['status']) {
                                        case 'pending':
                                            echo 'Beklemede';
                                            break;
                                        case 'confirmed':
                                            echo 'Onaylandı';
                                            break;
                                        case 'cancelled':
                                            echo 'İptal Edildi';
                                            break;
                                    }
                                    ?>
                                </span>
                                <?php if ($appointment['status'] !== 'cancelled'): ?>
                                    <form method="POST" class="mt-2" onsubmit="return confirm('Randevuyu iptal etmek istediğinizden emin misiniz?');">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <button type="submit" name="cancel_appointment" class="btn btn-cancel">
                                            <i class="fas fa-times"></i> İptal Et
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-appointments">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Henüz Randevunuz Bulunmuyor</h3>
                    <p class="text-muted">Yeni bir randevu almak için yukarıdaki butonu kullanabilirsiniz.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Appointment Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel">Yeni Randevu Oluştur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="appointmentForm" method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="doctor_id" class="form-label">Doktor Seçin</label>
                                <select class="form-select" id="doctor_id" name="doctor_id" required>
                                    <option value="">Doktor Seçin</option>
                                    <?php
                                    $doctors_query = "SELECT * FROM doctors ORDER BY department, name, surname";
                                    $doctors = $conn->query($doctors_query);
                                    while ($doctor = $doctors->fetch_assoc()): ?>
                                        <option value="<?php echo $doctor['id']; ?>">
                                            Dr. <?php echo htmlspecialchars($doctor['name'] . ' ' . $doctor['surname']); ?> - 
                                            <?php echo htmlspecialchars($doctor['department']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="appointment_date" class="form-label">Randevu Tarihi</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="appointment_time" class="form-label">Randevu Saati</label>
                                <select class="form-select" id="appointment_time" name="appointment_time" required>
                                    <option value="">Saat Seçin</option>
                                    <?php
                                    $start = strtotime('09:00');
                                    $end = strtotime('17:00');
                                    for ($time = $start; $time <= $end; $time += 1800) {
                                        echo '<option value="' . date('H:i:s', $time) . '">' . date('H:i', $time) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="notes" class="form-label">Notlar</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Randevu ile ilgili notlarınızı buraya yazabilirsiniz..."></textarea>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" name="create_appointment" class="btn btn-primary">
                                <i class="fas fa-calendar-plus"></i> Randevu Oluştur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            const date = document.getElementById('appointment_date').value;
            const time = document.getElementById('appointment_time').value;
            const doctor = document.getElementById('doctor_id').value;

            if (!date || !time || !doctor) {
                e.preventDefault();
                alert('Lütfen tüm gerekli alanları doldurun.');
            }
        });

        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                document.querySelectorAll('.appointment-card').forEach(card => {
                    if (filter === 'all' || card.dataset.status === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html> 