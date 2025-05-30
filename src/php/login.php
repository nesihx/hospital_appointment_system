<?php
include 'dbcon.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = $_POST['email'];
	$password = $_POST['password'];

	$query = "SELECT * FROM users WHERE email = ?";
	$stmt = $conn->prepare($query);
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows > 0) {
		$user = $result->fetch_assoc();
		if (password_verify($password, $user['password'])) {
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['role'] = $user['role'];
			$_SESSION['name'] = $user['name'];
			$_SESSION['surname'] = $user['surname'];
			
			// Redirect based on role
			if ($user['role'] == 'admin') {
				$_SESSION['admin_id'] = $user['id'];
				$_SESSION['admin_name'] = $user['name'];
				header("Location: admin_dashboard.php");
				exit();
			} elseif ($user['role'] == 'doctor') {
				$_SESSION['doctor_id'] = $user['id'];
				$_SESSION['doctor_name'] = $user['name'];
				header("Location: doctor_dashboard.php");
				exit();
			} else {
				header("Location: home.php");
				exit();
			}
		} else {
			$error = "Geçersiz e-posta veya şifre.";
		}
	} else {
		$error = "Geçersiz e-posta veya şifre.";
	}
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Memorial Sağlık Grubu - Sağlığınız İçin Yanınızdayız</title>
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
		}

		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			color: var(--dark-text);
		}

		.hero-section {
			background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
						url('https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
			background-size: cover;
			background-position: center;
			color: white;
			padding: 180px 0;
			text-align: center;
			position: relative;
		}

		.hero-section::after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 0;
			right: 0;
			height: 100px;
			background: linear-gradient(to top, var(--light-bg), transparent);
		}

		.hero-title {
			font-size: 3.5rem;
			font-weight: 700;
			margin-bottom: 1rem;
			text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
		}

		.hero-subtitle {
			font-size: 1.5rem;
			margin-bottom: 2rem;
			text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
		}

		.feature-card {
			background: white;
			border-radius: 15px;
			padding: 2.5rem;
			margin-bottom: 2rem;
			box-shadow: 0 5px 15px rgba(0,0,0,0.05);
			transition: all 0.3s ease;
			height: 100%;
			position: relative;
			overflow: hidden;
		}

		.feature-card::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 5px;
			background: var(--secondary-color);
			transform: scaleX(0);
			transition: transform 0.3s ease;
		}

		.feature-card:hover {
			transform: translateY(-10px);
			box-shadow: 0 15px 30px rgba(0,0,0,0.1);
		}

		.feature-card:hover::before {
			transform: scaleX(1);
		}

		.feature-icon {
			font-size: 3rem;
			color: var(--secondary-color);
			margin-bottom: 1.5rem;
			transition: transform 0.3s ease;
		}

		.feature-card:hover .feature-icon {
			transform: scale(1.1);
		}

		.department-card {
			background: white;
			border-radius: 15px;
			padding: 2rem;
			margin-bottom: 1.5rem;
			box-shadow: 0 5px 15px rgba(0,0,0,0.05);
			transition: all 0.3s ease;
			height: 100%;
			position: relative;
			overflow: hidden;
		}

		.department-card::after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 0;
			width: 100%;
			height: 3px;
			background: var(--secondary-color);
			transform: scaleX(0);
			transition: transform 0.3s ease;
		}

		.department-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 8px 25px rgba(0,0,0,0.1);
		}

		.department-card:hover::after {
			transform: scaleX(1);
		}

		.department-icon {
			font-size: 2.5rem;
			margin-bottom: 1rem;
		}

		.stats-section {
			background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
			color: white;
			padding: 4rem 0;
			position: relative;
			overflow: hidden;
		}

		.stats-section::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') center/cover;
			opacity: 0.1;
		}

		.stat-item {
			text-align: center;
			padding: 2rem;
		}

		.stat-number {
			font-size: 3rem;
			font-weight: 700;
			margin-bottom: 0.5rem;
		}

		.stat-label {
			font-size: 1.2rem;
			opacity: 0.9;
		}

		.contact-info {
			background: var(--primary-color);
			color: white;
			padding: 4rem 0;
			position: relative;
		}

		.contact-info::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') center/cover;
			opacity: 0.1;
		}

		.contact-item {
			margin-bottom: 2rem;
			display: flex;
			align-items: center;
		}

		.contact-icon {
			font-size: 2rem;
			margin-right: 1.5rem;
			color: var(--secondary-color);
			width: 50px;
			height: 50px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: rgba(255,255,255,0.1);
			border-radius: 50%;
			transition: all 0.3s ease;
		}

		.contact-item:hover .contact-icon {
			transform: scale(1.1);
			background: var(--secondary-color);
			color: white;
		}

		.map-container {
			height: 400px;
			border-radius: 15px;
			overflow: hidden;
			box-shadow: 0 5px 15px rgba(0,0,0,0.1);
		}

		.map-container iframe {
			width: 100%;
			height: 100%;
			border: none;
		}

		.btn-primary {
			background-color: var(--secondary-color);
			border: none;
			padding: 1rem 2rem;
			border-radius: 30px;
			font-size: 1.1rem;
			transition: all 0.3s ease;
			box-shadow: 0 5px 15px rgba(0,0,0,0.2);
		}

		.btn-primary:hover {
			background-color: var(--primary-color);
			transform: translateY(-3px);
			box-shadow: 0 8px 20px rgba(0,0,0,0.3);
		}

		.login-modal .modal-content {
			border-radius: 20px;
			border: none;
			box-shadow: 0 10px 30px rgba(0,0,0,0.2);
		}

		.login-modal .modal-header {
			background: var(--primary-color);
			color: white;
			border-radius: 20px 20px 0 0;
			padding: 1.5rem;
		}

		.login-modal .modal-body {
			padding: 2rem;
		}

		.login-modal .form-control {
			border-radius: 30px;
			padding: 1rem 1.5rem;
			border: 2px solid #eee;
			transition: all 0.3s ease;
		}

		.login-modal .form-control:focus {
			border-color: var(--secondary-color);
			box-shadow: 0 0 0 0.2rem rgba(41, 128, 185, 0.25);
		}

		.login-modal .btn-login {
			border-radius: 30px;
			padding: 1rem 2rem;
			background: var(--secondary-color);
			border: none;
			width: 100%;
			font-size: 1.1rem;
			transition: all 0.3s ease;
		}

		.login-modal .btn-login:hover {
			background: var(--primary-color);
			transform: translateY(-2px);
		}

		.section-title {
			position: relative;
			margin-bottom: 3rem;
			text-align: center;
		}

		.section-title::after {
			content: '';
			position: absolute;
			bottom: -10px;
			left: 50%;
			transform: translateX(-50%);
			width: 80px;
			height: 3px;
			background: var(--secondary-color);
		}

		.section-title h2 {
			font-size: 2.5rem;
			font-weight: 700;
			color: var(--primary-color);
		}
	</style>
</head>

<body>
	<!-- Hero Section -->
	<section class="hero-section">
		<div class="container">
			<h1 class="hero-title">Memorial Sağlık Grubu</h1>
			<p class="hero-subtitle">50 Yıllık Tecrübe, Modern Teknoloji ve Uzman Kadro ile Sağlığınız İçin Yanınızdayız</p>
			<button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">
				<i class="fas fa-sign-in-alt"></i> Hasta Girişi
			</button>
		</div>
	</section>

	<!-- Features Section -->
	<section class="py-5 bg-light">
		<div class="container">
			<div class="section-title">
				<h2>Neden Memorial?</h2>
			</div>
			<div class="row">
				<div class="col-md-4">
					<div class="feature-card text-center">
						<i class="fas fa-user-md feature-icon"></i>
						<h3>Uzman Kadro</h3>
						<p>Alanında uzman 500'den fazla doktorumuz ve 2000'den fazla sağlık personelimizle hizmetinizdeyiz.</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="feature-card text-center">
						<i class="fas fa-hospital feature-icon"></i>
						<h3>Modern Teknoloji</h3>
						<p>En son teknolojik cihazlar, robotik cerrahi sistemleri ve modern tedavi yöntemleriyle sağlığınıza kavuşun.</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="feature-card text-center">
						<i class="fas fa-heartbeat feature-icon"></i>
						<h3>7/24 Hizmet</h3>
						<p>Acil servisimiz, yoğun bakım ünitelerimiz ve uzman kadromuz 7/24 hizmetinizde.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Stats Section -->
	<section class="stats-section">
		<div class="container">
			<div class="row">
				<div class="col-md-3">
					<div class="stat-item">
						<div class="stat-number">50+</div>
						<div class="stat-label">Yıllık Tecrübe</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="stat-item">
						<div class="stat-number">500+</div>
						<div class="stat-label">Uzman Doktor</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="stat-item">
						<div class="stat-number">1000+</div>
						<div class="stat-label">Yatak Kapasitesi</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="stat-item">
						<div class="stat-number">1M+</div>
						<div class="stat-label">Mutlu Hasta</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Departments Section -->
	<section class="py-5">
		<div class="container">
			<div class="section-title">
				<h2>Bölümlerimiz</h2>
			</div>
			<div class="row">
				<div class="col-md-4">
					<div class="department-card">
						<i class="fas fa-heart text-danger department-icon"></i>
						<h4>Kardiyoloji</h4>
						<p>Kalp sağlığınız için en son teknoloji ve uzman kadromuzla hizmetinizdeyiz. Kalp hastalıklarının teşhis ve tedavisinde öncü.</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="department-card">
						<i class="fas fa-brain text-primary department-icon"></i>
						<h4>Nöroloji</h4>
						<p>Sinir sistemi hastalıklarında uzman tedavi. Beyin ve sinir sistemi hastalıklarının teşhis ve tedavisinde deneyimli ekibimiz.</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="department-card">
						<i class="fas fa-bone text-success department-icon"></i>
						<h4>Ortopedi</h4>
						<p>Kemik ve eklem sağlığınız için modern tedavi yöntemleri. Robotik cerrahi sistemleri ile minimal invaziv tedaviler.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Contact Section -->
	<section class="contact-info">
		<div class="container">
			<div class="row">
				<div class="col-md-6">
					<h3 class="mb-4">İletişim Bilgileri</h3>
					<div class="contact-item">
						<div class="contact-icon">
							<i class="fas fa-map-marker-alt"></i>
						</div>
						<div>
							<h5>Adres</h5>
							<p>Piyalepaşa Bulvarı No:1, Şişli/İstanbul</p>
						</div>
					</div>
					<div class="contact-item">
						<div class="contact-icon">
							<i class="fas fa-phone"></i>
						</div>
						<div>
							<h5>Telefon</h5>
							<p>+90 (212) 314 66 66</p>
						</div>
					</div>
					<div class="contact-item">
						<div class="contact-icon">
							<i class="fas fa-envelope"></i>
						</div>
						<div>
							<h5>E-posta</h5>
							<p>info@memorial.com.tr</p>
						</div>
					</div>
					<div class="contact-item">
						<div class="contact-icon">
							<i class="fas fa-clock"></i>
						</div>
						<div>
							<h5>Çalışma Saatleri</h5>
							<p>7/24 Hizmet</p>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="map-container">
						<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3007.963371325!2d28.9776!3d41.0082!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDHCsDAwJzI5LjUiTiAyOMKwNTgnMzkuNCJF!5e0!3m2!1str!2str!4v1234567890" allowfullscreen></iframe>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Login Modal -->
	<div class="modal fade login-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="loginModalLabel">Hasta Girişi</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<?php if (isset($error)): ?>
						<div class="alert alert-danger"><?php echo $error; ?></div>
					<?php endif; ?>
					<form method="POST" action="">
						<div class="mb-4">
							<label for="email" class="form-label">E-posta Adresi</label>
							<input type="email" class="form-control" id="email" name="email" required 
								   placeholder="ornek@email.com">
						</div>
						<div class="mb-4">
							<label for="password" class="form-label">Şifre</label>
							<input type="password" class="form-control" id="password" name="password" required
								   placeholder="••••••••">
						</div>
						<button type="submit" class="btn btn-login">
							<i class="fas fa-sign-in-alt"></i> Giriş Yap
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>