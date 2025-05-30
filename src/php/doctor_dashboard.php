<?php
include 'dbcon.php';
session_start();

// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// Get doctor information
$user_id = $_SESSION['user_id'];
$query = "SELECT d.* FROM doctors d 
          JOIN users u ON d.id = u.doctor_id 
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    header("Location: login.php");
    exit();
}

// Get statistics
$today = date('Y-m-d');
$stats_query = "SELECT 
    COUNT(CASE WHEN appointment_date = ? THEN 1 END) as today_count,
    COUNT(CASE WHEN appointment_date > ? THEN 1 END) as upcoming_count,
    COUNT(CASE WHEN appointment_date < ? THEN 1 END) as past_count,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_count,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count
    FROM appointments WHERE doctor_id = ?";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("sssi", $today, $today, $today, $doctor['id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get weekly appointments data
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));
$weekly_query = "SELECT 
    DAYNAME(appointment_date) as day_name,
    COUNT(*) as appointment_count
    FROM appointments 
    WHERE doctor_id = ? 
    AND appointment_date BETWEEN ? AND ?
    GROUP BY appointment_date
    ORDER BY appointment_date";
$stmt = $conn->prepare($weekly_query);
$stmt->bind_param("iss", $doctor['id'], $week_start, $week_end);
$stmt->execute();
$weekly_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle appointment status updates
if (isset($_POST['update_status'])) {
    $appointment_id = $_POST['appointment_id'];
    $new_status = $_POST['status'];
    
    $update_query = "UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sii", $new_status, $appointment_id, $doctor['id']);
    
    if ($update_stmt->execute()) {
        echo "<script>alert('Randevu durumu güncellendi.');</script>";
    } else {
        echo "<script>alert('Randevu durumu güncellenirken bir hata oluştu.');</script>";
    }
}

// Get today's appointments
$query = "SELECT a.*, u.name as patient_name, u.surname as patient_surname, u.tc as patient_tc, u.phone as patient_phone, u.email as patient_email 
          FROM appointments a 
          JOIN users u ON a.patient_id = u.id 
          WHERE a.doctor_id = ? AND a.appointment_date = ? 
          ORDER BY a.appointment_time";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $doctor['id'], $today);
$stmt->execute();
$today_appointments = $stmt->get_result();

// Get upcoming appointments
$query = "SELECT a.*, u.name as patient_name, u.surname as patient_surname, u.tc as patient_tc, u.phone as patient_phone, u.email as patient_email 
          FROM appointments a 
          JOIN users u ON a.patient_id = u.id 
          WHERE a.doctor_id = ? AND a.appointment_date > ? 
          ORDER BY a.appointment_date, a.appointment_time";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $doctor['id'], $today);
$stmt->execute();
$upcoming_appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doktor Paneli - Memorial Sağlık Grubu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            background-color: rgba(255,255,255,0.1);
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

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: #666;
            font-size: 1.1rem;
        }

        .appointment-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .appointment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: var(--warning-color);
            color: white;
        }

        .status-confirmed {
            background-color: var(--success-color);
            color: white;
        }

        .status-cancelled {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            margin-left: 0.5rem;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
            background: var(--primary-color);
        }

        .patient-info {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .patient-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        .patient-details {
            flex: 1;
        }

        .patient-name {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .patient-contact {
            color: #666;
            font-size: 0.9rem;
        }

        .working-hours {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .working-hours-title {
            font-weight: bold;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .working-hours-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .working-hours-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .working-hours-list li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-hospital"></i> Memorial Sağlık
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user-md"></i> Dr. <?php echo htmlspecialchars($doctor['name'] . ' ' . $doctor['surname']); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Çıkış
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Hoş Geldiniz, Dr. <?php echo htmlspecialchars($doctor['name'] . ' ' . $doctor['surname']); ?></h1>
                    <p class="lead mb-0"><?php echo htmlspecialchars($doctor['department']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="working-hours">
                        <h5 class="working-hours-title">Çalışma Saatleri</h5>
                        <ul class="working-hours-list">
                            <li><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($doctor['working_hours_start'])); ?> - <?php echo date('H:i', strtotime($doctor['working_hours_end'])); ?></li>
                            <li><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($doctor['working_days']); ?></li>
                            <li><i class="fas fa-door-open"></i> Oda: <?php echo htmlspecialchars($doctor['room_number']); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Section -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-calendar-check stats-icon"></i>
                    <div class="stats-number"><?php echo $stats['today_count']; ?></div>
                    <div class="stats-label">Bugünkü Randevular</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-calendar-alt stats-icon"></i>
                    <div class="stats-number"><?php echo $stats['upcoming_count']; ?></div>
                    <div class="stats-label">Gelecek Randevular</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-clock stats-icon"></i>
                    <div class="stats-number"><?php echo $stats['pending_count']; ?></div>
                    <div class="stats-label">Bekleyen Randevular</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-check-circle stats-icon"></i>
                    <div class="stats-number"><?php echo $stats['confirmed_count']; ?></div>
                    <div class="stats-label">Onaylanan Randevular</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h4 class="section-title">Randevu Durumları</h4>
                    <canvas id="appointmentStatusChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h4 class="section-title">Haftalık Randevu Dağılımı</h4>
                    <canvas id="weeklyAppointmentsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="section-title">Bugünkü Randevular</h4>
                <?php if ($today_appointments->num_rows > 0): ?>
                    <?php while ($appointment = $today_appointments->fetch_assoc()): ?>
                        <div class="appointment-card">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="patient-info">
                                        <div class="patient-avatar">
                                            <?php echo strtoupper(substr($appointment['patient_name'], 0, 1)); ?>
                                        </div>
                                        <div class="patient-details">
                                            <div class="patient-name">
                                                <?php echo htmlspecialchars($appointment['patient_name'] . ' ' . $appointment['patient_surname']); ?>
                                            </div>
                                            <div class="patient-contact">
                                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-0">
                                        <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($appointment['appointment_time'])); ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-id-card"></i> TC: <?php echo htmlspecialchars($appointment['patient_tc']); ?>
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <?php if ($appointment['notes']): ?>
                                        <p class="mb-0">
                                            <i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars($appointment['notes']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3 text-end">
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
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
                                    <?php if ($appointment['status'] == 'pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" name="update_status" class="btn btn-success btn-action">
                                                <i class="fas fa-check"></i> Onayla
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" name="update_status" class="btn btn-danger btn-action">
                                                <i class="fas fa-times"></i> İptal Et
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Bugün için randevu bulunmamaktadır.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="section-title">Gelecek Randevular</h4>
                <?php if ($upcoming_appointments->num_rows > 0): ?>
                    <?php while ($appointment = $upcoming_appointments->fetch_assoc()): ?>
                        <div class="appointment-card">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="patient-info">
                                        <div class="patient-avatar">
                                            <?php echo strtoupper(substr($appointment['patient_name'], 0, 1)); ?>
                                        </div>
                                        <div class="patient-details">
                                            <div class="patient-name">
                                                <?php echo htmlspecialchars($appointment['patient_name'] . ' ' . $appointment['patient_surname']); ?>
                                            </div>
                                            <div class="patient-contact">
                                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-0">
                                        <i class="fas fa-calendar"></i> <?php echo date('d.m.Y', strtotime($appointment['appointment_date'])); ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($appointment['appointment_time'])); ?>
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <?php if ($appointment['notes']): ?>
                                        <p class="mb-0">
                                            <i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars($appointment['notes']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3 text-end">
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
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
                                    <?php if ($appointment['status'] == 'pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" name="update_status" class="btn btn-success btn-action">
                                                <i class="fas fa-check"></i> Onayla
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" name="update_status" class="btn btn-danger btn-action">
                                                <i class="fas fa-times"></i> İptal Et
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Gelecek randevu bulunmamaktadır.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Appointment Status Chart
        const statusCtx = document.getElementById('appointmentStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Bekleyen', 'Onaylanan', 'İptal Edilen'],
                datasets: [{
                    data: [
                        <?php echo $stats['pending_count']; ?>,
                        <?php echo $stats['confirmed_count']; ?>,
                        <?php echo $stats['cancelled_count']; ?>
                    ],
                    backgroundColor: [
                        '#f1c40f',
                        '#2ecc71',
                        '#e74c3c'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Weekly Appointments Chart
        const weeklyCtx = document.getElementById('weeklyAppointmentsChart').getContext('2d');
        new Chart(weeklyCtx, {
            type: 'bar',
            data: {
                labels: ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'],
                datasets: [{
                    label: 'Randevu Sayısı',
                    data: [
                        <?php 
                        $weekly_counts = array_fill(0, 7, 0);
                        foreach ($weekly_data as $data) {
                            $day_index = array_search($data['day_name'], ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
                            if ($day_index !== false) {
                                $weekly_counts[$day_index] = $data['appointment_count'];
                            }
                        }
                        echo implode(', ', $weekly_counts);
                        ?>
                    ],
                    backgroundColor: '#2980b9'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 