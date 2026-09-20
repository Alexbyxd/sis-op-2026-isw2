# Sistema de Interoperabilidad de Almacenamiento Policial (Bucket Service)

Servicio backend de almacenamiento desacoplado e interoperable diseñado para la Policía, facilitando la persistencia segura, aislamiento estricto multi-inquilino (multi-tenant), streaming binario directo y generación de URLs temporales firmadas para la visualización en frontend de sistemas externos.

---

## 🏛️ Arquitectura del Ecosistema

El sistema actúa como un servicio centralizado de almacenamiento on-premise (intranet local sin dependencia de la nube) para dos sistemas cliente:

1. **Sistema 1 (Correspondencia):** Almacena oficios, informes, circulares y documentación interna.
2. **Sistema 2 (Manejo de Oficiales):** Almacena legajos policiales, expedientes de personal y registros biométricos/fotográficos.
3. **Servicio de Almacenamiento (Este repositorio):** Custodia y sirve los archivos de forma aislada.

```
┌─────────────────────────┐          ┌─────────────────────────┐
│ Sistema 1               │          │ Sistema 2               │
│ (Correspondencia)       │          │ (Oficiales)             │
└────────────┬────────────┘          └────────────┬────────────┘
             │                                    │
             │ [Bearer Token Sistema 1]           │ [Bearer Token Sistema 2]
             ▼                                    ▼
┌──────────────────────────────────────────────────────────────┐
│       API de Almacenamiento Interoperable (Laravel 13)       │
│  - Aislamiento estricto por Tenant (user_id)                 │
│  - Referenciación universal por UUID                         │
│  - Verificación de integridad (SHA-256 Checksum)             │
│  - URLs firmadas temporales HMAC para Frontend (sin tokens)  │
└──────────────────────────────┬───────────────────────────────┘
                               │
                ┌──────────────┴──────────────┐
                ▼                             ▼
       ┌─────────────────┐           ┌─────────────────┐
       │ PostgreSQL 16   │           │ Almacenamiento  │
       │ (Podman)        │           │ en Disco Local  │
       └─────────────────┘           └─────────────────┘
```

---

## 🔒 Principios de Seguridad y Aislamiento

- **Multi-tenancy Estricto:** Cada archivo pertenece exclusivamente al sistema que lo creó. Ningún sistema puede listar, consultar metadatos, visualizar, descargar, activar/desactivar o eliminar archivos pertenecientes al otro sistema.
- **Identificadores UUID (RFC 4122):** Se devuelven UUIDs públicos en lugar de IDs numéricos para evitar ataques de enumeración.
- **Doble Esquema de Acceso:**
  1. **Streaming Directo Autenticado:** Acceso vía API con `Authorization: Bearer <token>` (`/view` y `/download`).
  2. **URLs Temporales Firmadas (HMAC):** Para permitir incrustar imágenes o PDFs en `<img src="...">` o `<iframe>` en el navegador del usuario final sin exponer las credenciales del sistema cliente.

---

## 🛠️ Stack Tecnológico

- **Framework:** Laravel 13
- **Lenguaje:** PHP 8.4 (con extensiones `pdo_pgsql`, `pgsql`)
- **Base de Datos:** PostgreSQL 16 ejecutándose en contenedor con **Podman**
- **Autenticación:** Laravel Sanctum (API Tokens)
- **Testing:** Pest PHP (56 tests automatizados con 100% de cobertura funcional)
- **Análisis Estático:** Larastan / PHPStan (Nivel tipado estricto)
- **Estándar de Código:** Laravel Pint (PSR-12 / Laravel Preset)

---

## 🚀 Instalación y Puesta en Marcha

### 1. Prerrequisitos
- **PHP 8.4** o superior con `php-pgsql`, `php-mbstring`, `php-xml`, `php-curl`.
- **Composer 2.x**
- **Podman** y `podman-compose`

### 2. Clonar el Repositorio
```bash
git clone https://github.com/Alexbyxd/sis-op-2026-isw2.git
cd sis-op-2026-isw2
```

### 3. Configurar Variables de Entorno
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Levantar la Base de Datos con Podman
```bash
podman compose up -d
```
> El contenedor `police_storage_postgres` se iniciará automáticamente en `localhost:5432`.

### 5. Ejecutar Migraciones y Datos de Prueba (Seeders)
```bash
php artisan migrate --seed
```

### 6. Ejecutar la Suite de Pruebas Automatizadas (Pest)
```bash
./vendor/bin/pest
```

### 7. Iniciar el Servidor de Desarrollo
```bash
php artisan serve
```
El servidor quedará disponible en `http://127.0.0.1:8000`.

---

## 👥 Cuentas de Sistemas Preconfiguradas

| Sistema | Username | Contraseña por Defecto | Propósito |
|---|---|---|---|
| **Sistema 1** | `sistema1` | `Correspondencia2026!` | Sistema de Correspondencia |
| **Sistema 2** | `sistema2` | `Oficiales2026!` | Sistema de Manejo de Oficiales |

---

## 📖 Especificación de la API REST (`/api/v1`)

### 1. Autenticación

#### `POST /api/v1/auth/login`
Obtiene un Bearer Token para el sistema cliente.

**Request:**
```json
{
  "username": "sistema1",
  "password": "Correspondencia2026!"
}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Autenticación exitosa",
  "data": {
    "token": "1|qW89uXp...",
    "token_type": "Bearer",
    "system": {
      "id": 1,
      "name": "Sistema de Correspondencia",
      "username": "sistema1",
      "system_code": "sistema1"
    }
  }
}
```

---

### 2. Gestión de Archivos

#### `POST /api/v1/files`
Carga un nuevo archivo para el sistema autenticado.

- **Headers:** `Authorization: Bearer <token>`, `Accept: application/json`
- **Content-Type:** `multipart/form-data`
- **Body:**
  - `file` (File, requerido): Archivo binario (.docx, .xls, .pdf, .jpg, .png, etc.).
  - `metadata` (JSON Array/Object, opcional): Metadatos contextuales (e.g. `{"caso": "EXP-2026", "oficial": "OF-432"}`).

**Response (201 Created):**
```json
{
  "status": "success",
  "message": "Archivo cargado correctamente.",
  "data": {
    "uuid": "7a355651-7ae1-4876-b9dc-3221971fa84c",
    "original_name": "informe_patrullaje.pdf",
    "mime_type": "application/pdf",
    "extension": "pdf",
    "size_bytes": 1048576,
    "size_human": "1 MB",
    "is_active": true,
    "checksum_sha256": "4b227777d4dd1fc61c6f884f48641d02b4d121d3fd328cb08b5531fcacdabf8a",
    "metadata": {
      "caso": "EXP-2026",
      "oficial": "OF-432"
    },
    "urls": {
      "direct_view": "http://127.0.0.1:8000/api/v1/files/7a355651-7ae1-4876-b9dc-3221971fa84c/view",
      "direct_download": "http://127.0.0.1:8000/api/v1/files/7a355651-7ae1-4876-b9dc-3221971fa84c/download",
      "signed_view": "http://127.0.0.1:8000/api/signed/files/7a355651-7ae1-4876-b9dc-3221971fa84c/view?expires=1790000000&signature=...",
      "signed_download": "http://127.0.0.1:8000/api/signed/files/7a355651-7ae1-4876-b9dc-3221971fa84c/download?expires=1790000000&signature=...",
      "signed_expires_at": "2026-09-20T17:45:00+00:00"
    },
    "created_at": "2026-09-20T17:15:00+00:00",
    "updated_at": "2026-09-20T17:15:00+00:00"
  }
}
```

---

#### `GET /api/v1/files`
Lista los archivos pertenecientes exclusivamente al sistema autenticado.

- **Query Params:**
  - `page`: Número de página (default: 1)
  - `per_page`: Cantidad por página (default: 15)
  - `is_active`: `true` | `false`
  - `extension`: Filtro por extensión (e.g. `pdf`, `docx`)
  - `search`: Búsqueda por nombre original

---

#### `GET /api/v1/files/{uuid}`
Consulta los metadatos y genera nuevas URLs firmadas actualizadas para un archivo específico.

---

#### `GET /api/v1/files/{uuid}/view`
Descarga/visualización en streaming directo para consumo autenticado vía API (`Content-Disposition: inline`).

---

#### `GET /api/v1/files/{uuid}/download`
Descarga forzada en streaming directo (`Content-Disposition: attachment; filename="..."`).

---

#### `PATCH /api/v1/files/{uuid}/status`
Habilita o deshabilita la disponibilidad de un archivo.

**Request:**
```json
{
  "is_active": false
}
```

---

#### `DELETE /api/v1/files/{uuid}`
Elimina físicamente el archivo del disco de almacenamiento y purga el registro de la base de datos.

**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Archivo eliminado físicamente de forma exitosa.",
  "data": {
    "uuid": "7a355651-7ae1-4876-b9dc-3221971fa84c",
    "deleted": true
  }
}
```

---

### 3. URLs Temporales Firmadas (Frontend Embedding)

- **`GET /api/signed/files/{uuid}/view?expires=...&signature=...`**: Previsualización directa en navegador.
- **`GET /api/signed/files/{uuid}/download?expires=...&signature=...`**: Descarga directa en navegador.

> Estas rutas no requieren la cabecera `Authorization: Bearer`. Su validez se garantiza matemáticamente mediante la firma HMAC de Laravel. Si la URL expiró o fue alterada, el servidor responderá con `403 Forbidden`.

---

## 🧪 Pruebas Automatizadas

Para correr toda la suite de pruebas unitarias y de integración con Pest:

```bash
./vendor/bin/pest
```

Para verificar análisis estático y tipos con Larastan:
```bash
./vendor/bin/phpstan analyse --memory-limit=512M
```

Para chequear y dar formato al código con Pint:
```bash
./vendor/bin/pint --test
```
