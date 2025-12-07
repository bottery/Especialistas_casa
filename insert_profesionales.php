<?php
/**
 * Script para insertar profesionales de prueba con codificación UTF-8 correcta
 */

require_once __DIR__ . '/bootstrap.php';

try {
    $config = require __DIR__ . '/config/database.php';
    $dbConfig = $config['connections']['mysql'];
    
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
    
    // Asegurar que usamos UTF-8
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    
    // Profesionales con tildes correctas
    $profesionales = [
        [
            'email' => 'dr.garcia@especialistas.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'nombre' => 'Carlos',
            'apellido' => 'García',
            'telefono' => '3001234567',
            'direccion' => 'Calle 123 #45-67, Bogotá',
            'rol' => 'profesional',
            'tipo_profesional' => 'medico',
            'puntuacion_promedio' => 4.8,
            'total_calificaciones' => 25,
            'servicios_completados' => 42,
            'perfil' => [
                'especialidad' => 'Medicina General',
                'descripcion' => 'Médico general con 10 años de experiencia en atención domiciliaria',
                'registro_profesional' => 'MP-12345',
                'tarifa_consulta_virtual' => 60000,
                'tarifa_consulta_presencial' => 80000
            ]
        ],
        [
            'email' => 'dra.martinez@especialistas.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'nombre' => 'María',
            'apellido' => 'Martínez',
            'telefono' => '3009876543',
            'direccion' => 'Carrera 50 #30-20, Medellín',
            'rol' => 'profesional',
            'tipo_profesional' => 'medico',
            'puntuacion_promedio' => 4.9,
            'total_calificaciones' => 38,
            'servicios_completados' => 65,
            'perfil' => [
                'especialidad' => 'Pediatría',
                'descripcion' => 'Pediatra especializada en atención infantil y neonatal',
                'registro_profesional' => 'MP-23456',
                'tarifa_consulta_virtual' => 70000,
                'tarifa_consulta_presencial' => 90000
            ]
        ],
        [
            'email' => 'enf.lopez@especialistas.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'nombre' => 'Ana',
            'apellido' => 'López',
            'telefono' => '3105551234',
            'direccion' => 'Avenida 68 #15-30, Bogotá',
            'rol' => 'profesional',
            'tipo_profesional' => 'enfermera',
            'puntuacion_promedio' => 4.7,
            'total_calificaciones' => 52,
            'servicios_completados' => 120,
            'perfil' => [
                'especialidad' => 'Enfermería General',
                'descripcion' => 'Enfermera profesional con experiencia en cuidados domiciliarios',
                'registro_profesional' => 'ENF-34567',
                'tarifa_consulta_virtual' => 40000,
                'tarifa_consulta_presencial' => 50000
            ]
        ],
        [
            'email' => 'vet.rodriguez@especialistas.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'nombre' => 'Pedro',
            'apellido' => 'Rodríguez',
            'telefono' => '3201234567',
            'direccion' => 'Calle 80 #25-10, Cali',
            'rol' => 'profesional',
            'tipo_profesional' => 'veterinario',
            'puntuacion_promedio' => 4.6,
            'total_calificaciones' => 18,
            'servicios_completados' => 30,
            'perfil' => [
                'especialidad' => 'Medicina Veterinaria',
                'descripcion' => 'Veterinario con especialidad en pequeñas especies',
                'registro_profesional' => 'VET-45678',
                'tarifa_consulta_virtual' => 50000,
                'tarifa_consulta_presencial' => 70000
            ]
        ],
        [
            'email' => 'lab.sanchez@especialistas.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'nombre' => 'Laura',
            'apellido' => 'Sánchez',
            'telefono' => '3159876543',
            'direccion' => 'Carrera 15 #100-50, Bogotá',
            'rol' => 'profesional',
            'tipo_profesional' => 'laboratorio',
            'puntuacion_promedio' => 4.5,
            'total_calificaciones' => 15,
            'servicios_completados' => 0,
            'perfil' => [
                'especialidad' => 'Toma de Muestras',
                'descripcion' => 'Técnica en laboratorio clínico especializada en toma de muestras a domicilio',
                'registro_profesional' => 'LAB-56789',
                'tarifa_consulta_virtual' => 30000,
                'tarifa_consulta_presencial' => 45000
            ]
        ]
    ];
    
    $insertedCount = 0;
    
    foreach ($profesionales as $prof) {
        // Insertar usuario
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (email, password, nombre, apellido, telefono, direccion, rol, tipo_profesional, puntuacion_promedio, total_calificaciones, servicios_completados, verificado, created_at)
            VALUES (:email, :password, :nombre, :apellido, :telefono, :direccion, :rol, :tipo_profesional, :puntuacion_promedio, :total_calificaciones, :servicios_completados, 1, NOW())
        ");
        
        $stmt->execute([
            'email' => $prof['email'],
            'password' => $prof['password'],
            'nombre' => $prof['nombre'],
            'apellido' => $prof['apellido'],
            'telefono' => $prof['telefono'],
            'direccion' => $prof['direccion'],
            'rol' => $prof['rol'],
            'tipo_profesional' => $prof['tipo_profesional'],
            'puntuacion_promedio' => $prof['puntuacion_promedio'],
            'total_calificaciones' => $prof['total_calificaciones'],
            'servicios_completados' => $prof['servicios_completados']
        ]);
        
        $usuarioId = $pdo->lastInsertId();
        
        // Insertar perfil profesional
        $stmtPerfil = $pdo->prepare("
            INSERT INTO perfiles_profesionales (usuario_id, especialidad, descripcion, registro_profesional, tarifa_consulta_virtual, tarifa_consulta_presencial, aprobado, created_at)
            VALUES (:usuario_id, :especialidad, :descripcion, :registro_profesional, :tarifa_consulta_virtual, :tarifa_consulta_presencial, 1, NOW())
        ");
        
        $stmtPerfil->execute([
            'usuario_id' => $usuarioId,
            'especialidad' => $prof['perfil']['especialidad'],
            'descripcion' => $prof['perfil']['descripcion'],
            'registro_profesional' => $prof['perfil']['registro_profesional'],
            'tarifa_consulta_virtual' => $prof['perfil']['tarifa_consulta_virtual'],
            'tarifa_consulta_presencial' => $prof['perfil']['tarifa_consulta_presencial']
        ]);
        
        echo "✓ Insertado: {$prof['nombre']} {$prof['apellido']} (ID: {$usuarioId})\n";
        $insertedCount++;
    }
    
    echo "\n✅ Se insertaron {$insertedCount} profesionales correctamente.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
