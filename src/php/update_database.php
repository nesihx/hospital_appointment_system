<?php
include 'dbcon.php';

// Read the SQL file
$sql = file_get_contents('../db/update_appointments.sql');

// Split the SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = true;
$errors = [];

// Execute each statement separately
foreach ($statements as $statement) {
    if (!empty($statement)) {
        if (!$conn->query($statement)) {
            $success = false;
            $errors[] = "Hata: " . $conn->error . " (SQL: " . $statement . ")";
        }
    }
}

// Output results
if ($success) {
    echo "<div style='color: green; padding: 20px;'>";
    echo "<h2>Veritabanı başarıyla güncellendi!</h2>";
    echo "<p>Aşağıdaki değişiklikler yapıldı:</p>";
    echo "<ul>";
    echo "<li>patient_surname sütunu eklendi</li>";
    echo "<li>patient_tc sütunu eklendi</li>";
    echo "<li>patient_phone sütunu eklendi</li>";
    echo "<li>patient_email sütunu eklendi</li>";
    echo "<li>patient_tc için indeks oluşturuldu</li>";
    echo "</ul>";
    echo "<p><a href='appointment-booking.php'>Randevu oluşturma sayfasına dön</a></p>";
    echo "</div>";
} else {
    echo "<div style='color: red; padding: 20px;'>";
    echo "<h2>Veritabanı güncellenirken hatalar oluştu:</h2>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

$conn->close();
?> 