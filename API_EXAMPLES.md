# 📡 EJEMPLOS DE USO DE LA API
# Especialistas en Casa

## 🔧 CONFIGURACIÓN INICIAL

Base URL: `http://localhost:8000/api` (desarrollo)
Base URL: `https://tudominio.com/api` (producción)

Headers requeridos:
```
Content-Type: application/json
Authorization: Bearer {TOKEN_JWT}  # Para rutas protegidas
```

---

## 🚀 EJEMPLOS CON cURL

### 1. REGISTRO DE NUEVO PACIENTE

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "paciente@test.com",
    "password": "MiPassword123!",
    "nombre": "Juan",
    "apellido": "Pérez",
    "telefono": "3001234567",
    "rol": "paciente"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "user_id": 3,
  "requires_approval": false
}
```

---

### 2. REGISTRO DE MÉDICO (requiere aprobación)

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "medico@test.com",
    "password": "MiPassword123!",
    "nombre": "María",
    "apellido": "García",
    "telefono": "3007654321",
    "rol": "medico",
    "documento_tipo": "CC",
    "documento_numero": "1234567890"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "user_id": 4,
  "requires_approval": true
}
```

---

### 3. INICIAR SESIÓN

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "paciente@test.com",
    "password": "MiPassword123!"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Inicio de sesión exitoso",
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "user": {
    "id": 3,
    "email": "paciente@test.com",
    "rol": "paciente",
    "nombre": "Juan",
    "apellido": "Pérez",
    "estado": "activo"
  }
}
```

**⚠️ IMPORTANTE:** Guardar el `access_token` para las siguientes peticiones.

---

### 4. LISTAR SERVICIOS DISPONIBLES

```bash
curl -X GET http://localhost:8000/api/paciente/servicios \
  -H "Authorization: Bearer TU_ACCESS_TOKEN"
```

**Respuesta esperada:**
```json
{
  "success": true,
  "servicios": [
    {
      "id": 1,
      "nombre": "Consulta Médica Virtual",
      "descripcion": "Consulta médica general por videollamada",
      "tipo": "medico",
      "modalidad": "virtual",
      "precio_base": "50000.00",
      "duracion_estimada": 30
    },
    {
      "id": 2,
      "nombre": "Consulta Médica a Domicilio",
      "descripcion": "Consulta médica general en su hogar",
      "tipo": "medico",
      "modalidad": "presencial",
      "precio_base": "80000.00",
      "duracion_estimada": 45
    }
  ]
}
```

---

### 5. SOLICITAR UN SERVICIO

```bash
curl -X POST http://localhost:8000/api/paciente/solicitar \
  -H "Authorization: Bearer TU_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "servicio_id": 1,
    "modalidad": "virtual",
    "fecha_programada": "2025-11-20 14:00:00",
    "sintomas": "Dolor de cabeza persistente",
    "observaciones": "Prefiero consulta por la tarde"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Solicitud creada exitosamente",
  "solicitud_id": 1,
  "monto_total": "50000.00"
}
```

---

### 6. VER HISTORIAL DE SERVICIOS

```bash
curl -X GET http://localhost:8000/api/paciente/historial \
  -H "Authorization: Bearer TU_ACCESS_TOKEN"
```

**Respuesta esperada:**
```json
{
  "success": true,
  "solicitudes": [
    {
      "id": 1,
      "servicio_nombre": "Consulta Médica Virtual",
      "tipo": "medico",
      "modalidad": "virtual",
      "fecha_programada": "2025-11-20 14:00:00",
      "estado": "pendiente",
      "monto_total": "50000.00",
      "profesional_nombre": null,
      "profesional_apellido": null
    }
  ]
}
```

---

### 7. VER DETALLE DE UNA SOLICITUD

```bash
curl -X GET "http://localhost:8000/api/paciente/solicitud?id=1" \
  -H "Authorization: Bearer TU_ACCESS_TOKEN"
```

**Respuesta esperada:**
```json
{
  "success": true,
  "solicitud": {
    "id": 1,
    "paciente_id": 3,
    "servicio_id": 1,
    "modalidad": "virtual",
    "fecha_programada": "2025-11-20 14:00:00",
    "sintomas": "Dolor de cabeza persistente",
    "estado": "pendiente",
    "monto_total": "50000.00",
    "pagado": false
  }
}
```

---

### 8. CANCELAR UNA SOLICITUD

```bash
curl -X POST http://localhost:8000/api/paciente/cancelar \
  -H "Authorization: Bearer TU_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "solicitud_id": 1,
    "razon": "No podré asistir a la cita programada"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Solicitud cancelada exitosamente"
}
```

---

### 9. RENOVAR TOKEN

```bash
curl -X POST http://localhost:8000/api/refresh-token \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "TU_REFRESH_TOKEN"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer"
}
```

---

### 10. CERRAR SESIÓN

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer TU_ACCESS_TOKEN"
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente"
}
```

---

## 🧪 EJEMPLOS CON POSTMAN

### Importar Collection

1. Abrir Postman
2. Importar → Raw Text
3. Pegar este JSON:

```json
{
  "info": {
    "name": "Especialistas en Casa API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api"
    },
    {
      "key": "token",
      "value": ""
    }
  ],
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Register",
          "request": {
            "method": "POST",
            "header": [{"key": "Content-Type", "value": "application/json"}],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"test@test.com\",\n  \"password\": \"Password123!\",\n  \"nombre\": \"Test\",\n  \"apellido\": \"User\",\n  \"rol\": \"paciente\"\n}"
            },
            "url": "{{base_url}}/register"
          }
        },
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [{"key": "Content-Type", "value": "application/json"}],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"test@test.com\",\n  \"password\": \"Password123!\"\n}"
            },
            "url": "{{base_url}}/login"
          }
        }
      ]
    }
  ]
}
```

---

## 🐍 EJEMPLOS CON PYTHON (requests)

### Instalar biblioteca
```bash
pip install requests
```

### Script de ejemplo

```python
import requests
import json

# Configuración
BASE_URL = "http://localhost:8000/api"

# 1. Registro
def register_user():
    url = f"{BASE_URL}/register"
    data = {
        "email": "python@test.com",
        "password": "Password123!",
        "nombre": "Python",
        "apellido": "Test",
        "rol": "paciente"
    }
    response = requests.post(url, json=data)
    return response.json()

# 2. Login
def login():
    url = f"{BASE_URL}/login"
    data = {
        "email": "python@test.com",
        "password": "Password123!"
    }
    response = requests.post(url, json=data)
    result = response.json()
    return result.get('access_token')

# 3. Listar servicios
def get_services(token):
    url = f"{BASE_URL}/paciente/servicios"
    headers = {"Authorization": f"Bearer {token}"}
    response = requests.get(url, headers=headers)
    return response.json()

# 4. Solicitar servicio
def request_service(token):
    url = f"{BASE_URL}/paciente/solicitar"
    headers = {"Authorization": f"Bearer {token}"}
    data = {
        "servicio_id": 1,
        "modalidad": "virtual",
        "fecha_programada": "2025-11-20 14:00:00",
        "sintomas": "Prueba desde Python"
    }
    response = requests.post(url, json=data, headers=headers)
    return response.json()

# Ejecutar
if __name__ == "__main__":
    # Registrar
    print("1. Registrando usuario...")
    print(register_user())
    
    # Login
    print("\n2. Iniciando sesión...")
    token = login()
    print(f"Token obtenido: {token[:50]}...")
    
    # Listar servicios
    print("\n3. Obteniendo servicios...")
    services = get_services(token)
    print(json.dumps(services, indent=2))
    
    # Solicitar servicio
    print("\n4. Solicitando servicio...")
    request = request_service(token)
    print(json.dumps(request, indent=2))
```

---

## 📱 EJEMPLOS CON JAVASCRIPT (Fetch)

```javascript
const BASE_URL = 'http://localhost:8000/api';

// 1. Registro
async function register() {
  const response = await fetch(`${BASE_URL}/register`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      email: 'js@test.com',
      password: 'Password123!',
      nombre: 'JavaScript',
      apellido: 'Test',
      rol: 'paciente'
    })
  });
  return await response.json();
}

// 2. Login
async function login() {
  const response = await fetch(`${BASE_URL}/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      email: 'js@test.com',
      password: 'Password123!'
    })
  });
  const data = await response.json();
  localStorage.setItem('token', data.access_token);
  return data;
}

// 3. Listar servicios
async function getServices() {
  const token = localStorage.getItem('token');
  const response = await fetch(`${BASE_URL}/paciente/servicios`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return await response.json();
}

// 4. Solicitar servicio
async function requestService() {
  const token = localStorage.getItem('token');
  const response = await fetch(`${BASE_URL}/paciente/solicitar`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      servicio_id: 1,
      modalidad: 'virtual',
      fecha_programada: '2025-11-20 14:00:00',
      sintomas: 'Prueba desde JavaScript'
    })
  });
  return await response.json();
}

// Uso
(async () => {
  console.log('1. Registrando...', await register());
  console.log('2. Login...', await login());
  console.log('3. Servicios...', await getServices());
  console.log('4. Solicitar...', await requestService());
})();
```

---

## ⚠️ CÓDIGOS DE ESTADO HTTP

- `200` - OK (éxito)
- `201` - Created (recurso creado)
- `400` - Bad Request (datos inválidos)
- `401` - Unauthorized (no autenticado)
- `403` - Forbidden (sin permisos)
- `404` - Not Found (recurso no encontrado)
- `409` - Conflict (email duplicado, etc.)
- `500` - Internal Server Error (error del servidor)
- `501` - Not Implemented (endpoint en desarrollo)

---

## 🔍 DEBUGGING

### Ver logs en tiempo real
```bash
tail -f storage/logs/error.log
```

### Habilitar debug en .env
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Headers útiles para debugging
```
X-Debug: true
X-Request-Id: unique-id-123
```

---

## 📚 RECURSOS ADICIONALES

- **Documentación completa:** README.md
- **Guía de inicio:** QUICKSTART.md
- **Estructura del proyecto:** STRUCTURE.txt
- **Deployment:** DEPLOYMENT.md

---

**¡Listo para probar la API!** 🚀

Recuerda que puedes probar todos estos endpoints desde:
- cURL (terminal)
- Postman
- Insomnia
- Python scripts
- JavaScript/Fetch
- Cualquier cliente HTTP
