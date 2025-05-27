<?php
include 'dbcon.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$department = isset($_GET['department']) ? $_GET['department'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the query
$query = "SELECT * FROM doctors WHERE 1=1";
if (!empty($department)) {
    $query .= " AND department = ?";
}
if (!empty($search)) {
    $query .= " AND (name LIKE ? OR surname LIKE ? OR specialization LIKE ?)";
}
$query .= " ORDER BY department, name, surname";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!empty($department) && !empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("ssss", $department, $search_param, $search_param, $search_param);
} elseif (!empty($department)) {
    $stmt->bind_param("s", $department);
} elseif (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}
$stmt->execute();
$result = $stmt->get_result();

// Get unique departments for filter
$departments_query = "SELECT DISTINCT department FROM doctors ORDER BY department";
$departments_result = $conn->query($departments_query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doktorlarımız - Hastane Randevu Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .doctor-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .doctor-card:hover {
            transform: translateY(-5px);
        }
        .working-days {
            font-size: 0.9em;
        }
        .specialization-badge {
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <h1 class="text-center mb-4">Doktorlarımız</h1>
                
                <!-- Search and Filter Form -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <select name="department" class="form-select">
                            <option value="">Tüm Bölümler</option>
                            <?php while ($dept = $departments_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($dept['department']); ?>"
                                    <?php echo $department === $dept['department'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Doktor adı, soyadı veya uzmanlık alanı ile arama yapın..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Ara</button>
                    </div>
                </form>

                <!-- Doctors Grid -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php while ($doctor = $result->fetch_assoc()): ?>
                        <div class="col">
                            <div class="card doctor-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        Dr. <?php echo htmlspecialchars($doctor['name'] . ' ' . $doctor['surname']); ?>
                                    </h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        <?php echo htmlspecialchars($doctor['department']); ?>
                                    </h6>
                                    <span class="badge bg-info specialization-badge mb-2">
                                        <?php echo htmlspecialchars($doctor['specialization']); ?>
                                    </span>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-graduation-cap"></i> 
                                            <?php echo htmlspecialchars($doctor['education']); ?>
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-briefcase"></i> 
                                            <?php echo $doctor['experience_years']; ?> yıl deneyim
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> 
                                            <?php 
                                            echo date('H:i', strtotime($doctor['working_hours_start'])) . ' - ' . 
                                                 date('H:i', strtotime($doctor['working_hours_end']));
                                            ?>
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> 
                                            <?php echo htmlspecialchars($doctor['working_days']); ?>
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-door-open"></i> 
                                            Oda: <?php echo htmlspecialchars($doctor['room_number']); ?>
                                        </small>
                                    </div>
                                    
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($doctor['bio']); ?>
                                    </p>
                                    
                                    <a href="appointment-booking.php?doctor=<?php echo $doctor['id']; ?>" 
                                       class="btn btn-primary">
                                        Randevu Al
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($result->num_rows === 0): ?>
                    <div class="alert alert-info text-center mt-4">
                        Arama kriterlerinize uygun doktor bulunamadı.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 