# Module: Equipment

## Description
Manages technological equipment belonging to clinics, including type, brand, model, serial number, and status.

## Status: NOT IMPLEMENTED
This module is planned but not yet implemented. No models, controllers, or migrations exist.

## Planned Models
- **Equipment** - Main entity representing technological equipment
  - id, clinic_id, name, type, brand, model, serial_number, purchase_date, warranty_expiry, status, created_at, updated_at

## Planned Relationships
- belongsTo(Clinic) - Equipment belongs to a clinic
- hasMany(ServiceOrder) - Equipment can have multiple service orders

## Planned Endpoints
- GET /equipment - List all equipment
- GET /equipment/{id} - Show equipment details
- POST /equipment - Create new equipment
- PUT /equipment/{id} - Update equipment
- DELETE /equipment/{id} - Delete equipment

## Planned Business Rules
- Serial number is required and unique
- Clinic association is required
- Status can be: active, inactive, maintenance, retired

## Implementation Notes
- Create Equipment model in modules/equipment/Models/Equipment.php
- Create migration in database/migrations/xxxx_xx_xx_create_equipment_table.php
- Create controller in modules/equipment/Http/Controllers/EquipmentController.php
- Create form request in modules/equipment/Http/Requests/
- Create service in modules/equipment/Services/
