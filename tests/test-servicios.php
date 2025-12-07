<?php
/**
 * Script de pruebas para verificar todos los servicios
 * Ejecutar desde: https://localhost/VitaHome/tests/test-servicios.php
 */

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test de Servicios - VitaHome</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">🧪 Test de Servicios VitaHome</h1>
        
        <?php
        $db = App\Services\Database::getInstance();
        $errores = [];
        $exitos = [];
        
        // =============================================
        // 1. VERIFICAR SERVICIOS EN BD
        // =============================================
        echo '<div class="bg-white rounded-lg shadow p-6 mb-4">';
        echo '<h2 class="text-xl font-semibold mb-4">📋 1. Servicios en Base de Datos</h2>';
        
        $servicios = $db->select("SELECT id, nombre, tipo, modalidad, precio_base, activo FROM servicios ORDER BY tipo");
        
        if (count($servicios) > 0) {
            echo '<table class="w-full text-sm">';
            echo '<tr class="bg-gray-100"><th class="p-2 text-left">ID</th><th class="p-2 text-left">Nombre</th><th class="p-2 text-left">Tipo</th><th class="p-2 text-left">Modalidad</th><th class="p-2 text-left">Precio</th><th class="p-2 text-left">Estado</th></tr>';
            
            $tiposConServicio = [];
            foreach ($servicios as $s) {
                $estado = $s['activo'] ? '✅ Activo' : '❌ Inactivo';
                echo "<tr><td class='p-2'>{$s['id']}</td><td class='p-2'>{$s['nombre']}</td><td class='p-2'>{$s['tipo']}</td><td class='p-2'>{$s['modalidad']}</td><td class='p-2'>$" . number_format($s['precio_base'], 0) . "</td><td class='p-2'>$estado</td></tr>";
                $tiposConServicio[$s['tipo']] = true;
            }
            echo '</table>';
            
            // Verificar tipos requeridos
            $tiposRequeridos = ['medico', 'enfermera', 'ambulancia', 'veterinario', 'laboratorio'];
            foreach ($tiposRequeridos as $tipo) {
                if (!isset($tiposConServicio[$tipo])) {
                    $errores[] = "❌ Falta servicio tipo: $tipo";
                } else {
                    $exitos[] = "✅ Servicio $tipo configurado";
                }
            }
        } else {
            $errores[] = "❌ No hay servicios en la base de datos";
        }
        echo '</div>';
        
        // =============================================
        // 2. VERIFICAR PROFESIONALES POR TIPO
        // =============================================
        echo '<div class="bg-white rounded-lg shadow p-6 mb-4">';
        echo '<h2 class="text-xl font-semibold mb-4">👨‍⚕️ 2. Profesionales por Tipo</h2>';
        
        $profesionales = $db->select("
            SELECT 
                u.tipo_profesional,
                COUNT(*) as total,
                SUM(CASE WHEN u.estado = 'activo' THEN 1 ELSE 0 END) as activos
            FROM usuarios u
            WHERE u.tipo_profesional IS NOT NULL
            GROUP BY u.tipo_profesional
        ");
        
        echo '<table class="w-full text-sm">';
        echo '<tr class="bg-gray-100"><th class="p-2 text-left">Tipo</th><th class="p-2 text-left">Total</th><th class="p-2 text-left">Activos</th><th class="p-2 text-left">Estado</th></tr>';
        
        $tiposProf = [];
        foreach ($profesionales as $p) {
            $tiposProf[$p['tipo_profesional']] = $p['activos'];
            $estado = $p['activos'] > 0 ? '✅' : '⚠️ Sin activos';
            echo "<tr><td class='p-2 font-medium'>{$p['tipo_profesional']}</td><td class='p-2'>{$p['total']}</td><td class='p-2'>{$p['activos']}</td><td class='p-2'>$estado</td></tr>";
        }
        echo '</table>';
        
        // Verificar profesionales requeridos
        foreach ($tiposRequeridos as $tipo) {
            if (!isset($tiposProf[$tipo]) || $tiposProf[$tipo] == 0) {
                $errores[] = "⚠️ No hay profesionales activos de tipo: $tipo";
            }
        }
        echo '</div>';
        
        // =============================================
        // 3. VERIFICAR ESPECIALIDADES
        // =============================================
        echo '<div class="bg-white rounded-lg shadow p-6 mb-4">';
        echo '<h2 class="text-xl font-semibold mb-4">🏥 3. Especialidades Médicas</h2>';
        
        $especialidades = $db->select("
            SELECT DISTINCT pp.especialidad, u.tipo_profesional, COUNT(*) as total
            FROM perfiles_profesionales pp
            INNER JOIN usuarios u ON pp.usuario_id = u.id
            WHERE pp.especialidad IS NOT NULL AND pp.especialidad != '' AND u.estado = 'activo'
            GROUP BY pp.especialidad, u.tipo_profesional
        ");
        
        if (count($especialidades) > 0) {
            echo '<ul class="list-disc pl-5">';
            foreach ($especialidades as $e) {
                echo "<li>{$e['especialidad']} ({$e['tipo_profesional']}) - {$e['total']} profesional(es)</li>";
            }
            echo '</ul>';
            $exitos[] = "✅ Especialidades configuradas: " . count($especialidades);
        } else {
            $errores[] = "⚠️ No hay especialidades configuradas";
        }
        echo '</div>';
        
        // =============================================
        // 4. VERIFICAR ENDPOINTS API
        // =============================================
        echo '<div class="bg-white rounded-lg shadow p-6 mb-4">';
        echo '<h2 class="text-xl font-semibold mb-4">🔌 4. Endpoints API</h2>';
        
        $endpoints = [
            '/api/servicios' => 'GET',
            '/api/especialidades' => 'GET',
            '/api/especialidades?tipo=medico' => 'GET',
        ];
        
        echo '<table class="w-full text-sm">';
        echo '<tr class="bg-gray-100"><th class="p-2 text-left">Endpoint</th><th class="p-2 text-left">Método</th><th class="p-2 text-left">Estado</th></tr>';
        
        foreach ($endpoints as $endpoint => $method) {
            $url = 'https://localhost/VitaHome' . $endpoint;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $estado = $httpCode == 200 ? '✅ OK' : "❌ HTTP $httpCode";
            $color = $httpCode == 200 ? 'text-green-600' : 'text-red-600';
            echo "<tr><td class='p-2'>{$endpoint}</td><td class='p-2'>{$method}</td><td class='p-2 $color'>$estado</td></tr>";
            
            if ($httpCode != 200) {
                $errores[] = "❌ Endpoint falla: $endpoint (HTTP $httpCode)";
            }
        }
        echo '</table>';
        echo '</div>';
        
        // =============================================
        // 5. VERIFICAR FORMULARIOS POR SERVICIO
        // =============================================
        echo '<div class="bg-white rounded-lg shadow p-6 mb-4">';
        echo '<h2 class="text-xl font-semibold mb-4">📝 5. Campos Requeridos por Servicio</h2>';
        
        $camposRequeridos = [
            'medico' => ['fecha_programada', 'rango_horario', 'sintomas', 'telefono_contacto'],
            'enfermera' => ['fecha_programada', 'tipo_cuidado', 'duracion_cantidad', 'direccion_servicio', 'telefono_contacto'],
            'ambulancia' => ['fecha_programada', 'hora_programada', 'origen', 'destino', 'condicion_paciente'],
            'veterinario' => ['fecha_programada', 'rango_horario', 'tipo_mascota', 'nombre_mascota', 'telefono_contacto'],
            'laboratorio' => ['fecha_programada', 'examenes_solicitados', 'direccion_servicio', 'email_resultados', 'telefono_contacto']
        ];
        
        foreach ($camposRequeridos as $tipo => $campos) {
            echo "<div class='mb-3'>";
            echo "<strong class='text-indigo-600'>" . ucfirst($tipo) . ":</strong> ";
            echo implode(', ', $campos);
            echo "</div>";
        }
        echo '</div>';
        
        // =============================================
        // 6. VERIFICAR SOLICITUDES EXISTENTES
        // =============================================
        echo '<div class="bg-white rounded-lg shadow p-6 mb-4">';
        echo '<h2 class="text-xl font-semibold mb-4">📊 6. Solicitudes por Estado</h2>';
        
        $solicitudes = $db->select("
            SELECT estado, COUNT(*) as total
            FROM solicitudes
            GROUP BY estado
        ");
        
        echo '<table class="w-full text-sm">';
        echo '<tr class="bg-gray-100"><th class="p-2 text-left">Estado</th><th class="p-2 text-left">Total</th></tr>';
        foreach ($solicitudes as $s) {
            echo "<tr><td class='p-2'>{$s['estado']}</td><td class='p-2'>{$s['total']}</td></tr>";
        }
        echo '</table>';
        
        // Solicitudes por tipo de servicio
        $solPorTipo = $db->select("
            SELECT s.tipo as servicio_tipo, COUNT(*) as total
            FROM solicitudes sol
            INNER JOIN servicios s ON sol.servicio_id = s.id
            GROUP BY s.tipo
        ");
        
        if (count($solPorTipo) > 0) {
            echo '<h3 class="font-semibold mt-4 mb-2">Por tipo de servicio:</h3>';
            echo '<ul class="list-disc pl-5">';
            foreach ($solPorTipo as $s) {
                echo "<li>{$s['servicio_tipo']}: {$s['total']}</li>";
            }
            echo '</ul>';
        }
        echo '</div>';
        
        // =============================================
        // RESUMEN DE ERRORES Y ÉXITOS
        // =============================================
        echo '<div class="grid grid-cols-2 gap-4">';
        
        // Éxitos
        echo '<div class="bg-green-50 border border-green-200 rounded-lg p-4">';
        echo '<h3 class="text-lg font-semibold text-green-800 mb-2">✅ Verificaciones Exitosas</h3>';
        if (count($exitos) > 0) {
            echo '<ul class="list-disc pl-5 text-green-700">';
            foreach ($exitos as $e) {
                echo "<li>$e</li>";
            }
            echo '</ul>';
        } else {
            echo '<p class="text-green-600">Sin verificaciones adicionales</p>';
        }
        echo '</div>';
        
        // Errores
        echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4">';
        echo '<h3 class="text-lg font-semibold text-red-800 mb-2">❌ Problemas Encontrados</h3>';
        if (count($errores) > 0) {
            echo '<ul class="list-disc pl-5 text-red-700">';
            foreach ($errores as $e) {
                echo "<li>$e</li>";
            }
            echo '</ul>';
        } else {
            echo '<p class="text-green-600">🎉 No se encontraron errores</p>';
        }
        echo '</div>';
        
        echo '</div>';
        ?>
        
        <div class="mt-6 text-center">
            <a href="<?= BASE_URL ?>/paciente/nueva-solicitud" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700">
                🧪 Probar Crear Solicitud
            </a>
        </div>
    </div>
</body>
</html>
