# Module: Clinics

## Description
Manages clinic information including name, address, phone, email, and status.

## Status: NOT IMPLEMENTED
This module is planned but not yet implemented. No models, controllers, or migrations exist.

## Planned Models
- **Clinic** - Main entity representing a medical clinic
  - id, name, address, phone, email, city, status, created_at, updated_at

## Planned Relationships
- hasMany(Equipment) - A clinic has multiple equipment
- hasMany(ServiceOrder) - A clinic has multiple service orders

## Planned Endpoints
- GET /clinics - List all clinics
- GET /clinics/{id} - Show clinic details
- POST /clinics - Create new clinic
- PUT /clinics/{id} - Update clinic
- DELETE /clinics/{id} - Delete clinic

## Planned Business Rules
- Clinic name is required and unique
- Email must be valid format
- Status can be: active, inactive

## Implementation Notes
- Create Clinic model in modules/clinics/Models/Clinic.php
- Create migration in database/migrations/xxxx_xx_xx_create_clinics_table.php
- Create controller in modules/clinics/Http/Controllers/ClinicController.php
- Create form request in modules/clinics/Http/Requests/
- Create service in modules/clinics/Services/
