# Technical Stack — Ice Ration Distribution System

---

## Chosen Stack

| Layer | Technology | Version | Reason |
|---|---|---|---|
| Backend Framework | **Laravel** | 11.x | Rapid development, Eloquent ORM, built-in scheduling, queues, auth |
| Database | **PostgreSQL** | 16+ | ENUM types, CHECK constraints, strong ACID, row-level locking |
| Cache / Queue Driver | **Redis** | 7+ | Fast queue processing for cron job fan-out; session store |
| Frontend (Admin) | **Blade + Livewire** | Livewire 3 | Real-time reactive UI without a full SPA; works with Blade templates |
| Frontend (Mobile Panels) | **Blade + Alpine.js + Tailwind CSS** | Alpine 3, Tailwind 3 | Ultra-lightweight; works on 2G; no JS build step needed for simple interactions |
| QR Code Scanning | **html5-qrcode** (JS library) | 2.x | Browser-based camera QR scanning, no native app required |
| QR Code Generation | **simple-qrcode** (Laravel package) | ^4.0 | Server-side QR PNG generation for citizen cards |
| Authentication | **Laravel Breeze** (API mode) OR **Laravel Sanctum** | — | Token-based sessions; lightweight |
| Task Scheduling | **Laravel Scheduler** (built-in) | — | Drives the nightly 00:00 reset cron |
| Job Queue | **Laravel Queue + Redis** | — | Async ticket generation fan-out for large citizen counts |
| HTTP Server | **Nginx + PHP-FPM** | — | Production-grade, handles concurrent mobile requests well |
| Containerization | **Docker + Docker Compose** | — | Reproducible local dev; easy deployment |
| Deployment Target | **VPS / any Linux server** | Ubuntu 22.04 LTS | Low cost; full control |

---

## Laravel Packages

```
composer require:
  laravel/sanctum              # API token auth
  livewire/livewire            # Reactive admin components
  simplesoftwareio/simple-qrcode  # QR code image generation
  spatie/laravel-permission    # Role & permission management
  spatie/laravel-activitylog   # Optional: extended audit logging
  barryvdh/laravel-debugbar    # Dev only
```

```
npm install / CDN:
  html5-qrcode    # QR scanner in station agent browser
  alpinejs        # Lightweight reactivity
  tailwindcss     # Utility CSS (compiled)
```

---

## Project Directory Structure (Laravel)

```
app/
  Models/
    User.php
    Station.php
    Citizen.php
    DailyTicket.php
    Delivery.php
    InventoryLog.php
  Http/
    Controllers/
      Admin/
        StationController.php
        CitizenController.php
        UserController.php
        InventoryController.php
        AnalyticsController.php
      Agent/
        DashboardController.php
        TicketController.php        ← validate + claim
        DeliveryController.php      ← confirm truck delivery
      Driver/
        DeliveryController.php      ← submit delivery report
    Middleware/
      EnsureRole.php
  Livewire/
    Admin/
      StationInventoryWidget.php
      DailyDistributionChart.php
    Agent/
      CitizenSearch.php             ← real-time search + QR decode result
  Console/
    Commands/
      ResetDailyTickets.php         ← nightly cron
database/
  migrations/
  seeders/
routes/
  web.php        ← admin (Blade/Livewire)
  api.php        ← agent + driver (JSON API consumed by Blade+Alpine)
resources/
  views/
    admin/
    agent/          ← mobile-first templates
    driver/         ← mobile-first templates
    components/
      ticket-result-green.blade.php
      ticket-result-red.blade.php
```

---

## Environment Variables (`.env`)

```env
APP_NAME="Ice Ration System"
APP_ENV=production
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ice_ration
DB_USERNAME=ice_user
DB_PASSWORD=<strong-password>
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
SESSION_DRIVER=redis
```

---

## Cron Setup (Server)

Add to server crontab (runs the Laravel scheduler every minute):

```cron
* * * * * php /var/www/ice-ration/artisan schedule:run >> /dev/null 2>&1
```

The scheduler itself fires `ResetDailyTickets` at midnight:

```php
// app/Console/Kernel.php  (or bootstrap/app.php in Laravel 11)
Schedule::command('tickets:daily-reset')->dailyAt('00:00');
```

---

## Security Considerations

- All mobile endpoints require **Sanctum token** in `Authorization: Bearer` header.
- Citizen data (national IDs, mobile numbers) must be stored; ensure **server-level encryption at rest** (PostgreSQL TDE or disk encryption).
- Rate-limit the citizen lookup endpoint to prevent enumeration: `throttle:60,1` per agent token.
- Never expose raw citizen IDs in URLs — use UUIDs or opaque ticket tokens.
- QR code content should be the `qr_code` UUID field, not the `national_id`, to prevent direct identity exposure from a lost card.
