# Tipan: Hospital Appointment + Records System
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

