---
name: ice-ration-system
description: Full-stack Supply Chain & Ration Distribution Management System for ice distribution. Covers architecture, database schema, Laravel/PostgreSQL implementation, mobile-first station agent UI, daily reset cron job, and MVP step-by-step plan.
---

# Ice Ration Distribution System — Project Skill

This skill governs all development decisions for the Supply Chain & Ration Distribution Management System. Read the supporting files in this directory for deep-dive references:

- `schema.md` — Full PostgreSQL database schema with all tables, columns, indexes, and relationships
- `stack.md` — Chosen technical stack with rationale and package list
- `implementation-plan.md` — Phased MVP build plan with specific tasks per phase

---

## Project Summary

Supply trucks deliver bulk ice to distribution hubs called **Stations (Sarpol)**. Citizens visit stations daily to claim a pre-allocated ration of ice blocks using a permanent identifier (plastic card with QR/barcode, National ID, or mobile number). No paper coupons — identifiers are permanent; the DB checks today's date per citizen to validate claims.

---

## User Roles

| Role | Panel | Primary Device |
|---|---|---|
| Super Admin | Full management + analytics | Desktop |
| Station Agent | Inventory + citizen validation + truck confirmation | Mobile (primary) |
| Truck Driver | Report deliveries to stations | Mobile |

---

## Core Business Rules (Always Enforce)

1. **One claim per citizen per calendar day.** The daily ticket is tied to `citizen_id + date`. Once claimed, it cannot be claimed again until the next day's reset.
2. **Daily reset at 00:00.** A scheduled job marks all unclaimed tickets from the previous day as `expired` and generates fresh tickets for every active citizen for the new day.
3. **No rollover.** Unclaimed rations from a previous day are permanently expired — they do not accumulate.
4. **Inventory deduction is atomic.** When a Station Agent confirms delivery to a citizen, the station's `current_stock` must be decremented in the same DB transaction as the ticket claim.
5. **Truck deliveries require agent confirmation.** A truck driver submits a pending delivery; the station agent must confirm it before inventory is updated.
6. **Permanent identifiers only.** Citizens use a fixed barcode/QR card, their 10-digit National ID, or their registered mobile number. Searching by any of these must resolve to the same citizen record.

---

## Key Workflows

### Citizen Claims Ration (Station Agent)
```
Agent scans QR / types National ID or Mobile
        ↓
System looks up citizen → finds today's ticket
        ↓
   Ticket status?
   ┌── NOT_CLAIMED ──→ Show GREEN card: "Approved – N Blocks"
   │                    Agent taps [Confirm Delivery]
   │                    → Mark ticket CLAIMED (timestamped)
   │                    → Decrement station inventory by N (atomic TX)
   └── CLAIMED      ──→ Show RED card: "Already claimed today at HH:MM"
```

### Truck Delivery Flow
```
Driver selects station + enters block count → Submit
        ↓
Pending delivery record created
        ↓
Station Agent sees notification → taps [Confirm]
        ↓
Station inventory incremented + delivery marked CONFIRMED
```

### Daily Reset (Cron Job — 00:00 daily)
```
1. UPDATE daily_tickets SET status='expired' WHERE status='pending' AND date < TODAY
2. INSERT INTO daily_tickets (citizen_id, station_id, date, allocated_blocks, status)
   SELECT id, preferred_station_id, TODAY, daily_ration, 'pending'
   FROM citizens WHERE is_active = true
```

---

## UI/UX Constraints (Non-Negotiable)

- **Station Agent & Truck Driver interfaces are Mobile-First.**
  - Minimum tap target: 48×48px
  - Font size ≥ 16px for all interactive elements
  - Avoid complex navigation — single-action screens where possible
  - All critical actions must work with 2G/weak connectivity (keep payloads under 10 KB)
- Use **green (#16a34a bg)** for success/approved states and **red (#dc2626 bg)** for already-claimed or error states — high contrast for outdoor use.
- QR scanning uses the device camera via a JavaScript library (no native app required).

---

## When Implementing Features

- Always check `schema.md` for exact column names and types before writing migrations or queries.
- Always check `stack.md` for approved packages before adding new dependencies.
- Follow the phase order in `implementation-plan.md` — do not skip phases.
- For every feature that touches inventory, wrap DB operations in a **Laravel DB transaction**.
- Station Agent routes must be under the `agent` middleware group; Truck Driver routes under `driver`; Super Admin under `admin`.
- All API responses (for the mobile panels) must return JSON with a consistent envelope:
  ```json
  { "success": true, "data": {}, "message": "..." }
  ```
