# VisitaTrack

Aplicación de seguimiento de visitas de campo: los trabajadores registran checkpoints GPS, fotos y firmas digitales a lo largo de una visita, y los administradores supervisan, aprueban y exportan todo desde un panel dedicado.

Este proyecto es una reescritura personal, con stack y datos completamente distintos, de una idea de producto que desarrollé originalmente para un empleador. El código, los datos de ejemplo y el stack técnico son nuevos — no reutiliza nada del proyecto original.

## Stack

- **Backend**: Laravel 13 + PHP 8.4
- **Panel admin**: Filament v4
- **Portal de trabajador**: Livewire 3 + Alpine.js (wizard móvil de 4 pasos)
- **Base de datos**: PostgreSQL
- **Mapas**: Leaflet
- **Export**: Laravel Excel + DomPDF
- **Auditoría**: spatie/laravel-activitylog
- **Deploy**: Docker (FrankenPHP) en Fly.io

## Funcionalidad

**Trabajador** (`/portal`)
- Wizard de 4 pasos con captura de checkpoints GPS: salida de base → llegada al destino → salida del destino → regreso a base.
- Recorrido GPS en vivo durante los tramos de viaje (batched, no un request por punto).
- Captura de fotos durante la visita.
- Firma digital del trabajador y de quien recibe, al cerrar la visita.
- Autocompletado de datos por número de OV para trabajos con máquina.

**Administrador** (`/admin`)
- Listado de visitas con tabs (Visitas / Maquinaria / Pendientes de aprobación), filtros y búsqueda.
- Revisión y aprobación/rechazo de visitas, con mapa del recorrido (Leaflet), galería de fotos y firmas.
- Catálogos de empresas, máquinas y actividades.
- Auditoría: log de cambios, historial de sesiones y errores capturados.
- Exportación a Excel y PDF.
- PWA instalable (sin soporte offline).

## Desarrollo local

Requiere Docker.

```bash
docker compose up -d --build
docker exec visitatrack-app composer install
docker exec visitatrack-app npm install && npm run build
docker exec visitatrack-app php artisan migrate --seed
```

La app queda disponible en `http://localhost:8090`. Usuario admin de ejemplo: `admin@visitatrack.test` / `password`.

## Deploy

Ver `Dockerfile` y `fly.toml` para el deploy en Fly.io (FrankenPHP, Postgres, Volume para storage privado).
