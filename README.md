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
| **Station Agent** | Mobile-first | QR/manual citizen lookup, green/red claim-status cards, one-tap ration confirmation, truck manager delivery confirmation |
| **Truck Manager** | Mobile-first | **Fleet & driver management**: register/edit/deactivate their own trucks; create/manage drivers assigned to their fleet; view and manage only their assigned trucks and drivers; 2-step delivery reporting (select station → select truck → blocks); delivery history |

### Core business rules enforced
- One ration claim per citizen per calendar day (`citizen_id + date` uniqueness).
- Nightly cron expires unclaimed tickets and generates fresh ones — **no rollover**.
- Inventory deduction is atomic (DB transaction + row locking + `CHECK` constraint safety net).
- Truck deliveries require station agent confirmation before stock updates.
- Each **Truck Manager** owns one or more trucks (CRUD scoped to their own fleet via `TruckPolicy`). Deliveries reference both the manager and the specific truck assigned.
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
| Truck Manager | `0931000001`, `0931000002` |

Truck managers own no trucks by default — each must register trucks under **My Trucks** (`/manager/trucks`) and drivers under **My Drivers** (`/manager/drivers`) before filing deliveries. Trucks and drivers are automatically assigned to the logged-in manager.

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
    Manager/   # delivery reporting + Manager\Truck CRUD (fleet mgmt) + Manager\Driver CRUD (driver mgmt)
    Auth/      # mobile+password login
  Models/      # User, Station, Citizen, DailyTicket, Delivery, InventoryLog, Truck
  Policies/    # TruckPolicy (ownership authorization on truck CRUD)
database/
  migrations/  # full schema per schema.md
  seeders/     # SuperAdminSeeder, StationSeeder, DemoStaffSeeder, CitizenSeeder
resources/views/
  admin/ agent/ manager/ manager/trucks/ manager/driver/ auth/ components/layouts/
routes/
  web.php      # role-scoped route groups (admin/agent/manager)
  console.php  # scheduled daily reset
tests/Feature/ # atomic claim, insufficient stock, daily reset, panel smoke tests
```

---

## 🔧 Recent Fixes

### Fixed Truck Manager Driver Management (2026-07)
- Fixed route error preventing access to driver management pages (`Target class [Manager\Driver\DriverController] does not exist`)
- Ensured proper controller imports and route definitions for driver CRUD operations
- Verified that truck managers can successfully create, edit, and deactivate drivers assigned to their fleet

## 📄 License

This project is licensed under the [MIT License](../LICENSE).
