<?php
session_start();
include 'dbcon.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$email = $_POST['email'];
	$password = $_POST['password'];

	$query = "SELECT id, name, password, role, doctor_id FROM users WHERE email = ?";
	$stmt = $conn->prepare($query);
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows == 1) {
		$user = $result->fetch_assoc();
		if (password_verify($password, $user['password'])) {
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['name'] = $user['name'];
			$_SESSION['role'] = $user['role'];
			
			if ($user['role'] === 'doctor') {
				$_SESSION['doctor_id'] = $user['doctor_id'];
				header("Location: doctor-panel.php");
			} else {
				header("Location: home.php");
			}
			exit();
		}
	}
	
	$error = "Geçersiz e-posta veya şifre!";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Giriş Yap - Hastane Randevu Sistemi</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<style>
		body {
			background-color: #f8f9fa;
			min-height: 100vh;
			display: flex;
			align-items: center;
		}
		.login-container {
			max-width: 400px;
			margin: 0 auto;
			padding: 20px;
		}
		.card {
			border: none;
			border-radius: 15px;
			box-shadow: 0 0 20px rgba(0,0,0,0.1);
		}
		.card-header {
			background-color: #0d6efd;
			color: white;
			text-align: center;
			border-radius: 15px 15px 0 0 !important;
			padding: 20px;
		}
		.btn-primary {
			width: 100%;
			padding: 12px;
			font-size: 16px;
			border-radius: 8px;
		}
		.form-control {
			padding: 12px;
			border-radius: 8px;
		}
		.form-control:focus {
			box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
		}
		.alert {
			border-radius: 8px;
		}
		.login-title {
			font-size: 24px;
			font-weight: 600;
			margin-bottom: 0;
		}
		.login-subtitle {
			font-size: 14px;
			opacity: 0.8;
			margin-top: 5px;
		}
		.back-link {
			text-align: center;
			margin-top: 20px;
		}
		.back-link a {
			color: #0d6efd;
			text-decoration: none;
		}
		.back-link a:hover {
			text-decoration: underline;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="login-container">
			<div class="card">
				<div class="card-header">
					<h3 class="login-title">Hastane Randevu Sistemi</h3>
					<p class="login-subtitle">Lütfen giriş yapın</p>
				</div>
				<div class="card-body p-4">
					<?php if (isset($error)): ?>
						<div class="alert alert-danger" role="alert">
							<i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
						</div>
					<?php endif; ?>

					<form method="POST" action="">
						<div class="mb-3">
							<label for="email" class="form-label">E-posta</label>
							<div class="input-group">
								<span class="input-group-text"><i class="fas fa-envelope"></i></span>
								<input type="email" class="form-control" id="email" name="email" required 
									   placeholder="ornek@email.com">
							</div>
						</div>
						<div class="mb-4">
							<label for="password" class="form-label">Şifre</label>
							<div class="input-group">
								<span class="input-group-text"><i class="fas fa-lock"></i></span>
								<input type="password" class="form-control" id="password" name="password" required
									   placeholder="••••••••">
							</div>
						</div>
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-sign-in-alt"></i> Giriş Yap
						</button>
					</form>
				</div>
			</div>
			<div class="back-link">
				<a href="../index.php">
					<i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
				</a>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>