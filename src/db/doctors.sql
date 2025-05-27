CREATE TABLE IF NOT EXISTS doctors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    specialization VARCHAR(255) NOT NULL,
    education VARCHAR(255) NOT NULL,
    experience_years INT NOT NULL,
    working_hours_start TIME NOT NULL,
    working_hours_end TIME NOT NULL,
    working_days VARCHAR(50) NOT NULL,
    room_number VARCHAR(10) NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100),
    bio TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample doctors data
INSERT INTO doctors (name, surname, department, specialization, education, experience_years, 
                    working_hours_start, working_hours_end, working_days, room_number, phone, email, bio) VALUES
('Ahmet', 'Yılmaz', 'Kardiyoloji', 'Kalp Hastalıkları', 'İstanbul Üniversitesi Tıp Fakültesi', 15,
 '09:00:00', '17:00:00', 'Pazartesi,Çarşamba,Cuma', '101', '5551234567', 'ahmet.yilmaz@hastane.com',
 'Dr. Ahmet Yılmaz, kalp hastalıkları konusunda uzmanlaşmış deneyimli bir kardiyologtur.'),
 
('Ayşe', 'Demir', 'Dahiliye', 'İç Hastalıkları', 'Ankara Üniversitesi Tıp Fakültesi', 12,
 '10:00:00', '18:00:00', 'Salı,Perşembe,Cumartesi', '102', '5552345678', 'ayse.demir@hastane.com',
 'Dr. Ayşe Demir, iç hastalıkları alanında uzmanlaşmış ve hasta odaklı yaklaşımıyla tanınmaktadır.'),
 
('Mehmet', 'Kaya', 'Ortopedi', 'Eklem Cerrahisi', 'Hacettepe Üniversitesi Tıp Fakültesi', 20,
 '08:00:00', '16:00:00', 'Pazartesi,Salı,Çarşamba', '103', '5553456789', 'mehmet.kaya@hastane.com',
 'Dr. Mehmet Kaya, eklem cerrahisi konusunda uzmanlaşmış ve birçok başarılı operasyona imza atmıştır.'); 