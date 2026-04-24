Garage Booking SaaS (BayWise Portal)

A modern, multi-tenant garage booking and repair tracking platform built from scratch using PHP, MySQL, and Tailwind CSS.

Turn any garage into an online booking system in minutes.

Overview

This is a full SaaS-style system where each garage gets its own:

Booking portal
Admin dashboard
Services & pricing
Calendar slots
Customer repair tracking

All data is securely isolated using a custom multi-tenant architecture (garage_id).

Features
Customer Portal
Book garage services online
Add and manage vehicles
Choose available time slots
Track repair progress step-by-step
View garage updates
🛠 Garage Admin Dashboard
Manage bookings and statuses
Add/edit services (pricing, duration)
Create calendar slots with capacity
Post updates visible to customers
Customise branding (name, tagline, hero image)
Share public booking portal link
Multi-Garage SaaS System
Each garage has isolated data
Custom onboarding creates:
Garage
Admin account
Default services
Demo booking slots

Public portals:

/g/garage-slug
UI / UX
Mobile-first design
Tailwind CSS
Glassmorphism + gradient UI
Clean admin dashboard
Architecture

Custom multi-tenant structure:

garages
 ├── users
 ├── customers
 ├── vehicles
 ├── bookings
 ├── services
 ├── calendar_slots
 └── booking_updates

Each table includes:

garage_id

This ensures:

Data isolation
Scalability
Real SaaS behaviour

Example portals:

/g/demo-garage
/g/fastfit
/g/london-mot
PHP (no frameworks)
MySQL
Tailwind CSS
Custom MVC-style structure
CSRF protection + secure auth
Highlights
Multi-Tenant SaaS (No Frameworks)

Built from scratch with full control over architecture.

Dynamic Garage Portals

Each garage has its own URL:

/g/{slug}
Full Booking Lifecycle
Requested → Confirmed → Inspection → In Progress → Completed
Automated Onboarding

Creating a garage automatically sets up:

Admin user
Services
Booking slots
Branding

Author:
Built by Brandon Darby

