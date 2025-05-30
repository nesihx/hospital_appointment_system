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

// Handle appointment creation
if (isset($_POST['create_appointment'])) {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $notes = $_POST['notes'];
    
    // Hasta bilgileri
    $patient_name = $_POST['patient_name'];
    $patient_surname = $_POST['patient_surname'];
    $patient_tc = $_POST['patient_tc'];
    $patient_phone = $_POST['patient_phone'];
    $patient_email = $_POST['patient_email'];
    $patient_birthdate = $_POST['patient_birthdate'];

    // Check if the time slot is available
    $check_query = "SELECT * FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("iss", $doctor_id, $appointment_date, $appointment_time);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('Bu randevu saati dolu. Lütfen başka bir saat seçin.');</script>";
    } else {
        // Create the appointment
        $insert_query = "INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, notes, status, 
                        patient_name, patient_surname, patient_tc, patient_phone, patient_email, patient_birthdate) 
                        VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iissssssssss", 
            $user_id, 
            $doctor_id, 
            $appointment_date, 
            $appointment_time, 
            $notes,
            $patient_name,
            $patient_surname,
            $patient_tc,
            $patient_phone,
            $patient_email,
            $patient_birthdate
        );

        if ($insert_stmt->execute()) {
            echo "<script>alert('Randevunuz başarıyla oluşturuldu.'); window.location.href='appointments.php';</script>";
        } else {
            echo "<script>alert('Randevu oluşturulurken bir hata oluştu.');</script>";
        }
    }
}

// Get all doctors
$query = "SELECT * FROM doctors ORDER BY department, name, surname";
$doctors = $conn->query($query);

// Get departments for filter
$query = "SELECT DISTINCT department FROM doctors ORDER BY department";
$departments = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Doktorlarımız - Memorial Sağlık Grubu</title>
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

        .search-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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

        .doctor-card {
            background: white;
            border-radius: 15px;
            padding: 1.2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .doctor-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 15px auto;
            display: block;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border: 4px solid white;
        }

        .department-badge {
            background: var(--secondary-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 0.8rem;
        }

        .specialization-badge {
            background: var(--primary-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 0.8rem;
        }

        .info-item {
            margin-bottom: 0.4rem;
            color: var(--dark-text);
            font-size: 0.9rem;
        }

        .info-item i {
            width: 20px;
            color: var(--secondary-color);
            margin-right: 0.5rem;
        }

        .btn-appointment {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 0.8rem;
        }

        .btn-appointment:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
            color: white;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .no-results i {
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
                        <a class="nav-link active" href="doctors.php">Doktorlarımız</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">Randevularım</a>
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
                    <h1 class="display-4 mb-3">Doktorlarımız</h1>
                    <p class="lead mb-0">Alanında uzman doktorlarımızla tanışın ve randevu alın.</p>
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
        <!-- Search Section -->
        <div class="search-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" 
                               placeholder="Doktor adı veya uzmanlık alanı ile arayın...">
                    </div>
                </div>
                <div class="col-md-6">
                    <select class="form-select" id="departmentFilter">
                        <option value="">Tüm Bölümler</option>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($dept['department']); ?>">
                                <?php echo htmlspecialchars($dept['department']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Doctors Grid -->
        <div class="row" id="doctorsGrid">
            <?php while ($doctor = $doctors->fetch_assoc()): ?>
                <div class="col-md-4 doctor-item" 
                     data-name="<?php echo strtolower($doctor['name'] . ' ' . $doctor['surname']); ?>"
                     data-specialization="<?php echo strtolower($doctor['specialization']); ?>"
                     data-department="<?php echo strtolower($doctor['department']); ?>">
                    <div class="doctor-card">
                        <img src="<?php 
                            $image_url = $doctor['image_url'];
                            if (empty($image_url)) {
                                switch($doctor['department']) {
                                    case 'Kardiyoloji':
                                        $image_url = 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80';
                                        break;
                                    case 'Nöroloji':
                                        $image_url = 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80';
                                        break;
                                    case 'Ortopedi':
                                        $image_url = 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80';
                                        break;
                                    case 'Göz Hastalıkları':
                                        $image_url = 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80';
                                        break;
                                    case 'Kulak Burun Boğaz':
                                        $image_url = 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80';
                                        break;
                                    case 'Dahiliye':
                                        $image_url = 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80';
                                        break;
                                    case 'Dermatoloji':
                                        $image_url = 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80';
                                        break;
                                    case 'Üroloji':
                                        $image_url = 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80';
                                        break;
                                    case 'Kadın Hastalıkları':
                                        $image_url = 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80';
                                        break;
                                    case 'Psikiyatri':
                                        $image_url = 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80';
                                        break;
                                    default:
                                        $image_url = 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=300&q=80';
                                }
                            }
                            echo $image_url;
                        ?>" 
                             alt="Doctor" class="doctor-image">
                        <div class="text-center">
                            <span class="department-badge"><?php echo htmlspecialchars($doctor['department']); ?></span>
                            <span class="specialization-badge"><?php echo htmlspecialchars($doctor['specialization']); ?></span>
                            <h4 class="mt-3 mb-2">Dr. <?php echo htmlspecialchars($doctor['name'] . ' ' . $doctor['surname']); ?></h4>
                            <div class="info-item">
                                <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($doctor['education']); ?>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-briefcase"></i> <?php echo $doctor['experience_years']; ?> Yıl Deneyim
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($doctor['working_hours_start'])); ?> - 
                                <?php echo date('H:i', strtotime($doctor['working_hours_end'])); ?>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-door-open"></i> Oda <?php echo $doctor['room_number']; ?>
                            </div>
                            <button class="btn btn-appointment" onclick="selectDoctor(<?php echo $doctor['id']; ?>)">
                                <i class="fas fa-calendar-plus"></i> Randevu Al
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- No Results -->
        <div class="no-results" style="display: none;">
            <i class="fas fa-search"></i>
            <h3>Sonuç Bulunamadı</h3>
            <p class="text-muted">Arama kriterlerinize uygun doktor bulunamadı.</p>
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
                        <input type="hidden" id="selected_doctor_id" name="doctor_id">
                        
                        <!-- Hasta Bilgileri -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-user-circle me-2"></i>Hasta Bilgileri</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="patient_name" class="form-label">Ad</label>
                                        <input type="text" class="form-control" id="patient_name" name="patient_name" 
                                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="patient_surname" class="form-label">Soyad</label>
                                        <input type="text" class="form-control" id="patient_surname" name="patient_surname" 
                                               value="<?php echo htmlspecialchars($user['surname']); ?>" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="patient_tc" class="form-label">T.C. Kimlik No</label>
                                        <input type="text" class="form-control" id="patient_tc" name="patient_tc" 
                                               value="<?php echo htmlspecialchars($user['tc']); ?>" 
                                               pattern="[0-9]{11}" maxlength="11" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="patient_phone" class="form-label">Telefon</label>
                                        <input type="tel" class="form-control" id="patient_phone" name="patient_phone" 
                                               value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="patient_email" class="form-label">E-posta</label>
                                        <input type="email" class="form-control" id="patient_email" name="patient_email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="patient_birthdate" class="form-label">Doğum Tarihi</label>
                                        <input type="date" class="form-control" id="patient_birthdate" name="patient_birthdate" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Randevu Bilgileri -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Randevu Bilgileri</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="appointment_date" class="form-label">Randevu Tarihi</label>
                                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                               min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
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
                                </div>
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Şikayet ve Notlar</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                                              placeholder="Randevu sebebinizi ve varsa özel notlarınızı buraya yazabilirsiniz..."></textarea>
                                </div>
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
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const departmentFilter = document.getElementById('departmentFilter');
        const doctorsGrid = document.getElementById('doctorsGrid');
        const noResults = document.querySelector('.no-results');

        function filterDoctors() {
            const searchTerm = searchInput.value.toLowerCase();
            const department = departmentFilter.value.toLowerCase();
            const doctorItems = document.querySelectorAll('.doctor-item');
            let visibleCount = 0;

            doctorItems.forEach(item => {
                const name = item.dataset.name;
                const specialization = item.dataset.specialization;
                const doctorDepartment = item.dataset.department;
                
                const matchesSearch = name.includes(searchTerm) || specialization.includes(searchTerm);
                const matchesDepartment = !department || doctorDepartment === department;

                if (matchesSearch && matchesDepartment) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (visibleCount === 0) {
                noResults.style.display = 'block';
                doctorsGrid.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                doctorsGrid.style.display = 'flex';
            }
        }

        searchInput.addEventListener('input', filterDoctors);
        departmentFilter.addEventListener('change', filterDoctors);

        // Doctor selection for appointment
        function selectDoctor(doctorId) {
            document.getElementById('selected_doctor_id').value = doctorId;
            const modal = new bootstrap.Modal(document.getElementById('appointmentModal'));
            modal.show();
        }

        // Form validation
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            const date = document.getElementById('appointment_date').value;
            const time = document.getElementById('appointment_time').value;
            const doctor = document.getElementById('selected_doctor_id').value;
            const tc = document.getElementById('patient_tc').value;
            const phone = document.getElementById('patient_phone').value;
            const email = document.getElementById('patient_email').value;

            if (!date || !time || !doctor) {
                e.preventDefault();
                alert('Lütfen tüm gerekli alanları doldurun.');
                return;
            }

            // TC Kimlik No kontrolü
            if (tc.length !== 11 || !/^\d+$/.test(tc)) {
                e.preventDefault();
                alert('Lütfen geçerli bir T.C. Kimlik No giriniz (11 haneli).');
                return;
            }

            // Telefon numarası kontrolü
            if (!/^[0-9]{10,11}$/.test(phone.replace(/[^0-9]/g, ''))) {
                e.preventDefault();
                alert('Lütfen geçerli bir telefon numarası giriniz.');
                return;
            }

            // E-posta kontrolü
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Lütfen geçerli bir e-posta adresi giriniz.');
                return;
            }
        });

        // Telefon numarası formatı
        document.getElementById('patient_phone').addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });

        // TC Kimlik No sadece rakam
        document.getElementById('patient_tc').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 11);
        });
    </script>
</body>
</html> 