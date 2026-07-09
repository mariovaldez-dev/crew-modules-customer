# Arquitectura — Administrador Biblioteca de Cultivos

---

## Visión general

```
┌─────────────────────────────────────────────────────────┐
│                    NAVEGADOR (HTML)                      │
│              SSR — sin lógica de negocio                │
└────────────────────────┬────────────────────────────────┘
                         │ HTTP Request / Livewire AJAX
┌────────────────────────▼────────────────────────────────┐
│                    UI LAYER                              │
│   Livewire Components  │  Blade Views  │  Middleware     │
│   (orquesta Use Cases) │  (solo HTML)  │  (auth/roles)  │
└────────────────────────┬────────────────────────────────┘
                         │ llama Use Cases (inyección DI)
┌────────────────────────▼────────────────────────────────┐
│                 APPLICATION LAYER                        │
│   Use Cases  │  DTOs de entrada  │  DTOs de salida       │
│   (orquesta dominio — sin framework, sin DB)            │
└────────────────────────┬────────────────────────────────┘
                         │ usa interfaces del dominio
┌────────────────────────▼────────────────────────────────┐
│                   DOMAIN LAYER                           │
│   Entities  │  Repository Interfaces  │  Value Objects   │
│   (PHP puro — sin imports de Laravel)                   │
└────────────────────────┬────────────────────────────────┘
                         │ implementado por
┌────────────────────────▼────────────────────────────────┐
│               INFRASTRUCTURE LAYER                       │
│   Repositories (DB::select + SPs)  │  Auth  │  Logging  │
│   Service Providers (DI binding)   │  Telescope          │
└─────────────────────────────────────────────────────────┘
```

---

## Reglas de dependencia entre capas

```
Domain         ←  no depende de nadie
Application    ←  depende solo de Domain
Infrastructure ←  depende de Domain (implementa sus interfaces)
UI             ←  depende de Application (usa sus Use Cases)
```

**Nunca:**
- UI → Infrastructure (directo)
- Application → Infrastructure (directo)
- Domain → cualquier otra capa

---

## Estructura de carpetas completa

```
app/
├── Domain/
│   ├── Cultivo/
│   │   ├── Entities/
│   │   │   ├── Cultivo.php
│   │   │   └── EtapaFenologica.php
│   │   └── Repositories/
│   │       └── ICultivoRepository.php
│   └── ConfiguracionZona/
│       ├── Entities/
│       │   └── ConfiguracionEtapa.php
│       └── Repositories/
│           └── IConfiguracionZonaRepository.php
│
├── Application/
│   ├── Cultivo/
│   │   ├── ListCultivos/
│   │   │   ├── ListCultivosUseCase.php
│   │   │   └── ListCultivosDTO.php
│   │   ├── CreateCultivo/
│   │   │   ├── CreateCultivoUseCase.php
│   │   │   └── CreateCultivoDTO.php
│   │   ├── UpdateCultivo/
│   │   │   ├── UpdateCultivoUseCase.php
│   │   │   └── UpdateCultivoDTO.php
│   │   └── DeleteCultivo/
│   │       └── DeleteCultivoUseCase.php
│   └── ConfiguracionZona/
│       ├── ListCultivosPorZona/
│       │   ├── ListCultivosPorZonaUseCase.php
│       │   └── ListCultivosPorZonaDTO.php
│       └── UpdateConfiguracionZona/
│           ├── UpdateConfiguracionZonaUseCase.php
│           └── UpdateConfiguracionZonaDTO.php
│
├── Infrastructure/
│   ├── Persistence/
│   │   ├── CultivoRepository.php         # implements ICultivoRepository
│   │   └── ConfiguracionZonaRepository.php
│   ├── Auth/
│   │   └── AuthRepository.php
│   └── Providers/
│       └── AppServiceProvider.php        # DI bindings
│
└── UI/
    ├── Livewire/
    │   ├── Auth/
    │   │   └── LoginForm.php
    │   ├── Cultivo/
    │   │   ├── CultivoIndex.php
    │   │   ├── CultivoCreate.php
    │   │   ├── CultivoEdit.php
    │   │   └── CultivoDelete.php
    │   └── ConfiguracionZona/
    │       ├── ZonaTerritorioIndex.php
    │       └── ConfiguracionEdit.php
    ├── Http/
    │   ├── Controllers/
    │   │   ├── CultivoController.php
    │   │   └── ZonaTeritorioController.php
    │   └── Middleware/
    │       └── AsesorAgronomicoMiddleware.php
    └── Views/                            # resources/views/
        ├── layouts/
        │   └── app.blade.php
        ├── components/
        │   ├── nav-item.blade.php
        │   └── cultivo-card.blade.php
        ├── livewire/
        │   ├── auth/
        │   │   └── login-form.blade.php
        │   ├── cultivo/
        │   │   ├── index.blade.php
        │   │   ├── create.blade.php
        │   │   ├── edit.blade.php
        │   │   └── delete.blade.php
        │   └── configuracion-zona/
        │       ├── index.blade.php
        │       └── edit.blade.php
        └── auth/
            └── login.blade.php

tests/
├── Feature/
│   ├── Auth/
│   │   └── AuthenticateUserTest.php
│   ├── Cultivo/
│   │   ├── ListCultivosTest.php
│   │   ├── CreateCultivoTest.php
│   │   ├── UpdateCultivoTest.php
│   │   └── DeleteCultivoTest.php
│   └── ConfiguracionZona/
│       ├── ListCultivosPorZonaTest.php
│       └── UpdateConfiguracionZonaTest.php
└── Unit/
    ├── Cultivo/
    │   └── CultivoEntityTest.php
    └── ConfiguracionZona/
        └── UpdateConfiguracionZonaUseCaseTest.php

docs/
├── requerimientos.md       # RQMs completos
├── stored-procedures.md    # Contratos de SPs (actualizar con cliente)
└── arquitectura.md         # Este archivo
```

---

## Decisiones de diseño

### 1. Stored Procedures sobre Eloquent

**Decisión:** Usar `DB::select()` / `DB::statement()` con SPs del cliente.
**Razón:** Requerimiento del cliente — el esquema y la lógica de BD están en SPs existentes.
**Consecuencia:** Sin migraciones propias (excepto Telescope). Sin Eloquent Models para consultas.
**Mitigación:** Los repositorios abstraen completamente los SPs — el dominio nunca ve SQL.

### 2. Livewire Full sobre Blade + JS

**Decisión:** Livewire 2.x como única capa reactiva — sin fetch/axios propios.
**Razón:** SSR puro, un solo lenguaje (PHP), sin context-switch frontend/backend.
**Excepción:** Alpine.js puntual para el multi-select de productos (RQM-08) con `wire:entangle`.

### 3. Un Use Case por acción

**Decisión:** `CreateCultivo`, `UpdateCultivo`, `DeleteCultivo` son clases separadas, no métodos de un servicio.
**Razón:** Single Responsibility — cada caso de uso tiene una sola razón para cambiar.
**Beneficio:** Más fácil de testear, de leer y de extender.

### 4. Interfaces para repositorios

**Decisión:** Toda persistencia detrás de una interfaz en Domain, implementada en Infrastructure.
**Razón:** Permite mockear repositorios en tests sin BD real. Permite cambiar implementación sin tocar Use Cases.
**Binding:** `AppServiceProvider::register()` vincula interfaz → implementación concreta.

### 5. Eliminación lógica

**Decisión:** `DeleteCultivo` inactiva el registro (campo `activo = false`), no lo borra físicamente.
**Razón:** RN-03 del RQM-06 lo especifica explícitamente. Permite trazabilidad histórica.

### 6. Logging estructurado con contexto

**Decisión:** Todo Use Case loguea inicio, éxito y error con `Log::withContext()`.
**Razón:** Telescope captura los logs — facilita el debugging local sin plataforma externa.
**Formato:** JSON — compatible con cualquier plataforma futura si el cliente escala.

---

## Rutas — Organización

```php
// routes/web.php

// Públicas
Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

// Protegidas — autenticado + rol asesor agronómico
Route::middleware(['auth', 'asesor.agronomo'])->group(function () {
    Route::get('/cultivos', [CultivoController::class, 'index'])->name('cultivos.index');
    Route::get('/zona-territorio', [ZonaTeritorioController::class, 'index'])->name('zona.index');
});
```

---

## Service Provider — Bindings DI

```php
// Infrastructure/Providers/AppServiceProvider.php

public function register(): void
{
    // Repositorios
    $this->app->bind(ICultivoRepository::class, CultivoRepository::class);
    $this->app->bind(IConfiguracionZonaRepository::class, ConfiguracionZonaRepository::class);

    // Use Cases — se resuelven automáticamente por el container
    // No necesitan binding explícito si sus dependencias están bindeadas
}

public function boot(): void
{
    // Gates de autorización
    Gate::define('acceder-biblioteca', fn(User $user) =>
        $user->rol === 'asesor_agronomo'
    );
}
```

---

## Testing — Estrategia

| Tipo | Qué prueba | Herramienta |
|---|---|---|
| Unit Test | Use Cases con repositorios mockeados | PHPUnit / Pest |
| Unit Test | Entidades del dominio (invariantes) | PHPUnit / Pest |
| Feature Test | Flujos HTTP completos con BD de test | PHPUnit / Pest + RefreshDatabase |
| Livewire Test | Componentes Livewire (validaciones, eventos) | Livewire Testing Helpers |

**Base de datos de tests:**
- SQLite en memoria para Feature Tests — más rápido
- Si los SPs no son compatibles con SQLite, usar MySQL de test con los SPs instalados
- Confirmar con el cliente qué BD usar para tests

**Convención de factories:**
- Sin Eloquent factories — crear helpers de test que llamen directamente a los SPs o a los repositorios concretos
