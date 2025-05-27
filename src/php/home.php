<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: ../index.php");
    exit();
}
include 'dbcon.php';
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <!--Meta-->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ana Sayfa - Hastane Randevu Sistemi</title>
    <link rel="icon" type="image/x-icon" href="../../src/img/favicon.ico" />

    <!--CSS-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>

<body data-bs-theme="dark" class="my-4">
    <!--Header-->
    <header class="container bg-warning bg-gradient rounded-2 p-1">
        <h1 class="h2 text-center text-dark">Hastane Randevu Sistemi</h1>
    </header>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card bg-dark border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="mb-0">Hoş Geldiniz, <?php echo htmlspecialchars($_SESSION['name']); ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card h-100 bg-dark border-primary">
                                    <div class="card-body text-center">
                                        <i class="bi bi-calendar-plus display-1 text-primary mb-3"></i>
                                        <h4>Yeni Randevu Al</h4>
                                        <p class="text-muted">Hızlı ve kolay bir şekilde yeni randevu oluşturun.</p>
                                        <a href="appointment-booking.php" class="btn btn-primary w-100">
                                            <i class="bi bi-plus-circle"></i> Randevu Al
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 bg-dark border-success">
                                    <div class="card-body text-center">
                                        <i class="bi bi-calendar-check display-1 text-success mb-3"></i>
                                        <h4>Randevularım</h4>
                                        <p class="text-muted">Mevcut randevularınızı görüntüleyin ve yönetin.</p>
                                        <a href="all-appointments.php" class="btn btn-success w-100">
                                            <i class="bi bi-list-check"></i> Randevularımı Gör
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-calendar-plus fa-3x mb-3 text-primary"></i>
                                        <h5 class="card-title">Yeni Randevu</h5>
                                        <p class="card-text">Size en uygun doktoru seçerek randevu oluşturun.</p>
                                        <a href="doctors.php" class="btn btn-primary">Doktorları Görüntüle</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="card bg-dark border-info">
                                <div class="card-body">
                                    <h5 class="card-title text-info">
                                        <i class="bi bi-info-circle"></i> Hızlı Bilgiler
                                    </h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="bi bi-check-circle text-success"></i>
                                            Randevularınızı online olarak yönetin
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-check-circle text-success"></i>
                                            Randevu saatlerini kolayca değiştirin
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-check-circle text-success"></i>
                                            Tüm randevularınızı tek bir yerden görüntüleyin
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <a href="logout.php" class="btn btn-outline-danger">
                                <i class="bi bi-box-arrow-right"></i> Çıkış Yap
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>