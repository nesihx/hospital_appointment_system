<?php
$host = "localhost";
$username = "root";
$password = "";

// Create connection without database
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Read and execute schema.sql file
$schema_sql_file = __DIR__ . '/../db/schema.sql';
$schema_sql = file_get_contents($schema_sql_file);

if ($schema_sql === false) {
    die("schema.sql dosyası okunamadı.");
}

if ($conn->multi_query($schema_sql)) {
    do {
        // Store result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Veritabanı şeması başarıyla kuruldu.<br>";
} else {
    echo "Veritabanı şeması kurulurken hata: " . $conn->error . "<br>";
}

// Reconnect to select the database after creating it
$conn->close();
$conn = new mysqli($host, $username, $password, "hastane_randevu");

// Check connection again
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Read and execute seed.sql file (excluding user insertions)
$seed_sql_file = __DIR__ . '/../db/seed.sql';
$seed_sql = file_get_contents($seed_sql_file);

if ($seed_sql === false) {
    die("seed.sql dosyası okunamadı.");
}

// Remove user insertion parts from seed_sql before executing
$seed_sql_parts = explode('-- Create test patient user', $seed_sql);
$seed_sql_without_users = $seed_sql_parts[0];

if ($conn->multi_query($seed_sql_without_users)) {
     do {
        // Store result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Örnek doktorlar ve randevular başarıyla eklendi.<br>";
} else {
    echo "Örnek doktorlar ve randevular eklenirken hata: " . $conn->error . "<br>";
}

// Insert test patient user using PHP password_hash
$test_patient_password = "test123";
$hashed_patient_password = password_hash($test_patient_password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, surname, email, password, role) VALUES 
        ('Test', 'Hasta', 'test@example.com', ?, 'patient')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed_patient_password);

if ($stmt->execute()) {
    echo "Test hasta kullanıcısı başarıyla oluşturuldu.<br>";
    echo "E-posta: test@example.com<br>";
    echo "Şifre: test123<br>";
} else {
    echo "Test hasta kullanıcısı oluşturulurken hata: " . $stmt->error . "<br>";
}

// Insert test doctor users using PHP password_hash
$doctors_to_insert = [
    ['name' => 'Ahmet', 'surname' => 'Yılmaz', 'email' => 'ahmet.yilmaz@hastane.com', 'password' => 'doctor123', 'doctor_id' => 1],
    ['name' => 'Ayşe', 'surname' => 'Demir', 'email' => 'ayse.demir@hastane.com', 'password' => 'doctor123', 'doctor_id' => 2],
    ['name' => 'Mehmet', 'surname' => 'Kaya', 'email' => 'mehmet.kaya@hastane.com', 'password' => 'doctor123', 'doctor_id' => 3]
];

foreach ($doctors_to_insert as $doctor) {
    $hashed_doctor_password = password_hash($doctor['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (name, surname, email, password, role, doctor_id) VALUES 
            (?, ?, ?, ?, 'doctor', ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $doctor['name'], $doctor['surname'], $doctor['email'], $hashed_doctor_password, $doctor['doctor_id']);
    
    if ($stmt->execute()) {
        echo "Doktor kullanıcısı başarıyla oluşturuldu: {$doctor['email']}<br>";
    } else {
        echo "Doktor kullanıcısı oluşturulurken hata: " . $stmt->error . "<br>";
    }
}

$conn->close();

echo "<br>Kurulum tamamlandı! <a href='login.php'>Giriş sayfasına git</a>";
?> 