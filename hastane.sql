-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 27 May 2025, 17:00:04
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `hastane`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `patient_surname` varchar(100) NOT NULL,
  `patient_tc` varchar(11) NOT NULL,
  `patient_phone` varchar(15) NOT NULL,
  `patient_email` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `doctor` varchar(100) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `appointments`
--

INSERT INTO `appointments` (`id`, `patient_name`, `patient_surname`, `patient_tc`, `patient_phone`, `patient_email`, `department`, `doctor`, `appointment_date`, `appointment_time`, `notes`, `status`, `created_at`) VALUES
(3, 'Eren', 'kardas', '34561231345', '5549697732', 'nesihkardas@gmail.com', 'Dahiliye', 'Dr. Mehmet Kaya', '2025-05-29', '14:00:00', 'sda', 'pending', '2025-05-27 13:59:12');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `education` varchar(255) NOT NULL,
  `experience_years` int(11) NOT NULL,
  `working_hours_start` time NOT NULL,
  `working_hours_end` time NOT NULL,
  `working_days` varchar(50) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `doctors`
--

INSERT INTO `doctors` (`id`, `name`, `surname`, `department`, `specialization`, `education`, `experience_years`, `working_hours_start`, `working_hours_end`, `working_days`, `room_number`, `phone`, `email`, `bio`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'Ahmet', 'Yılmaz', 'Kardiyoloji', 'Kalp Hastalıkları', 'İstanbul Üniversitesi Tıp Fakültesi', 15, '09:00:00', '17:00:00', 'Pazartesi,Çarşamba,Cuma', '101', '5551234567', 'ahmet.yilmaz@hastane.com', 'Dr. Ahmet Yılmaz, kalp hastalıkları konusunda uzmanlaşmış deneyimli bir kardiyologtur.', NULL, '2025-05-27 14:00:49', '2025-05-27 14:00:49'),
(2, 'Ayşe', 'Demir', 'Dahiliye', 'İç Hastalıkları', 'Ankara Üniversitesi Tıp Fakültesi', 12, '10:00:00', '18:00:00', 'Salı,Perşembe,Cumartesi', '102', '5552345678', 'ayse.demir@hastane.com', 'Dr. Ayşe Demir, iç hastalıkları alanında uzmanlaşmış ve hasta odaklı yaklaşımıyla tanınmaktadır.', NULL, '2025-05-27 14:00:49', '2025-05-27 14:00:49'),
(3, 'Mehmet', 'Kaya', 'Ortopedi', 'Eklem Cerrahisi', 'Hacettepe Üniversitesi Tıp Fakültesi', 20, '08:00:00', '16:00:00', 'Pazartesi,Salı,Çarşamba', '103', '5553456789', 'mehmet.kaya@hastane.com', 'Dr. Mehmet Kaya, eklem cerrahisi konusunda uzmanlaşmış ve birçok başarılı operasyona imza atmıştır.', NULL, '2025-05-27 14:00:49', '2025-05-27 14:00:49');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `randevu`
--

CREATE TABLE `randevu` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `surname` varchar(255) DEFAULT NULL,
  `tc` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `date` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `randevu`
--

INSERT INTO `randevu` (`id`, `name`, `surname`, `tc`, `city`, `department`, `date`) VALUES
(1, 'mehmet', 'kardas', '1574224103', 'Kars Vegas', 'Ağız ve Diş Sağlığı', '2000-05-05');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Test Kullanıcı', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-05-27 14:03:31', '2025-05-27 14:03:31');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_name` (`patient_name`),
  ADD KEY `appointment_date` (`appointment_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_patient_tc` (`patient_tc`);

--
-- Tablo için indeksler `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `randevu`
--
ALTER TABLE `randevu`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `randevu`
--
ALTER TABLE `randevu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
