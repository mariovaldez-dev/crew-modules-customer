# Administrador Biblioteca de Cultivos

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![SQL Server](https://img.shields.io/badge/SQL_Server-CC2927?style=for-the-badge&logo=microsoft-sql-server&logoColor=white)

Sistema administrativo para la gestión de cultivos y sus configuraciones de nutrición por zona y territorio.

## Tecnologías

- **Backend:** Laravel 8, PHP 8.x
- **Frontend:** Livewire 3.x + Blade + Tailwind CSS (Atomic Components)
- **Persistencia:** Stored Procedures (SQL Server) vía `DB::select()` / `DB::statement()`
- **Arquitectura:** Clean Architecture (Domain, Application, Infrastructure, UI)
- **Renderizado:** 100% Server-Side Rendering (SSR) con Livewire

## Configuración del Entorno

1. Copiar el archivo de ejemplo y configurar las variables:
   ```bash
   cp .env.example .env
   ```

2. Variables clave en el `.env`:
   - `APP_VERSION`: Versión actual de la aplicación (enviada en login).
   - `AUTH_API_URL`: URL base de la API externa de autenticación.
   - `DB_CONNECTION`: Configuración para SQL Server.

3. Instalar dependencias:
   ```bash
   composer install
   ```

4. Generar la clave de aplicación:
   ```bash
   php artisan key:generate
   ```

## Autenticación y Detección de Plataforma

El sistema detecta automáticamente la plataforma del usuario mediante el `User-Agent` para facilitar la auditoría y compatibilidad con WebViews móviles:
- `IOS_APP` / `IOS_WEB`
- `ANDROID_APP` / `ANDROID_WEB`
- `WEB` (Desktop)

Además, se genera un `deviceId` único por sesión/navegador y se reporta la `versionApp` configurada en el entorno.

## Diagramas de Arquitectura y Flujo

### Flujo de Autenticación
Este diagrama ilustra la interacción entre las capas de Clean Architecture al autenticar un usuario contra la API externa.

```mermaid
sequenceDiagram
    actor Usuario
    participant UI as UI (LoginForm Livewire)
    participant APP as Application (UseCase)
    participant INF as Infrastructure (AuthRepository)
    participant API as API Externa (Auth Service)

    Usuario->>UI: Ingresa credenciales
    UI->>UI: Detecta plataforma/dispositivo
    UI->>APP: execute(AuthenticateUserDTO)
    APP->>INF: authenticate(user, pass, platform)
    INF->>API: POST /auth/login (JSON)
    API-->>INF: success: true, sessionId, userData
    INF-->>APP: Domain User Entity
    APP->>INF: logAttempt(success)
    APP-->>UI: User Entity
    UI->>Usuario: Redirigir a Dashboard
```

### Modelo de Renderizado (SSR)
El proyecto utiliza un modelo de renderizado 100% en el servidor mediante Livewire.

```mermaid
graph TD
    A[Usuario interactúa] -->|Evento wire:click/model| B[Livewire Request]
    B --> C[Laravel Router / Middleware]
    C --> D[Livewire Component]
    D --> E[Use Case]
    E --> F[Repository / Stored Procedure]
    F -->|Resultados| E
    E -->|Domain Entity/DTO| D
    D --> G[Blade View Rendering]
    G -->|DOM Diff HTML| H[Livewire Core JS]
    H -->|Surgical Update| I[Navegador del Usuario]
```

### Flujo: Catálogo de Cultivos
Gestión centralizada de la biblioteca base de cultivos y sus etapas fenológicas.

```mermaid
graph LR
    L[Listado General] --> F{Filtrar?}
    F -->|Sí| L
    L --> D[Ver Etapas]
    L --> C[Nuevo Cultivo]
    L --> E[Editar Cultivo]
    L --> DEL[Eliminar Lógico]
    C --> S[Validar Días/Etapas]
    E --> S
    S -->|Éxito| L
```

### Flujo: Configuración de Nutrición
Ajuste de dosis y productos específicos por Zona y Territorio.

```mermaid
graph TD
    Z[Seleccionar Zona] --> T[Seleccionar Territorio]
    T --> Q[Consultar]
    Q --> R{¿Tiene Config?}
    R -->|No| B[Borde Rojo: RN-04]
    R -->|Sí| C[Borde Normal]
    B --> M[Abrir Modal de Ajuste]
    C --> M
    M --> P[Seleccionar Productos]
    M --> K[Ajustar Dosis Ton/Ha]
    P --> SAVE[Guardar Todo - Bulk JSON]
    K --> SAVE
    SAVE --> Q
```

## Desarrollo

### Comandos útiles
```bash
# Levantar servidor local
php artisan serve

# Ejecutar tests
php artisan test

# Limpiar caché de configuración y vistas
php artisan config:clear && php artisan view:clear
```

### Reglas de Arquitectura
- **Domain:** Lógica de negocio pura, sin dependencias del framework.
- **Application:** Casos de uso (Use Cases) que orquestan el dominio.
- **Infrastructure:** Implementaciones concretas (Repositorios con SPs, Clientes API).
- **UI:** Componentes Livewire y vistas Blade.

---
© 2026 Administrador Biblioteca de Cultivos.
