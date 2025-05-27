<?php
session_start();
include 'dbcon.php';

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Oturum açmanız gerekiyor']);
    exit();
}

// Bölüm parametresini al
$department = filter_input(INPUT_GET, 'department', FILTER_SANITIZE_STRING);

if (empty($department)) {
    http_response_code(400);
    echo json_encode(['error' => 'Bölüm seçilmedi']);
    exit();
}

// Doktorları getir
$query = "SELECT id, name FROM doctors WHERE department = ? ORDER BY name";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = [
        'id' => $row['id'],
        'name' => $row['name']
    ];
}

// JSON olarak doktorları döndür
header('Content-Type: application/json');
echo json_encode($doctors);

$stmt->close();
$conn->close();
?> 