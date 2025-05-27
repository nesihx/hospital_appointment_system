USE hastane_randevu;

-- Insert sample doctors
INSERT INTO doctors (name, surname, department, specialization, education, experience_years, 
        working_hours_start, working_hours_end, working_days, room_number, phone, email, bio) VALUES
        ('Ahmet', 'Yılmaz', 'Kardiyoloji', 'Kalp Hastalıkları', 'İstanbul Üniversitesi Tıp Fakültesi', 15,
         '09:00:00', '17:00:00', 'Pazartesi,Çarşamba,Cuma', '101', '5551234567', 'ahmet.yilmaz@hastane.com',
         'Dr. Ahmet Yılmaz, kalp hastalıkları konusunda uzmanlaşmış deneyimli bir kardiyologtur.'),
        ('Ayşe', 'Demir', 'Dahiliye', 'İç Hastalıkları', 'Ankara Üniversitesi Tıp Fakültesi', 12,
         '10:00:00', '18:00:00', 'Salı,Perşembe,Cumartesi', '102', '5552345678', 'ayse.demir@hastane.com',
         'Dr. Ayşe Demir, iç hastalıkları alanında uzmanlaşmış deneyimli bir hekimdir.'),
        ('Mehmet', 'Kaya', 'Ortopedi', 'Kemik ve Eklem Hastalıkları', 'Hacettepe Üniversitesi Tıp Fakültesi', 20,
         '08:00:00', '16:00:00', 'Pazartesi,Salı,Çarşamba,Perşembe', '103', '5553456789', 'mehmet.kaya@hastane.com',
         'Dr. Mehmet Kaya, ortopedi alanında uzmanlaşmış deneyimli bir hekimdir.');

-- Create test patient user
-- Password for 'test@example.com' is 'test123' (hashed)
INSERT INTO users (name, surname, email, password, role) VALUES 
        ('Test', 'Hasta', 'test@example.com', '$2y$10$f9.h.n.9.y.z.a.b.c.d.e.f.g.h.i.j.k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z', 'patient');

-- Create test doctor users
-- Passwords for doctors are 'doctor123' (hashed)
INSERT INTO users (name, surname, email, password, role, doctor_id) VALUES 
        ('Ahmet', 'Yılmaz', 'ahmet.yilmaz@hastane.com', '$2y$10$a1b2c3d4e5f6789012345O2aG3I9B7v8J9V0P1R2S3T4U5W6X7Y8Z0.tL1n2H3y4D5F6G', 'doctor', 1),
        ('Ayşe', 'Demir', 'ayse.demir@hastane.com', '$2y$10$b2c3d4e5f6g7890123456O3aH4J8K9L0M1N2P3Q4R5S6T7U8V9W0X1Y2Z3A4B5C6D', 'doctor', 2),
        ('Mehmet', 'Kaya', 'mehmet.kaya@hastane.com', '$2y$10$c3d4e5f6g7h8901234567O4aI5K9L0M1N2P3Q4R5S6T7U8V9W0X1Y2Z3A4B5C6D7E', 'doctor', 3);

-- Insert sample appointments
-- You might need to adjust patient_id and doctor_id based on your user and doctor IDs
-- Make sure the patient_id matches the id of the 'test@example.com' user
-- Make sure the doctor_id matches the ids of the doctors inserted above
INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, notes) VALUES
((SELECT id FROM users WHERE email = 'test@example.com'), (SELECT id FROM doctors WHERE email = 'ahmet.yilmaz@hastane.com'), CURDATE(), '10:00:00', 'confirmed', 'Kontrol muayenesi'),
((SELECT id FROM users WHERE email = 'test@example.com'), (SELECT id FROM doctors WHERE email = 'ayse.demir@hastane.com'), CURDATE() + INTERVAL 1 DAY, '14:00:00', 'pending', 'İlk muayene'),
((SELECT id FROM users WHERE email = 'test@example.com'), (SELECT id FROM doctors WHERE email = 'mehmet.kaya@hastane.com'), CURDATE() + INTERVAL 2 DAY, '11:00:00', 'pending', 'Diz ağrısı şikayeti'); 