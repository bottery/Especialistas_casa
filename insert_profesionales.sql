SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Profesionales de ejemplo
INSERT INTO usuarios (email, password, rol, nombre, apellido, telefono, ciudad, estado, verificado, puntuacion_promedio, total_calificaciones) VALUES
('dr.garcia@especialistas.com', '$2y$10$7YO5wo.EAn9kKLQx3PckBuj0k1R97PYb.gJj6u9KxxnexjWZ13B.W', 'medico', 'Carlos', 'García', '3001234567', 'Bogotá', 'activo', TRUE, 4.5, 12),
('dra.martinez@especialistas.com', '$2y$10$7YO5wo.EAn9kKLQx3PckBuj0k1R97PYb.gJj6u9KxxnexjWZ13B.W', 'medico', 'María', 'Martínez', '3002345678', 'Bogotá', 'activo', TRUE, 4.8, 25),
('enf.lopez@especialistas.com', '$2y$10$7YO5wo.EAn9kKLQx3PckBuj0k1R97PYb.gJj6u9KxxnexjWZ13B.W', 'enfermera', 'Ana', 'López', '3003456789', 'Medellín', 'activo', TRUE, 4.2, 8),
('vet.rodriguez@especialistas.com', '$2y$10$7YO5wo.EAn9kKLQx3PckBuj0k1R97PYb.gJj6u9KxxnexjWZ13B.W', 'veterinario', 'Pedro', 'Rodríguez', '3004567890', 'Cali', 'activo', TRUE, 4.7, 15),
('lab.sanchez@especialistas.com', '$2y$10$7YO5wo.EAn9kKLQx3PckBuj0k1R97PYb.gJj6u9KxxnexjWZ13B.W', 'laboratorio', 'Laura', 'Sánchez', '3005678901', 'Bogotá', 'activo', TRUE, 4.9, 30);

-- Perfiles profesionales
INSERT INTO perfiles_profesionales (usuario_id, especialidad, registro_profesional, universidad, descripcion, aprobado) VALUES
((SELECT id FROM usuarios WHERE email='dr.garcia@especialistas.com'), 'Medicina General', 'RM-12345', 'Universidad Nacional', 'Médico general con 10 años de experiencia', TRUE),
((SELECT id FROM usuarios WHERE email='dra.martinez@especialistas.com'), 'Pediatría', 'RM-23456', 'Universidad de los Andes', 'Especialista en pediatría y neonatología', TRUE),
((SELECT id FROM usuarios WHERE email='enf.lopez@especialistas.com'), 'Enfermería General', 'RE-34567', 'Universidad Javeriana', 'Enfermera con experiencia en cuidados domiciliarios', TRUE),
((SELECT id FROM usuarios WHERE email='vet.rodriguez@especialistas.com'), 'Veterinaria General', 'RV-45678', 'Universidad de Antioquia', 'Veterinario especializado en pequeñas especies', TRUE),
((SELECT id FROM usuarios WHERE email='lab.sanchez@especialistas.com'), 'Laboratorio Clínico', 'RL-56789', 'Universidad del Rosario', 'Bacterióloga con especialización en análisis clínicos', TRUE);
