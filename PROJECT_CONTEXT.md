# ClinicTech Manager

## Description
A Laravel application for managing technology equipment in medical clinics, including service orders and assignments.

## Status: BASE PROJECT CREATED
- Laravel 13.29.0 installed
- Laravel Breeze installed (Blade + Alpine.js)
- SQLite database configured
- Authentication system ready

## Tech Stack
- Laravel 13.29.0
- PHP 8.4.14
- SQLite (default database)
- Laravel Breeze (authentication)
- Blade + Alpine.js (frontend)
- Vite (build tool)

## Current Implementation
### Models
- **User** (app/Models/User.php) - Default Laravel user model
  - Fields: id, name, email, email_verified_at, password, remember_token, created_at, updated_at

### Controllers
- **Auth Controllers** (app/Http/Controllers/Auth/) - 9 authentication controllers
  - AuthenticatedSessionController
  - ConfirmablePasswordController
  - EmailVerificationNotificationController
  - EmailVerificationPromptController
  - NewPasswordController
  - PasswordController
  - PasswordResetLinkController
  - RegisteredUserController
  - VerifyEmailController
- **ProfileController** (app/Http/Controllers/ProfileController.php) - User profile management

### Routes
- **web.php** - Main routes (/, /dashboard, /profile)
- **auth.php** - Authentication routes (login, register, password reset, etc.)

### Migrations
- 0001_01_01_000000_create_users_table.php
- 0001_01_01_000001_create_cache_table.php
- 0001_01_01_000002_create_jobs_table.php

## Modules (PLANNED - NOT IMPLEMENTED)
1. **Clinics** (modules/clinics/) - Manage medical clinics
2. **Equipment** (modules/equipment/) - Manage technological equipment
3. **Service Orders** (modules/service-orders/) - Manage service orders for equipment
4. **Assignments** (modules/assignments/) - Assign service orders to technicians

## Database
- Default: SQLite (database/database.sqlite)
- Can be configured to MySQL/PostgreSQL in .env

## Development Commands
```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Clear cache
php artisan cache:clear

# Run tests
php artisan test

# Format code
./vendor/bin/pint
```

## Project Structure
```
clinictech/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Auth/           # 9 auth controllers
│   │       └── ProfileController.php
│   └── Models/
│       └── User.php
├── database/
│   └── migrations/             # 3 default migrations
├── modules/
│   ├── clinics/                # NOT IMPLEMENTED
│   ├── equipment/              # NOT IMPLEMENTED
│   ├── service-orders/         # NOT IMPLEMENTED
│   └── assignments/            # NOT IMPLEMENTED
├── routes/
│   ├── web.php
│   ├── auth.php
│   └── console.php
├── resources/
├── config/
├── public/
└── tests/
```

## Next Steps
1. Implement Clinic model, migration, controller, routes
2. Implement Equipment model, migration, controller, routes
3. Implement ServiceOrder model, migration, controller, routes
4. Implement Assignment model, migration, controller, routes
5. Add role-based authentication (admin, technician)
6. Create dashboard views for each module
