<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Al</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body data-bs-theme="dark">
    <?php
    session_start();
    if (!isset($_SESSION['name'])) {
        header("Location: ../index.php");
        exit();
    }
    include 'dbcon.php';
    ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card bg-dark border-warning">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Yeni Randevu Al</h3>
                        <a href="home.php" class="btn btn-secondary">
                            <i class="bi bi-house"></i> Ana Sayfa
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                                <?php echo htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>
                        <form action="process-appointment.php" method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="patient_name" class="form-label">Ad</label>
                                    <input type="text" class="form-control" id="patient_name" name="patient_name" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="patient_surname" class="form-label">Soyad</label>
                                    <input type="text" class="form-control" id="patient_surname" name="patient_surname" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="patient_tc" class="form-label">T.C. Kimlik No</label>
                                    <input type="text" class="form-control" id="patient_tc" name="patient_tc" pattern="[0-9]{11}" maxlength="11" required>
                                    <div class="invalid-feedback">
                                        Lütfen geçerli bir T.C. Kimlik No girin (11 haneli).
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="patient_phone" class="form-label">Telefon</label>
                                    <input type="tel" class="form-control" id="patient_phone" name="patient_phone" pattern="[0-9]{10}" maxlength="10" required>
                                    <div class="invalid-feedback">
                                        Lütfen geçerli bir telefon numarası girin (10 haneli).
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="patient_email" class="form-label">E-posta</label>
                                <input type="email" class="form-control" id="patient_email" name="patient_email" required>
                                <div class="invalid-feedback">
                                    Lütfen geçerli bir e-posta adresi girin.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="department" class="form-label">Bölüm Seçin</label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="">Bölüm seçin...</option>
                                    <?php
                                    $query = "SELECT DISTINCT department FROM doctors ORDER BY department";
                                    $result = $conn->query($query);
                                    while ($row = $result->fetch_assoc()) {
                                        $selected = (isset($_POST['department']) && $_POST['department'] == $row['department']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($row['department']) . "' $selected>" . htmlspecialchars($row['department']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="doctor" class="form-label">Doktor Seçin</label>
                                <select class="form-select" id="doctor" name="doctor" required>
                                    <option value="">Önce bölüm seçin...</option>
                                    <?php
                                    if (isset($_POST['department'])) {
                                        $dept = $_POST['department'];
                                        $query = "SELECT id, name FROM doctors WHERE department = ? ORDER BY name";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("s", $dept);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date" class="form-label">Randevu Tarihi</label>
                                    <input type="text" class="form-control" id="date" name="date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="time" class="form-label">Randevu Saati</label>
                                    <select class="form-select" id="time" name="time" required>
                                        <option value="">Saat seçin...</option>
                                        <option value="09:00">09:00</option>
                                        <option value="10:00">10:00</option>
                                        <option value="11:00">11:00</option>
                                        <option value="13:00">13:00</option>
                                        <option value="14:00">14:00</option>
                                        <option value="15:00">15:00</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notlar (İsteğe bağlı)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-calendar-check"></i> Randevu Al
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/tr.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Date picker
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

        // Doctor selection
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

        // TC Kimlik No validation
        document.getElementById('patient_tc').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Phone number validation
        document.getElementById('patient_phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html> 