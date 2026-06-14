Locked in: cancellation deletes the appointment row, which frees the slot (`is_booked` 
# Tipan — Hospital Appointment + Records System
## Software Requirements Specification

*Tipan: Where care meets schedule.*

A web-based hospital appointment and records system built on Laravel with Blade, backed by Neon Postgres, with email through Resend, deployed on AWS EC2. This document specifies requirements aligned to the final d# Tipan — Hospital Appointment + Records System
## Technical Design Document

*Tipan: Where care meets schedule.*

A web-based hospital appointment and records system built on Laravel with Blade, backed by Neon Postgres, sending email through Resend, and deployed on AWS EC2. This document describes the architecture, data model, components, and business rules aligned to the final database schema. No code.

---

## 1. Technology Stack

| Layer | Choice |
|-------|--------|
| Framework | Laravel (PHP) |
| Templating | Blade (server-side rendered views) |
| Database | Neon (serverless PostgreSQL) |
| ORM | Eloquent |
| Authentication | Laravel session-based auth (Breeze, Blade stack) |
| Authorization | Role middleware + Policies/Gates |
| Email | Resend (SMTP or API driver) |
| Frontend | Blade + Tailwind CSS, minimal JavaScript for slot selection |
| Hosting | AWS EC2 (free-tier t3.micro), Nginx + PHP-FPM |

Neon replaces AWS RDS and Resend replaces AWS SES, so AWS hosts only the application compute. All three sit within free tiers, keeping recurring cost effectively zero.

---

## 2. Architecture Overview

The application follows Laravel's MVC pattern. A browser request enters through the web routes, passes authentication and role middleware, reaches a controller, is validated by a Form Request, and either performs a simple Eloquent operation or invokes a Service class for non-trivial logic. Data persists to Neon Postgres via Eloquent, and the controller returns a rendered Blade view. Transactional email is dispatched through Resend where appropriate.

The request flow, in order: the browser issues an HTTP request; routes direct it and apply auth and role middleware; the controller receives validated input; booking and cancellation rules run inside a service that wraps a database transaction; Eloquent reads and writes Neon; and the controller renders a Blade view back to the browser.

The defining architectural characteristic of this system is that booking is **slot-based**, not free-range. Doctors pre-generate discrete schedule slots, and a booking simply claims one slot. The database enforces the core conflict guarantee, so the application logic stays thin and correctness does not depend on application-side time-range math.

---

## 3. Data Model

The schema uses UUID primary keys for all domain entities and small lookup tables for controlled vocabularies, each carrying a machine `name` and a human `display_name`.

**Lookup tables.** `role` enumerates patient, doctor, and admin. `gender` enumerates male, female, other, and prefer-not-to-say. `specialization` holds ten medical specialties. `status` enumerates scheduled, completed, cancelled, and missed. These are seeded once and referenced by foreign key, which keeps values consistent and makes display labels easy to manage.

**Accounts.** A central `user` table holds email, hashed password, a role reference, and a creation timestamp. Each user links one-to-one to exactly one profile table chosen by role: `patient`, `doctor`, or `admin`, each cascading on deletion of the parent user. The patient profile carries name, date of birth, gender, phone, and address. The doctor profile carries name, specialization, a unique license number, and phone. The admin profile carries name only. Splitting profiles by role keeps each table's columns meaningful rather than nullable catch-alls.

**Scheduling.** A `schedule` row is one bookable slot owned by a doctor, defined by an available date, a slot start time, a slot end time, and an `is_booked` flag defaulting to false. A check constraint guarantees the slot end is later than the slot start, and a unique constraint over doctor, date, and start time prevents duplicate slots. This table is the supply of availability that patients book against.

**Appointments.** An `appointment` links a patient, a doctor, and exactly one schedule slot, the slot reference being unique so a slot can hold at most one appointment. It also stores an appointment datetime, a status referencing the status lookup and defaulting to scheduled, an optional reason, and a creation timestamp. Foreign keys to patient, doctor, and schedule use restrict-on-delete to protect integrity while the appointment is live. Cancellation is modeled by deleting the appointment row rather than by a status flag, which frees the slot for rebooking.

**Clinical records.** A `medical_record` captures the clinical outcome of a visit. It references a patient and a doctor with restrict-on-delete, and optionally references the appointment it pertains to. The appointment reference is unique, so a given appointment has at most one record, and is set to null if that appointment is ever removed, so the clinical record survives. The record stores a diagnosis, free-text notes, and a recorded timestamp.

**Prescriptions.** A `prescription` belongs to a medical record and cascades when that record is deleted. Each row is a single medication with its name, dosage, frequency, an optional duration in days constrained to be positive, and optional instructions. Multiple prescriptions on one record represent multiple medications.

Relationships in summary: a user has one patient, doctor, or admin profile; a doctor has many schedule slots and many appointments; a patient has many appointments and many medical records; an appointment claims exactly one slot and may have one medical record; and a medical record has many prescriptions. A patient's overall medical history is derived by aggregating their medical records and prescriptions in query, so no separate history container table is needed.

---

## 4. Application Components

**Routing and middleware.** Web routes are grouped by role. Public routes cover patient registration and login. Patient, doctor, and admin route groups are each guarded by authentication plus a custom role middleware that compares the logged-in user's role against the group and rejects mismatches with a forbidden response. This is the enforcement point for role-based access control.

**Controllers.** Responsibilities divide cleanly along the scope areas. Authentication controllers handle registration, login, and logout. A doctor controller manages doctor listings, the doctor's own profile, and the doctor's schedule slots. An appointment controller lists a doctor's open slots, stores bookings, lists a patient's appointments, cancels them, and updates status. A medical record controller serves the patient's read-only history and the doctor's view of a treated patient, and lets a doctor create records. A prescription controller adds medications to a record. Admin controllers drive the dashboard statistics, account management, the system-wide appointment view, and slot management.

**Service layer.** The booking and cancellation rules are encapsulated in an appointment service so the transactional logic lives in one place and behaves identically regardless of entry point. This is the only part of the system with non-trivial business logic; everything else is straightforward CRUD over Eloquent.

**Form requests.** Validation is centralized. Registration enforces a unique, well-formed email and a minimum password strength. Doctor creation enforces a unique license number and a valid specialization. Booking validates that the chosen slot exists and is not already booked. Record creation validates diagnosis and notes presence as required. Prescription entry validates that medication, dosage, and frequency are present and that any duration is positive, mirroring the database check.

**Policies.** Authorization for sensitive data is expressed in policies. A medical record policy ensures a patient can view only their own records and a doctor can view only records for patients they have treated. An appointment policy ensures a patient can cancel only their own appointments. These complement the role middleware, which controls area access, with row-level control over specific entities.

---

## 5. Core Business Rules

**Booking a slot.** When a patient books, the appointment service runs inside a single database transaction. It confirms the target slot belongs to the chosen doctor and is not yet booked, creates the appointment row referencing that slot with status defaulting to scheduled, and sets the slot's `is_booked` flag to true. The unique constraint on the appointment's schedule reference is the authoritative guarantee: even if two patients attempt the same slot simultaneously, the database permits only one appointment row for that slot, so the second attempt fails and is reported as a slot-unavailable condition. Application logic therefore does not need to compute time-range overlaps; the discrete-slot model plus the unique constraint handle conflict prevention.

**Availability display.** Patients are shown only slots whose `is_booked` flag is false for the selected doctor, so booked slots disappear from the choices.

**Cancellation.** Cancelling an appointment runs as one transaction that deletes the appointment row and resets the linked slot's `is_booked` flag to false. Because the row is deleted, the unique constraint on the slot is released and the slot immediately becomes available for rebooking, satisfying the freed-slot requirement. The accepted tradeoff is that cancelled appointments leave no historical row; this is a deliberate simplification.

**Status transitions.** Appointments begin scheduled. A doctor may mark a scheduled appointment as completed or missed, which updates the status and retains the row. Cancellation is distinct from these, being a deletion rather than a status change.

**Record and prescription integrity.** A medical record attaches to at most one appointment, enforced by the unique appointment reference, and survives the removal of that appointment because the reference is set to null rather than cascading. Prescriptions, in contrast, cascade with their parent record, so deleting a record cleanly removes its medications. The positive-duration check on prescriptions is enforced at the database level as a backstop to form validation.

---

## 6. View Layer

Blade templates are organized by role beneath a shared master layout that renders navigation appropriate to the current user. Authentication views cover login and patient registration. Patient views cover doctor search, the open-slots screen for a selected doctor, the appointment list with a cancel action, and the read-only medical history. Doctor views cover slot management, the doctor's own appointment list with status actions, a treated patient's record screen, and forms for recording a diagnosis and adding prescriptions. Admin views cover the dashboard with summary statistic cards, doctor and patient management screens, the system-wide appointment list, and slot oversight. Blade permission directives show or hide actions according to policy so the interface matches each user's authorization.

---

## 7. Email

Resend handles outbound transactional email through Laravel's mail layer, configured with either SMTP credentials or Resend's API driver and a verified sending domain. Email is used for booking confirmations and status changes. Because Resend is external, no AWS SES setup or production-access review is required.

---

## 8. Deployment Architecture

The deployment separates compute, data, and email across three providers, each chosen to stay within free tiers.

**Compute.** A single AWS EC2 t3.micro instance runs Nginx and PHP-FPM serving the Laravel application and handling all web traffic. On the AWS free tier, one continuously running instance incurs no cost in the first year.

**Database.** Neon hosts the PostgreSQL database as a managed, serverless service that scales down when idle, reached over an SSL-required connection. This removes the main recurring database cost that an AWS RDS instance would carry.

**Email.** Resend delivers transactional mail over its API or SMTP, replacing AWS SES and its approval process.

The topology: browsers reach the EC2 instance over HTTP or HTTPS; the Laravel application on EC2 connects outward to Neon for all persistence and to Resend for mail. Database credentials, mail credentials, and application secrets are supplied through environment variables on the instance rather than committed to source. Migrations and seeders initialize the lookup tables and load demonstration accounts, slots, and appointments. The application may optionally be containerized with Docker so the same image runs in local development and on the EC2 host, but containerization is not required.

**Cost summary.** With free-tier EC2, free-tier Neon, and free-tier Resend, the running cost of the project is effectively zero, the only AWS component being a single small compute instance.

---

## 9. Testing Strategy

Testing centers on the acceptance criteria and the schema's guarantees. Feature tests verify that booking an open slot creates one appointment and flips the slot to booked; that a second booking of the same slot fails on the unique constraint and is reported as unavailable; that only unbooked slots are offered; that cancelling deletes the appointment and frees the slot for rebooking; that a doctor can mark an appointment completed or missed; that a second medical record for the same appointment is rejected; that a non-positive prescription duration is refused; that deleting a record cascades to its prescriptions; and that role middleware and policies block cross-role and cross-patient access. Seeders provide consistent fixtures so these tests and live demonstrations behave predictably.

---

Want an ER diagram of the final schema to accompany this, or a sequence description of the booking and cancellation transactions?atabase schema.

---

## 1. Scope

The system covers five areas: patient, doctor, and admin accounts; appointment booking against pre-generated doctor schedule slots; per-patient medical records; basic doctor notes and prescriptions; and an admin dashboard. Requirements stay within these areas.

---

## 2. Data Model Summary

The schema uses lookup tables for controlled vocabularies and UUID primary keys for all entities.

**Lookup tables.** `role` (patient, doctor, admin), `gender` (male, female, other, prefer not to say), `specialization` (ten medical specialties), and `status` (scheduled, completed, cancelled, missed). Each stores a machine `name` and a human `display_name`.

**Accounts.** A central `user` table holds email, hashed password, and a role reference. Each user links one-to-one to exactly one profile table: `patient`, `doctor`, or `admin`. Patient profiles carry name, date of birth, gender, phone, and address. Doctor profiles carry name, specialization, a unique license number, and phone. Admin profiles carry name only.

**Scheduling.** A `schedule` row is a single bookable slot owned by a doctor, defined by an available date, a slot start and end time, and an `is_booked` flag. A check constraint enforces that slot end is after slot start, and a unique constraint prevents duplicate slots for the same doctor, date, and start time.

**Appointments.** An `appointment` links a patient, a doctor, and exactly one schedule slot (enforced unique), with an appointment datetime, a status (defaulting to scheduled), an optional reason, and a creation timestamp. Deletes are restricted against patient, doctor, and schedule to protect referential integrity during active life; cancellation is handled by deleting the appointment row itself.

**Clinical records.** A `medical_record` captures the clinical outcome of an appointment: it references a patient, an optional unique appointment, and a doctor, and stores a diagnosis, free-text notes, and a recorded timestamp. A `prescription` belongs to a medical record and stores one medication with its dosage, frequency, duration in days, and instructions; multiple prescriptions per record represent multiple medications.

---

## 3. Functional Requirements

### FR1 — Accounts (Patient, Doctor, Admin)

- FR1.1 — A patient shall self-register with email, password, first name, last name, date of birth, gender, and optionally phone and address.
- FR1.2 — The system shall create a `user` row with the patient role and a linked `patient` profile in a single transaction.
- FR1.3 — Doctor and admin accounts shall be created by an admin, not self-registered, since doctors require a verified, unique license number.
- FR1.4 — All users shall log in with email and password.
- FR1.5 — Passwords shall be stored only as hashes, never in plain text.
- FR1.6 — Email shall be unique and well-formed; registration shall reject duplicates.
- FR1.7 — Passwords shall meet a minimum strength (8+ characters with a mix of letters and numbers).
- FR1.8 — A user shall be able to log out and end the session.
- FR1.9 — Each user shall hold exactly one role, drawn from the `role` lookup table.
- FR1.10 — A patient shall view and edit their own profile; a doctor shall view and edit their own profile and slots.

### FR2 — Appointment Booking (Slot-Based)

- FR2.1 — A doctor (or admin) shall generate availability by creating `schedule` slots with a date, start time, and end time.
- FR2.2 — A patient shall search and select a doctor, then view that doctor's open slots, those where `is_booked` is false.
- FR2.3 — The system shall display only slots that are not yet booked.
- FR2.4 — Booking a slot shall create one `appointment` referencing that slot, the patient, and the doctor, with status defaulting to scheduled.
- FR2.5 — A slot shall hold at most one appointment, enforced by the unique constraint on `schedule_id`; this is the system's conflict guard.
- FR2.6 — On booking, the system shall set the slot's `is_booked` to true within the same transaction as the appointment insert.
- FR2.7 — A patient shall optionally record a reason for the visit at booking.
- FR2.8 — A patient shall view their upcoming and past appointments with statuses.
- FR2.9 — A doctor shall view their own appointment schedule.

### FR3 — Cancellation and Status Lifecycle

- FR3.1 — An appointment status shall be one of scheduled, completed, cancelled, or missed, drawn from the `status` lookup table.
- FR3.2 — New appointments shall default to scheduled.
- FR3.3 — Cancelling an appointment shall delete the appointment row and reset the linked slot's `is_booked` to false, both in one transaction, so the slot becomes immediately rebookable.
- FR3.4 — Because cancellation deletes the row, the system shall not retain a historical record of cancelled appointments; this is an accepted design tradeoff.
- FR3.5 — A doctor shall mark a scheduled appointment as completed or missed; these transitions update the status and retain the row.
- FR3.6 — A patient shall cancel only their own upcoming appointments.

### FR4 — Medical Records

- FR4.1 — A `medical_record` shall be created by a doctor, normally tied to the appointment it pertains to (one record per appointment, enforced unique).
- FR4.2 — A record shall capture diagnosis, free-text notes, the attending doctor, the patient, and a recorded timestamp.
- FR4.3 — A patient's medical history shall be derived by aggregating their medical records (and their prescriptions) in most-recent-first order; no separate container table is required.
- FR4.4 — A patient shall view their own records and prescriptions read-only.
- FR4.5 — A doctor shall view the records of patients they have treated.
- FR4.6 — A patient shall not view another patient's records.
- FR4.7 — If a linked appointment is removed, its record shall remain with the appointment reference cleared (set null), preserving the clinical record.

### FR5 — Doctor Notes + Prescriptions (Basic)

- FR5.1 — Doctor notes shall be recorded in the medical record's notes and diagnosis fields, tied to the appointment.
- FR5.2 — A doctor shall add one or more prescriptions to a medical record.
- FR5.3 — Each prescription shall capture medication name, dosage, frequency, duration in days, and optional instructions.
- FR5.4 — Duration in days, when given, shall be greater than zero.
- FR5.5 — Multiple prescriptions on one record shall represent multiple medications.
- FR5.6 — Deleting a medical record shall cascade-delete its prescriptions.
- FR5.7 — A patient shall view their prescriptions within their records.

### FR6 — Admin Dashboard

- FR6.1 — The dashboard shall be accessible only to admin accounts.
- FR6.2 — An admin shall create and manage doctor accounts, including license number and specialization.
- FR6.3 — An admin shall view patient accounts.
- FR6.4 — An admin shall view all appointments across the system with their statuses.
- FR6.5 — The dashboard shall display summary counts: total patients, total doctors, and appointments grouped by status.
- FR6.6 — An admin shall manage doctor schedule slots if needed.

---

## 4. User Acceptance Criteria

Given–When–Then, grouped by feature.

### UAC1 — Accounts

**UAC1.1 — Patient registration**
- Given a new visitor, when they submit valid details with a unique, well-formed email and a strong password, then a user row with the patient role and a linked patient profile are created and the password is stored hashed.

**UAC1.2 — Duplicate email blocked**
- Given an email already in use, when a visitor registers with it, then registration is rejected with a clear message.

**UAC1.3 — Weak password rejected**
- Given the registration form, when the password is under 8 characters or lacks the required mix, then submission is blocked with a requirements message.

**UAC1.4 — Doctor created by admin with unique license**
- Given an admin, when they create a doctor with a license number already in use, then the system rejects it due to the unique license constraint; when the license is unique, the doctor is created and can log in.

**UAC1.5 — Login outcomes**
- Given a registered user, when credentials are correct they are logged in and routed by role; when incorrect, access is denied and no session is created.

**UAC1.6 — Role-based access**
- Given a logged-in patient, when they attempt to open the admin dashboard, then access is denied.

### UAC2 — Booking

**UAC2.1 — Book an open slot**
- Given a patient viewing a doctor's open slots, when they select an unbooked slot and confirm, then one appointment is created with status scheduled and the slot's `is_booked` becomes true, removing it from the open list.

**UAC2.2 — Booked slot cannot be double-booked**
- Given a slot already holding an appointment, when another patient attempts to book it, then the unique constraint on the slot blocks a second appointment and the patient is shown a slot-unavailable message.

**UAC2.3 — Only open slots are offered**
- Given a doctor with some booked and some open slots, when a patient views availability, then only slots with `is_booked` false appear.

### UAC3 — Cancellation and Status

**UAC3.1 — Cancel frees the slot**
- Given a patient with a scheduled appointment, when they cancel it, then the appointment row is deleted and the slot's `is_booked` resets to false, making it appear again in the open list and immediately rebookable.

**UAC3.2 — Cancel restricted to owner**
- Given a patient, when they attempt to cancel an appointment that is not theirs, then the action is denied.

**UAC3.3 — Doctor marks outcome**
- Given a doctor viewing a scheduled appointment, when they mark it completed or missed, then the status updates and the appointment row is retained.

### UAC4 — Medical Records

**UAC4.1 — Doctor records an outcome**
- Given a doctor on a treated patient's appointment, when they save a medical record with diagnosis and notes, then one record is created for that appointment and appears in the patient's history.

**UAC4.2 — One record per appointment**
- Given an appointment already having a medical record, when a second record for the same appointment is attempted, then it is rejected by the unique constraint.

**UAC4.3 — Patient views own history**
- Given a logged-in patient, when they open their records, then their medical records and prescriptions appear most-recent-first, read-only.

**UAC4.4 — Cross-patient access blocked**
- Given a patient, when they attempt to view another patient's records, then access is denied.

**UAC4.5 — Record survives appointment removal**
- Given a medical record linked to an appointment, when that appointment is removed, then the record remains with its appointment reference cleared.

### UAC5 — Prescriptions

**UAC5.1 — Add prescriptions**
- Given a doctor on a medical record, when they add one or more medications with dosage, frequency, and a positive duration, then each prescription is stored and visible to the patient.

**UAC5.2 — Invalid duration rejected**
- Given a prescription entry, when the duration in days is zero or negative, then the database check constraint rejects it.

**UAC5.3 — Prescriptions cascade with record**
- Given a medical record with prescriptions, when the record is deleted, then its prescriptions are deleted with it.

### UAC6 — Admin Dashboard

**UAC6.1 — Admin-only access**
- Given a non-admin, when they try to reach the dashboard, then access is denied.

**UAC6.2 — Manage doctors**
- Given an admin, when they create or update a doctor account, then the change is reflected in doctor listings.

**UAC6.3 — View all appointments**
- Given an admin, when they open the appointments view, then all appointments across patients and doctors are listed with statuses.

**UAC6.4 — Summary statistics**
- Given an admin on the dashboard, when it loads, then accurate totals for patients, doctors, and appointments-by-status are shown.

---

## 5. Non-Functional Requirements

- NFR1 — **Security.** Passwords hashed; sessions expire after inactivity; medical data accessible only to authorized roles; Neon connections require SSL.
- NFR2 — **Data integrity.** Slot uniqueness and the booking transaction (appointment insert plus `is_booked` update) run server-side so concurrent bookings cannot both succeed; cancellation runs as a single transaction so row deletion and `is_booked` reset never diverge.
- NFR3 — **Usability.** Responsive Blade interface usable on desktop and mobile browsers.
- NFR4 — **Performance.** Dashboard and appointment listings load within a reasonable time under normal use.
- NFR5 — **Availability.** Data persists reliably in Neon Postgres.

---

## 6. Roles Summary

| Role | Capabilities |
|------|-------------|
| **Patient** | Self-register, edit profile, search doctors, view open slots, book, cancel (frees slot), view own records and prescriptions |
| **Doctor** | Login, manage profile and schedule slots, view own appointments, mark completed/missed, create medical records, add prescriptions |
| **Admin** | Create/manage doctor and patient accounts, view all appointments, view summary statistics, manage slots |

---

## 7. Deployment

The Laravel application runs on a free-tier AWS EC2 instance (Nginx + PHP-FPM). It connects over SSL to Neon for all persistence and to Resend for transactional email such as booking confirmations and status changes. No AWS RDS or SES is used, keeping recurring cost effectively zero on free tiers. Configuration is supplied via environment variables; migrations and seeders initialize the lookup tables and load demonstration accounts, slots, and appointments.

