-- Add new patient information columns to appointments table
ALTER TABLE appointments
ADD COLUMN patient_surname VARCHAR(100) NOT NULL AFTER patient_name,
ADD COLUMN patient_tc VARCHAR(11) NOT NULL AFTER patient_surname,
ADD COLUMN patient_phone VARCHAR(15) NOT NULL AFTER patient_tc,
ADD COLUMN patient_email VARCHAR(100) NOT NULL AFTER patient_phone;

-- Add index for patient_tc column
CREATE INDEX idx_patient_tc ON appointments(patient_tc); 