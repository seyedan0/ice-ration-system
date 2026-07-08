# MVP Implementation Plan — Ice Ration Distribution System

Phases are ordered by dependency. Do not skip phases. Each phase should be
committed and smoke-tested before the next begins.

---

## Phase 1 — Project Scaffolding & Database

**Goal:** Working Laravel app connected to PostgreSQL with all migrations, seeders, and models.

### Tasks

1. **Scaffold**
   ```bash
   laravel new ice-ration --no-interaction
   cd ice-ration
   composer require laravel/sanctum livewire/livewire spatie/laravel-permission simplesoftwareio/simple-qrcode
   ```

2. **Configure `.env`** — Set DB to PostgreSQL, cache/queue to Redis (see `stack.md`).

3. **Create ENUM types** (PostgreSQL migration):
   ```php
   // In a dedicated migration run first:
   DB::statement("CREATE TYPE user_role AS ENUM ('super_admin', 'station_agent', 'truck_driver')");
   DB::statement("CREATE TYPE ticket_status AS ENUM ('pending', 'claimed', 'expired')");
   DB::statement("CREATE TYPE delivery_status AS ENUM ('pending', 'confirmed', 'rejected')");
   DB::statement("CREATE TYPE inventory_change_type AS ENUM ('delivery_in', 'ration_out', 'manual_adjustment')");
   ```

4. **Migrations** (in order):
   - `create_stations_table`
   - `create_users_table` (modify default to add `role`, `station_id`, `is_active`)
   - `create_citizens_table`
   - `create_daily_tickets_table`
   - `create_deliveries_table`
   - `create_inventory_logs_table`
   - Add all CHECK constraints and UNIQUE indexes (see `schema.md`)

5. **Eloquent Models** — Create one model per table with:
   - `$fillable` arrays
   - Relationships (`hasMany`, `belongsTo`)
   - `DailyTicket::scopePending()`, `scopeForToday()`
   - `Station::deductStock($blocks)` method (calls the atomic SQL from `schema.md`)

6. **Seeders**:
   - `SuperAdminSeeder` — one admin user
   - `StationSeeder` — 3–5 sample stations
   - `CitizenSeeder` — 20 sample citizens with varied rations

7. **Run & verify:**
   ```bash
   php artisan migrate --seed
   php artisan tinker   # verify relationships
   ```

---

## Phase 2 — Authentication & Role Middleware

**Goal:** Login system with role-based route protection.

### Tasks

1. Install Sanctum and Breeze (API mode) or use session-based auth with Breeze web mode.

2. Create `EnsureRole` middleware:
   ```php
   // Checks auth()->user()->role === $role, aborts 403 otherwise
   ```

3. Register route groups in `routes/web.php`:
   ```php
   Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->group(...);
   Route::middleware(['auth', 'role:station_agent'])->prefix('agent')->group(...);
   Route::middleware(['auth', 'role:truck_driver'])->prefix('driver')->group(...);
   ```

4. Add login page that redirects each role to their respective dashboard after auth.

5. **Test:** Log in as each role, confirm redirect and 403 on wrong routes.

---

## Phase 3 — Super Admin Panel

**Goal:** Full CRUD management of all entities via desktop-optimized Blade/Livewire UI.

### Tasks (build in this order)

#### 3a. Stations (Sarpol) Management
- Livewire `StationIndex` — list, search, paginate
- Create / Edit / Delete (soft delete: set `is_active = false`)
- Show current stock per station

#### 3b. User Management
- Manage Station Agents: create user with `role=station_agent`, assign `station_id`
- Manage Truck Drivers: create user with `role=truck_driver`
- Toggle `is_active`

#### 3c. Citizen & Ration Management
- Register citizen: name, national_id, mobile, daily_ration, preferred_station
- Auto-generate `qr_code` UUID on save
- Show QR code image (using `simple-qrcode` package)
- Print-friendly citizen card view (name + QR + national ID)
- Edit `daily_ration` — change takes effect on next day's ticket generation

#### 3d. Inventory Management
- Admin can manually add blocks to a station (for corrections)
- Creates an `inventory_log` record with `change_type = manual_adjustment`

#### 3e. Analytics Dashboard
- Today's distribution: total blocks out, total remaining across all stations
- Per-station table: current_stock, claimed today, pending today
- Livewire `DailyDistributionChart` — simple bar chart using Chart.js CDN
- Last 7 days consumption trend (from `inventory_logs`)

---

## Phase 4 — Station Agent Panel (Mobile-First)

**Goal:** Fast, large-tap mobile UI for validating citizens and confirming deliveries.

### Tasks

#### 4a. Agent Dashboard
- Shows: station name, current stock (large number, center screen)
- Pending truck deliveries (badge count)
- Navigation: [Validate Citizen] [Confirm Delivery]

#### 4b. Citizen Validation Screen
- Large search input (autofocus on load): accepts national_id, mobile, or qr_code text
- QR camera scanner button — uses `html5-qrcode` to decode and populate the input
- On submit → AJAX POST to `/agent/tickets/validate`

**Response rendering (Blade + Alpine):**
```
x-show="result.status === 'approved'"  → green card component
x-show="result.status === 'claimed'"   → red card component
x-show="result.status === 'not_found'" → yellow card "Citizen not registered"
```

**Green card contains:**
- Citizen name
- Allocated blocks (large text)
- [Confirm Delivery] button → POST `/agent/tickets/{id}/claim`

**After claim:** Show confirmation toast, clear search, refocus input (ready for next citizen).

#### 4c. Ticket Validate API Endpoint
```
POST /agent/tickets/validate
Body: { "identifier": "..." }
Response: {
  "success": true,
  "data": {
    "citizen_name": "...",
    "status": "approved" | "claimed" | "not_found",
    "allocated_blocks": 3,
    "claimed_at": null | "2025-07-08T10:30:00Z",
    "ticket_id": 1234
  }
}
```

#### 4d. Ticket Claim API Endpoint
```
POST /agent/tickets/{ticket_id}/claim
→ Runs atomic DB transaction (see schema.md)
→ Returns updated station stock
```

#### 4e. Confirm Truck Delivery Screen
- List of pending deliveries for this agent's station
- Each row: driver name, block count, submitted time, [Confirm] / [Reject] buttons
- Confirm → POST `/agent/deliveries/{id}/confirm`
  - Increments `stations.current_stock`
  - Logs `inventory_log` with `change_type = delivery_in`
  - Sets `delivery.status = confirmed`

---

## Phase 5 — Truck Driver Panel (Mobile-First)

**Goal:** Single-action screen to report a delivery.

### Tasks

1. **Driver Dashboard**: One screen, three steps:
   - Step 1: Select station (dropdown, populated from active stations)
   - Step 2: Enter number of ice blocks (large number input)
   - Step 3: [Submit Delivery] button

2. POST to `/driver/deliveries`:
   - Creates `deliveries` record with `status = pending`
   - Returns confirmation: "Delivery reported. Waiting for agent confirmation."

3. **History tab** (secondary, simple): Last 10 deliveries by this driver with status badges.

---

## Phase 6 — Daily Reset Cron Job

**Goal:** Nightly automation that expires old tickets and generates new ones.

### Tasks

1. Create Artisan command `php artisan tickets:daily-reset`:

```php
// app/Console/Commands/ResetDailyTickets.php

public function handle(): void
{
    DB::transaction(function () {
        // Step 1: Expire yesterday's unclaimed tickets
        DailyTicket::where('status', 'pending')
                   ->where('ticket_date', '<', today())
                   ->update(['status' => 'expired']);

        // Step 2: Generate today's tickets (skip if already exists)
        $today = today();
        Citizen::where('is_active', true)
               ->chunkById(500, function ($citizens) use ($today) {
                   $inserts = $citizens->map(fn($c) => [
                       'citizen_id'       => $c->id,
                       'station_id'       => $c->preferred_station_id,
                       'ticket_date'      => $today,
                       'allocated_blocks' => $c->daily_ration,
                       'status'           => 'pending',
                       'created_at'       => now(),
                   ])->toArray();

                   DailyTicket::insertOrIgnore($inserts); // uq_citizen_date prevents duplicates
               });
    });

    $this->info('Daily tickets reset complete: ' . today()->toDateString());
}
```

2. Schedule in `bootstrap/app.php` (Laravel 11):
   ```php
   Schedule::command('tickets:daily-reset')->dailyAt('00:00')->withoutOverlapping();
   ```

3. **Test manually:**
   ```bash
   php artisan tickets:daily-reset
   ```
   Confirm ticket count = active citizen count, status = pending, date = today.

4. Add server crontab entry (see `stack.md`).

---

## Phase 7 — Testing & Hardening

**Goal:** Confidence before go-live.

### Tasks

1. **Feature Tests** (PHPUnit):
   - `CitizenClaimTest`: claim once → success; claim twice same day → error
   - `InventoryAtomicTest`: concurrent claims don't reduce stock below 0
   - `DailyResetTest`: command creates correct ticket count, expires previous day

2. **Mobile Testing**:
   - Test agent panel on real Android device over mobile data
   - Verify QR scanner works in outdoor lighting (test `html5-qrcode` constraints: `facingMode: "environment"`)

3. **Load Test** (optional but recommended):
   - Simulate 100 concurrent agent validations using `artisan tinker` or a simple script

4. **Security Review**:
   - Confirm all routes require auth
   - Confirm role middleware blocks cross-role access
   - Check rate limiting on `/agent/tickets/validate`

---

## Phase 8 — Deployment

1. Provision Ubuntu 22.04 VPS
2. Install: Nginx, PHP 8.3-FPM, PostgreSQL 16, Redis 7, Composer
3. Clone repo, `composer install --no-dev`, `npm run build`
4. Set up `.env` with production values
5. `php artisan migrate --force`
6. `php artisan db:seed --class=SuperAdminSeeder`
7. Configure Nginx virtual host + SSL (Let's Encrypt / Certbot)
8. Set up crontab
9. `php artisan queue:work --daemon` (or use Supervisor)
10. First login: create stations, register agents and drivers, bulk-import citizens

---

## Quick Reference: Key Artisan Commands

```bash
php artisan migrate:fresh --seed       # Reset DB (dev only)
php artisan tickets:daily-reset        # Run cron manually
php artisan queue:work                 # Start queue worker
php artisan make:livewire Agent/CitizenSearch
php artisan make:command ResetDailyTickets
php artisan schedule:run               # Trigger scheduler manually
```
