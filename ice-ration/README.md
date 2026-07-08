# ❄️ Ice Ration Distribution System

A full-stack **Supply Chain & Ration Distribution Management System** for ice
distribution. Supply trucks deliver bulk ice to distribution hubs
("Stations" / *Sarpol*), and citizens claim a pre-allocated daily ration
using a permanent identifier (QR/barcode card, National ID, or mobile
number) — no paper coupons.

Built with **Laravel 13** + **MySQL 8** + **Blade/Alpine.js/Tailwind CSS**.

---

## ✨ Features

| Role | Panel | Highlights |
|---|---|---|
| **Super Admin** | Desktop | Stations, staff, and citizen CRUD; QR card printing; manual inventory adjustments; full **analytics dashboard** (KPIs, charts, CSV export) |
| **Station Agent** | Mobile-first | QR/manual citizen lookup, green/red claim-status cards, one-tap ration confirmation, truck delivery confirmation |
| **Truck Driver** | Mobile-first | 3-step delivery reporting, delivery history |

### Core business rules enforced
- One ration claim per citizen per calendar day (`citizen_id + date` uniqueness).
- Nightly cron expires unclaimed tickets and generates fresh ones — **no rollover**.
- Inventory deduction is atomic (DB transaction + row locking + `CHECK` constraint safety net).
- Truck deliveries require station agent confirmation before stock updates.
- Citizens are looked up by National ID, mobile number, or QR code — all resolve to the same record.

---

## 🛠 Tech Stack

- **Backend:** Laravel 13, PHP 8.3
- **Database:** MySQL 8 (ENUM types + CHECK constraints)
- **Frontend:** Blade + Alpine.js + Tailwind CSS (via CDN, no build step required)
- **QR:** `simplesoftwareio/simple-qrcode` (generation) + `html5-qrcode` (browser camera scanning)
- **Auth:** Laravel session auth with a custom `role` column + `EnsureRole` middleware
- **Scheduling:** Laravel Scheduler (`tickets:daily-reset` at 00:00)

See [`.agents/skills/ice-ration-system/`](../.agents/skills/ice-ration-system) for the full architecture spec, DB schema, and implementation plan this project was built from.

---

## 🚀 Getting Started

### Requirements
- PHP 8.3+
- Composer
- MySQL 8+
- Node.js (optional — only needed if you later add a real asset build step)

### Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ice_ration
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed demo data:

```bash
php artisan migrate --seed
```

Seeded accounts (password for all: `password`):

| Role | Mobile |
|---|---|
| Super Admin | `0900000000` |
| Station Agent | `0921000001`, `0921000002`, ... |
| Truck Driver | `0931000001`, `0931000002` |

Start the dev server:

```bash
php artisan serve
```

### Run the daily reset job manually

```bash
php artisan tickets:daily-reset
```

In production, the Laravel scheduler (`routes/console.php`) fires this automatically at midnight — make sure your server crontab runs:

```cron
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

### Run tests

```bash
php artisan test
```

---

## 📁 Project Structure

```
app/
  Console/Commands/ResetDailyTickets.php   # nightly reset job
  Http/Controllers/
    Admin/     # stations, staff, citizens, inventory, analytics
    Agent/     # citizen validation + claim, delivery confirmation
    Driver/    # delivery reporting
    Auth/      # mobile+password login
  Models/      # User, Station, Citizen, DailyTicket, Delivery, InventoryLog
database/
  migrations/  # full schema per schema.md
  seeders/     # SuperAdminSeeder, StationSeeder, DemoStaffSeeder, CitizenSeeder
resources/views/
  admin/ agent/ driver/ auth/ components/layouts/
routes/
  web.php      # role-scoped route groups (admin/agent/driver)
  console.php  # scheduled daily reset
tests/Feature/ # atomic claim, insufficient stock, daily reset, panel smoke tests
```

---

## 📄 License

This project is licensed under the [MIT License](../LICENSE).
