# 📋 Datos Requeridos por Tipo de Profesional

## ✅ Implementado: MÉDICOS

### Datos Obligatorios
- ✅ Nombre(s) y Apellidos
- ✅ Email (único)
- ✅ Contraseña
- ✅ Profesión / Título (ej: "Médico Cirujano")
- ✅ **Especialidad Médica** (ej: Cardiología, Pediatría, Medicina General)
  - Mostrada al paciente en selector de especialidades
  - Filtrado de profesionales por especialidad
- ✅ Teléfono / WhatsApp
- ✅ Teléfono Adicional (opcional)
- ✅ Dirección Residencial (opcional)
- ✅ Dirección Consultorio/Oficina
- ✅ Hoja de Vida Digital (PDF)
- ✅ Estado (Activo/Inactivo/Bloqueado)

### Flujo del Paciente
1. Selecciona "Médico"
2. **Sistema muestra especialidades disponibles** cargadas dinámicamente de la BD
3. Paciente elige especialidad (o Medicina General)
4. Continúa con formulario de solicitud
5. La especialidad se guarda en `solicitudes.especialidad_solicitada`
6. Admin ve especialidad al asignar profesionales

---

## 🩺 Pendiente: ENFERMERAS

### Datos Adicionales Sugeridos
- ✅ Nombre(s) y Apellidos
- ✅ Email
- ✅ Profesión (ej: "Enfermera Jefe", "Auxiliar de Enfermería")
- 📌 **Tipo de enfermería:**
  - Enfermería básica
  - Cuidados intensivos
  - Enfermería pediátrica
  - Enfermería geriátrica
  - Cuidados paliativos
- 📌 **Turnos disponibles:**
  - Diurno (6am-6pm)
  - Nocturno (6pm-6am)
  - 24 horas
  - Por horas
- 📌 Experiencia en años
- 📌 Certificaciones especiales (opcional)
- ✅ Teléfono / WhatsApp
- ✅ Dirección
- ✅ Hoja de Vida

### Flujo del Paciente
1. Selecciona "Enfermería"
2. Especifica tipo de cuidado requerido
3. Selecciona turno e intensidad horaria
4. Opcionalmente puede preferir género del profesional
5. Sistema filtra enfermeras disponibles por criterios

---

## 🐾 Pendiente: VETERINARIOS

### Datos Adicionales Sugeridos
- ✅ Nombre(s) y Apellidos
- ✅ Email
- ✅ Profesión (ej: "Médico Veterinario")
- 📌 **Especialidad veterinaria:**
  - Medicina general veterinaria
  - Cirugía veterinaria
  - Dermatología veterinaria
  - Cardiología veterinaria
  - Ortopedia veterinaria
  - Exóticos (aves, reptiles)
- 📌 **Especies que atiende:**
  - Perros
  - Gatos
  - Aves
  - Reptiles
  - Pequeñas especies (hamsters, conejos)
- 📌 Número de tarjeta profesional
- ✅ Teléfono / WhatsApp
- ✅ Dirección Consultorio
- ✅ Hoja de Vida

### Flujo del Paciente
1. Selecciona "Veterinaria"
2. Indica tipo de mascota y motivo
3. Puede filtrar por especialidad si es necesaria
4. Sistema muestra veterinarios que atienden esa especie

---

## 🔬 Pendiente: TÉCNICOS DE LABORATORIO

### Datos Adicionales Sugeridos
- ✅ Nombre(s) y Apellidos
- ✅ Email
- ✅ Profesión (ej: "Bacteriólogo", "Técnico de Laboratorio")
- 📌 **Tipos de exámenes que realiza:**
  - Hematología
  - Química sanguínea
  - Microbiología
  - Urianálisis
  - Serología
  - Pruebas rápidas (COVID, influenza, etc.)
- 📌 **Equipamiento disponible:**
  - Equipos portátiles básicos
  - Equipos especializados
  - Requiere laboratorio fijo
- 📌 Certificación de bioseguridad
- ✅ Teléfono / WhatsApp
- ✅ Zona de cobertura
- ✅ Hoja de Vida

### Flujo del Paciente
1. Selecciona "Laboratorio"
2. Elige exámenes requeridos de lista predefinida
3. Indica si requiere ayuno o preparación especial
4. Sistema asigna técnico con equipamiento adecuado

---

## 🚑 Pendiente: OPERADORES DE AMBULANCIA

### Datos Adicionales Sugeridos
- ✅ Nombre(s) y Apellidos
- ✅ Email
- ✅ Profesión (ej: "Técnico en APH", "Paramédico")
- 📌 **Tipo de ambulancia:**
  - Ambulancia básica (traslado simple)
  - Ambulancia medicalizada (UCI móvil)
  - Ambulancia neonatal
  - Ambulancia psiquiátrica
- 📌 **Certificaciones:**
  - Soporte vital básico (SVB)
  - Soporte vital avanzado (SVA)
  - Manejo de paciente crítico
- 📌 **Equipo disponible:**
  - Desfibrilador
  - Ventilador mecánico
  - Monitor de signos vitales
  - Equipo de inmovilización
- 📌 Placa del vehículo
- 📌 Capacidad de pasajeros (incluye acompañantes)
- ✅ Teléfono / WhatsApp
- ✅ Zona de cobertura
- ✅ Hoja de Vida

### Flujo del Paciente
1. Selecciona "Ambulancia"
2. Indica tipo de emergencia (programado/urgente)
3. Especifica origen y destino
4. Describe condición del paciente
5. Sistema asigna ambulancia del tipo apropiado

---

## 📊 Campos Comunes a Todos los Profesionales

### Obligatorios
- ✅ Nombre(s)
- ✅ Apellidos
- ✅ Email (único en el sistema)
- ✅ Contraseña (hash seguro)
- ✅ Tipo de Profesional (medico/enfermera/veterinario/laboratorio/ambulancia)
- ✅ Profesión / Título
- ✅ Teléfono WhatsApp

### Opcionales pero Recomendados
- ✅ Teléfono adicional
- ✅ Dirección residencial
- ✅ Dirección de consultorio/oficina
- ✅ Hoja de vida digital (PDF)
- ✅ Estado (activo/inactivo/bloqueado)
- 📌 Foto de perfil
- 📌 Número de documento
- 📌 Fecha de nacimiento
- 📌 Años de experiencia
- 📌 Universidad/Institución

### Campos del Sistema
- ✅ `puntuacion_promedio` (calificación)
- ✅ `total_calificaciones` (cantidad de evaluaciones)
- ✅ Servicios completados
- ✅ Fecha de registro
- ✅ Último acceso

---

## 🔄 Próximos Pasos de Implementación

### Alta Prioridad
1. **Agregar campo `especialidad_veterinaria`** a tabla usuarios
2. **Agregar campo `tipo_cuidado_enfermeria`** a tabla usuarios
3. **Agregar campo `tipo_ambulancia`** a tabla usuarios
4. **Agregar campo `tipo_examen_laboratorio`** a tabla usuarios

### Media Prioridad
5. Modificar formulario de profesionales para campos específicos por tipo
6. Actualizar lógica de filtrado en asignación por especialidad/tipo
7. Agregar validaciones específicas por tipo de profesional

### Baja Prioridad
8. Sistema de upload de documentos (hoja de vida, certificaciones)
9. Galería de fotos de consultorio
10. Sistema de reseñas y comentarios públicos

---

## 💡 Notas de Implementación

### Base de Datos Actual
```sql
-- Campos ya existentes en tabla usuarios
tipo_profesional ENUM('medico','enfermera','veterinario','laboratorio','ambulancia')
especialidad VARCHAR(100)  -- Actualmente solo para médicos
profesion VARCHAR(100)
telefono_whatsapp VARCHAR(20)
direccion TEXT
direccion_consultorio TEXT
hoja_vida_url VARCHAR(255)
estado ENUM('pendiente','activo','inactivo','bloqueado')
```

### Campos a Agregar (Opcional)
```sql
-- Campos específicos adicionales
especialidad_veterinaria VARCHAR(100)
especies_atiende JSON  -- ['perros', 'gatos', 'aves']
tipo_cuidado_enfermeria VARCHAR(100)
turnos_disponibles JSON  -- ['diurno', 'nocturno']
tipo_ambulancia VARCHAR(50)
certificaciones_aph JSON
tipo_examen_laboratorio JSON
equipamiento_disponible JSON
```

### API Endpoints Implementados
- ✅ `GET /api/especialidades` - Lista especialidades médicas únicas
- ✅ `GET /api/admin/profesionales` - Lista profesionales con filtros
- ✅ `POST /api/admin/profesionales` - Crear nuevo profesional
- ✅ `PUT /api/admin/profesionales/{id}` - Actualizar profesional
- ✅ Auto-asignación de servicios al crear profesional

---

## ✅ Estado Actual del Sistema

### ✅ Completamente Funcional
- CRUD completo de profesionales médicos
- Selector de especialidades para pacientes
- Filtrado de profesionales por especialidad
- Guardado de especialidad en solicitudes
- Vista de administración con tabs
- Modal de creación/edición de profesionales
- Validaciones de email único
- Auto-asignación a servicios

### 🔧 Listo para Extender
- Estructura lista para agregar campos específicos
- Formulario adaptable por tipo de profesional
- API preparada para campos adicionales
- Base de datos flexible (campos JSON disponibles)

---

**Fecha:** Noviembre 2025  
**Versión:** 1.0.0  
**Estado:** Médicos implementado ✅ | Otros profesionales en diseño 📋
