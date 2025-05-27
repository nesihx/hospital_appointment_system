<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
include 'dbcon.php';

$appointment = null;

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    // Get appointment details for the logged-in user
    $sql = "SELECT * FROM appointments WHERE id = ? AND patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $_SESSION['error'] = "Randevu bulunamadı veya bu randevuyu düzenleme yetkiniz yok.";
        header("Location: all-appointments.php");
        exit();
    }
    
    $appointment = $result->fetch_assoc();
} else {
    header("Location: all-appointments.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $appointment) {
    $id = filter_input(INPUT_POST, 'appointment_id', FILTER_SANITIZE_NUMBER_INT);
    $department = filter_input(INPUT_POST, 'department', FILTER_SANITIZE_STRING);
    $doctor_id = filter_input(INPUT_POST, 'doctor', FILTER_SANITIZE_NUMBER_INT);
    $appointment_date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $appointment_time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING) ?? '';
    
    // Basic validation
    if (empty($department) || empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
        $_SESSION['error'] = "Lütfen tüm gerekli alanları doldurun.";
        header("Location: update.php?id=" . $id);
        exit();
    }

    // Validate date format (YYYY-MM-DD)
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $appointment_date)) {
        $_SESSION['error'] = "Geçersiz tarih formatı.";
        header("Location: update.php?id=" . $id);
        exit();
    }

    // Validate time format (HH:MM)
    if (!preg_match("/^\d{2}:\d{2}$/", $appointment_time)) {
        $appointment_time .= ':00';
        if (!preg_match("/^\d{2}:\d{2}:\d{2}$/", $appointment_time)) {
             $_SESSION['error'] = "Geçersiz saat formatı.";
             header("Location: update.php?id=" . $id);
             exit();
        }
    }

    // Check if the doctor exists and belongs to the selected department
    $query = "SELECT id FROM doctors WHERE id = ? AND department = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $doctor_id, $department);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Seçilen doktor bulunamadı veya seçilen bölüme ait değil.";
        header("Location: update.php?id=" . $id);
        exit();
    }
    
    // Check for existing appointment at the same time for the same doctor (excluding current appointment)
    $check_sql = "SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("issi", $doctor_id, $appointment_date, $appointment_time, $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Bu saatte doktorun başka bir randevusu bulunmaktadır. Lütfen farklı bir saat seçin.";
        header("Location: update.php?id=" . $id);
        exit();
    }
    
    // Update the appointment
    $sql = "UPDATE appointments SET doctor_id = ?, appointment_date = ?, appointment_time = ?, notes = ? WHERE id = ? AND patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issiii", $doctor_id, $appointment_date, $appointment_time, $notes, $id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Randevu başarıyla güncellendi.";
        header("Location: all-appointments.php");
    } else {
        $_SESSION['error'] = "Randevu güncellenirken bir hata oluştu: " . $stmt->error;
        header("Location: update.php?id=" . $id);
    }
    exit();
}

// Fetch doctors for the selected department initially
$doctors_list = [];
if ($appointment && !empty($appointment['department'])) {
    $query = "SELECT id, name FROM doctors WHERE department = ? ORDER BY name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $appointment['department']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $doctors_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <!--Meta-->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Randevu Güncelle</title>
    <link rel="icon" type="image/x-icon" href="./src/img/favicon.ico" />

    <!--CSS-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body data-bs-theme="dark" class="my-4">

    <!--Header-->
    <header class="container bg-warning bg-gradient rounded-2 p-1">
        <h1 class="h2 text-center text-dark">Hastane Randevu Sistemi</h1>
    </header>

    <!--Randevu-->
    <section class="container rounded-2 mt-3">
        <div class="row">

            <!--Input-->
            <div class="col-md-8 offset-md-2">
                <div class="card bg-dark border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="mb-0">Randevu Güncelle</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                            <div class="mb-3">
                                <label for="department" class="form-label">Bölüm</label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="">Bölüm seçin...</option>
                                    <?php
                                    $query = "SELECT DISTINCT department FROM doctors ORDER BY department";
                                    $result = $conn->query($query);
                                    while ($row = $result->fetch_assoc()) {
                                        $selected = ($appointment['department'] == $row['department']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($row['department']) . "' $selected>" . htmlspecialchars($row['department']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="doctor" class="form-label">Doktor</label>
                                <select class="form-select" id="doctor" name="doctor" required>
                                    <option value="">Önce bölüm seçin...</option>
                                    <?php foreach ($doctors_list as $doctor): ?>
                                        <option value="<?php echo $doctor['id']; ?>" <?php echo ($appointment['doctor_id'] == $doctor['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($doctor['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="date" class="form-label">Randevu Tarihi</label>
                                <input type="text" class="form-control" id="date" name="date" value="<?php echo $appointment['appointment_date']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="time" class="form-label">Randevu Saati</label>
                                <select class="form-select" id="time" name="time" required>
                                    <option value="">Saat seçin...</option>
                                    <option value="09:00" <?php echo $appointment['appointment_time'] == '09:00:00' ? 'selected' : ''; ?>>09:00</option>
                                    <option value="10:00" <?php echo $appointment['appointment_time'] == '10:00:00' ? 'selected' : ''; ?>>10:00</option>
                                    <option value="11:00" <?php echo $appointment['appointment_time'] == '11:00:00' ? 'selected' : ''; ?>>11:00</option>
                                    <option value="13:00" <?php echo $appointment['appointment_time'] == '13:00:00' ? 'selected' : ''; ?>>13:00</option>
                                    <option value="14:00" <?php echo $appointment['appointment_time'] == '14:00:00' ? 'selected' : ''; ?>>14:00</option>
                                    <option value="15:00" <?php echo $appointment['appointment_time'] == '15:00:00' ? 'selected' : ''; ?>>15:00</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notlar</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($appointment['notes'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning">Randevuyu Güncelle</button>
                                <a href="all-appointments.php" class="btn btn-secondary">İptal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!--Animasyon-->
            <div class="col col-md-6 d-flex align-items-center justify-content-center">
                <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
                <lottie-player src="https://assets7.lottiefiles.com/packages/lf20_x1gjdldd.json" mode="bounce"
                    background="transparent" speed="0.6" style="width: fit-content; height: fit-content" loop
                    autoplay></lottie-player>
            </div>
        </div>
    </section>

    <!--JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/tr.js"></script>
    <script>
        flatpickr("#date", {
            locale: "tr",
            dateFormat: "Y-m-d",
            minDate: "today",
            disable: [
                function(date) {
                    return (date.getDay() === 0 || date.getDay() === 6);
                }
            ]
        });

        const departmentSelect = document.getElementById('department');
        const doctorSelect = document.getElementById('doctor');

        departmentSelect.addEventListener('change', function() {
            const selectedDepartment = this.value;
            if (selectedDepartment) {
                // AJAX ile doktorları getir
                fetch('get_doctors.php?department=' + encodeURIComponent(selectedDepartment))
                    .then(response => response.json())
                    .then(data => {
                        doctorSelect.innerHTML = '<option value="">Doktor seçin...</option>';
                        data.forEach(doctor => {
                            const option = document.createElement('option');
                            option.value = doctor.id;
                            option.textContent = doctor.name;
                            doctorSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Hata:', error);
                        doctorSelect.innerHTML = '<option value="">Doktorlar yüklenirken hata oluştu</option>';
                    });
            } else {
                doctorSelect.innerHTML = '<option value="">Önce bölüm seçin...</option>';
            }
        });
    </script>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>

</html>