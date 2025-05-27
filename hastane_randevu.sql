-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 27 May 2025, 17:00:08
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
-- Veritabanı: `hastane_randevu`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Ahmet', 'Yılmaz', 'Kardiyoloji', 'Kalp Hastalıkları', 'İstanbul Üniversitesi Tıp Fakültesi', 15, '09:00:00', '17:00:00', 'Pazartesi,Çarşamba,Cuma', '101', '5551234567', 'ahmet.yilmaz@hastane.com', 'Dr. Ahmet Yılmaz, kalp hastalıkları konusunda uzmanlaşmış deneyimli bir kardiyologdur.', NULL, '2025-05-27 14:57:33', '2025-05-27 14:57:33'),
(2, 'Ayşe', 'Demir', 'Dahiliye', 'İç Hastalıkları', 'Ankara Üniversitesi Tıp Fakültesi', 12, '10:00:00', '18:00:00', 'Salı,Perşembe,Cumartesi', '102', '5552345678', 'ayse.demir@hastane.com', 'Dr. Ayşe Demir, iç hastalıkları alanında uzmanlaşmış deneyimli bir hekimdir.', NULL, '2025-05-27 14:57:33', '2025-05-27 14:57:33'),
(3, 'Mehmet', 'Kaya', 'Ortopedi', 'Kemik ve Eklem Hastalıkları', 'Hacettepe Üniversitesi Tıp Fakültesi', 20, '08:00:00', '16:00:00', 'Pazartesi,Salı,Çarşamba,Perşembe', '103', '5553456789', 'mehmet.kaya@hastane.com', 'Dr. Mehmet Kaya, ortopedi alanında uzmanlaşmış deneyimli bir hekimdir.', NULL, '2025-05-27 14:57:33', '2025-05-27 14:57:33'),
(4, 'Can', 'Özkan', 'Göz', 'Göz Hastalıkları', 'Ege Üniversitesi Tıp Fakültesi', 10, '09:00:00', '17:00:00', 'Pazartesi,Salı,Çarşamba,Perşembe,Cuma', '201', '5554567890', 'can.ozkan@hastane.com', 'Dr. Can Özkan, göz hastalıkları uzmanıdır.', NULL, '2025-05-27 14:57:33', '2025-05-27 14:57:33'),
(5, 'Elif', 'Aydın', 'Kulak Burun Boğaz', 'KBB Hastalıkları', 'Akdeniz Üniversitesi Tıp Fakültesi', 8, '10:00:00', '18:00:00', 'Pazartesi,Salı,Çarşamba,Perşembe,Cuma', '202', '5555678901', 'elif.aydin@hastane.com', 'Dr. Elif Aydın, kulak burun boğaz hastalıkları uzmanıdır.', NULL, '2025-05-27 14:57:33', '2025-05-27 14:57:33');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `tc` varchar(11) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('patient','doctor','admin') NOT NULL DEFAULT 'patient',
  `doctor_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `name`, `surname`, `tc`, `phone`, `email`, `password`, `role`, `doctor_id`, `created_at`, `updated_at`) VALUES
(1, 'Test', 'Hasta', NULL, NULL, 'test@example.com', '$2y$10$cCnOfFgg6eRuTTw3mE.csuGLfQwiIKIV7cawIlZCfZy5vjrV1YvFi', 'patient', NULL, '2025-05-27 14:57:33', '2025-05-27 14:57:33'),
(2, 'Ahmet', 'Yılmaz', NULL, NULL, 'ahmet.yilmaz@hastane.com', '$2y$10$xo6YHjDRGxb1nOX.5OYoTeDDGdU3KoanTvj7olwrA2bTv85oGzQPi', 'doctor', 1, '2025-05-27 14:57:33', '2025-05-27 14:57:33'),
(3, 'Ayşe', 'Demir', NULL, NULL, 'ayse.demir@hastane.com', '$2y$10$d4I13b68nb6l1NnRayTo1.K/vgr6S9qx71aK79XaEKK9FCtyljrLe', 'doctor', 2, '2025-05-27 14:57:33', '2025-05-27 14:57:33'),
(4, 'Mehmet', 'Kaya', NULL, NULL, 'mehmet.kaya@hastane.com', '$2y$10$1i.pgBwZfCbSm2.tWs.E8Omt/i1KFd/Matr46Pt.l/P3.vTZWNmdG', 'doctor', 3, '2025-05-27 14:57:33', '2025-05-27 14:57:33'),
(5, 'Can', 'Özkan', NULL, NULL, 'can.ozkan@hastane.com', '$2y$10$canpasswordhash', 'doctor', 4, '2025-05-27 14:57:33', '2025-05-27 14:57:33'),
(6, 'Elif', 'Aydın', NULL, NULL, 'elif.aydin@hastane.com', '$2y$10$elifpasswordhash', 'doctor', 5, '2025-05-27 14:57:33', '2025-05-27 14:57:33'),
(7, 'Yeni', 'Hasta', '12345678901', '5550001122', 'yeni.hasta@example.com', '$2y$10$yenipasswordhash', 'patient', NULL, '2025-05-27 14:57:33', '2025-05-27 14:57:33');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `idx_appointment_date` (`appointment_date`),
  ADD KEY `idx_status` (`status`);

--
-- Tablo için indeksler `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `tc` (`tc`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Tablo için AUTO_INCREMENT değeri `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Yeni örnek randevular
INSERT INTO `appointments` (`patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `notes`, `status`) VALUES
(1, 4, '2025-06-10', '10:00:00', 'Göz muayenesi', 'pending'),
(7, 5, '2025-06-11', '14:00:00', 'Kulak ağrısı kontrolü', 'confirmed'),
(1, 2, '2025-06-12', '11:00:00', 'Genel kontrol', 'pending');
