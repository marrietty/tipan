# Tipan: Hospital Appointment + Records System

*Where care meets schedule.*

Tipan is a web-based hospital appointment and records system. Patients book against
pre-generated doctor schedule slots, doctors record clinical outcomes and
prescriptions, and an admin manages accounts and oversees the clinic. The audience is
ordinary people booking medical care and the clinicians and staff who serve them, so
clarity and trust come before cleverness.

## Scope

Five areas, and the requirements stay within them:

- **Accounts**: patient, doctor, and admin, each with a role-specific profile.
- **Appointment booking**: slot-based, against availability doctors pre-generate.
- **Medical records**: per-patient clinical history.
- **Doctor notes and prescriptions**: basic, attached to a record.
- **Admin dashboard**: account management and summary statistics.

## Stack

| Layer | Choice |
|-------|--------|
| Framework | Laravel 13 (PHP 8.3+) |
| Templating | Blade (server-side rendered) |
| Database | Neon (serverless PostgreSQL) |
| ORM | Eloquent |
| Authentication | Laravel session auth (Breeze, Blade stack) |
| Authorization | Role middleware + Policies/Gates |
| Email | Resend (SMTP or API driver) |
| Frontend | Blade + Tailwind CSS via Vite, minimal JS for slot selection |
| Hosting | AWS EC2 (free-tier t3.micro), Nginx + PHP-FPM |

Neon replaces AWS RDS and Resend replaces AWS SES, so AWS hosts only the application
compute. All three sit within free tiers, keeping recurring cost effectively zero.

## Architecture

Standard Laravel MVC. A request passes authentication and role middleware, reaches a
controller, is validated by a Form Request, and either performs a simple Eloquent
operation or invokes a service for non-trivial logic. Data persists to Neon over SSL;
the controller returns a Blade view. Transactional email goes through Resend.

The defining characteristic is that **booking is slot-based, not free-range**. A
`schedule` row is one bookable slot owned by a doctor; an `appointment` claims exactly
one slot. The database enforces the core conflict guarantee, so application logic stays
thin and correctness does not depend on application-side time-range math.

## Data model

UUID primary keys for all domain entities; small lookup tables for controlled
vocabularies, each carrying a machine `name` and a human `display_name` (always render
`display_name` to users, never the raw `name`).

- **Lookups**: `role`, `gender`, `specialization`, `status`. Seeded once, referenced by
  foreign key. Serial integer keys.
- **Accounts**: a central `user` (email, password hash, role) links one-to-one to
  exactly one profile chosen by role: `patient`, `doctor`, or `admin`, each cascading on
  user deletion.
- **Scheduling**: a `schedule` slot has a date, start/end time, and an `is_booked` flag.
  A check constraint enforces end-after-start; a unique constraint over (doctor, date,
  start) prevents duplicate slots.
- **Appointments**: an `appointment` links a patient, a doctor, and one slot (the slot
  reference is unique, so a slot holds at most one appointment), with a datetime, a
  status, and an optional reason.
- **Clinical records**: a `medical_record` references a patient and a doctor, optionally
  the appointment it pertains to (unique, set null on appointment removal so the record
  survives), and stores diagnosis and notes.
- **Prescriptions**: a `prescription` belongs to a medical record and cascades with it;
  each is one medication with dosage, frequency, optional positive duration, and
  instructions.

The full schema is the source of truth and is **final**: build to it exactly. See
[docs/db-schema.md](docs/db-schema.md).

## Core business rules

- **Booking** runs inside one transaction: confirm the slot is unbooked, create the
  appointment referencing it (status defaults to scheduled), and set `is_booked` true.
  The unique constraint on `appointment.schedule_id` is the authoritative conflict guard,
  so two simultaneous bookings cannot both win.
- **Availability** shows only slots with `is_booked = false` for the selected doctor.
- **Cancellation** runs as one transaction that deletes the appointment row and resets
  the slot's `is_booked` to false, freeing it for rebooking. Cancelled appointments
  leave no historical row: a deliberate simplification.
- **Status transitions**: appointments begin scheduled; a doctor may mark a scheduled
  one completed or missed (the row is retained). Cancellation is a deletion, not a status.
- **Records and prescriptions**: one record per appointment (unique); records survive
  appointment removal; prescriptions cascade with their record; non-positive durations
  are rejected at the database level.

## Roles

| Role | Capabilities |
|------|-------------|
| **Patient** | Self-register, edit profile, search doctors, view open slots, book, cancel (frees slot), view own records and prescriptions |
| **Doctor** | Manage profile and schedule slots, view own appointments, mark completed/missed, create medical records, add prescriptions |
| **Admin** | Create/manage doctor and patient accounts, view all appointments, view summary statistics, manage slots |

Access is enforced in two layers: role middleware controls which area a user can reach;
policies control row-level access (a patient sees only their own records; a doctor sees
only patients they have treated; a patient cancels only their own appointments).

## Design standard

The interface aims for a calm, confident, generous-with-whitespace sensibility. Every
screen holds to: one obvious primary action; the shortest path to the goal; honest,
immediate state; plain, human microcopy; designed empty/loading/error states; hierarchy
through type and space rather than boxes; a restrained neutral palette with one accent;
comfortable touch targets; and real accessibility. No emoji, no generic template
layouts, no dark patterns. See [docs/design.md](docs/design.md).

## Getting started

Requirements: PHP 8.3+, Composer, Node.js, and access to a Neon PostgreSQL database.

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configure the database in `.env`. Neon connections require SSL (`DB_SSLMODE=require`).
**Use the Neon direct endpoint, not the `-pooler` one**: the pooler runs PgBouncer in
transaction-pooling mode, under which Laravel's `BEGIN`/`COMMIT` can land on different
backend sessions, so the transactional booking flow silently fails to commit.

```dotenv
DB_CONNECTION=pgsql
DB_HOST=<your-project>.aws.neon.tech    # direct endpoint, no "-pooler"
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=<password>
DB_SSLMODE=require
```

Session, cache, and queue use file/sync drivers (no extra database tables required):

```dotenv
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

The database is managed and seeded out of band (the lookup tables ship with the schema).
A `DemoSeeder` loads a demonstration dataset: one admin, three doctors with open slots,
four patients, and a few appointments with a medical record and prescriptions:

```bash
php artisan db:seed --class=DemoSeeder
```

Run the app:

```bash
npm run dev          # Vite, in one terminal
php artisan serve    # in another
```

### Demo accounts

Every demo account uses the password `password`.

| Role | Email |
|------|-------|
| Admin | `liza.mercado@tipan.ph` |
| Doctor | `ramon.dela.cruz@tipan.ph` |
| Patient | `maria.santos@example.ph` |

After login, each user is routed to their role's dashboard (`/patient`, `/doctor`,
`/admin`); cross-role access is rejected with 403.

## Email

Resend handles outbound transactional mail through Laravel's mail layer (SMTP or the
Resend API driver, with a verified sending domain): booking confirmations and status
changes. Because Resend is external, no AWS SES setup or production-access review is
required.

## Deployment

Compute, data, and email are separated across three free-tier providers: a single AWS
EC2 t3.micro (Nginx + PHP-FPM) serves the app and connects outward to Neon over SSL and
to Resend for mail. Credentials and secrets are supplied via environment variables on the
instance, never committed. The app may optionally be containerized with Docker, but that
is not required.

## Documentation

- [docs/requirements.md](docs/requirements.md): functional requirements and acceptance criteria.
- [docs/design.md](docs/design.md): architecture, components, business rules, and the UX standard.
- [docs/db-schema.md](docs/db-schema.md): the final database schema (read-only contract).
