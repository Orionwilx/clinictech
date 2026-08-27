# Module: Service Orders

## Description
Manages service orders for equipment, including issue description, priority, status, and dates.

## Status: NOT IMPLEMENTED
This module is planned but not yet implemented. No models, controllers, or migrations exist.

## Planned Models
- **ServiceOrder** - Main entity representing a service order
  - id, equipment_id, title, description, priority, status, reported_date, assigned_date, completed_date, created_at, updated_at

## Planned Relationships
- belongsTo(Equipment) - Service order belongs to equipment
- belongsTo(User) - Service order can be assigned to a user (technician)
- hasOne(Assignment) - Service order has one assignment

## Planned Endpoints
- GET /service-orders - List all service orders
- GET /service-orders/{id} - Show service order details
- POST /service-orders - Create new service order
- PUT /service-orders/{id} - Update service order
- DELETE /service-orders/{id} - Delete service order

## Planned Business Rules
- Title and description are required
- Priority can be: low, medium, high, critical
- Status can be: pending, assigned, in_progress, completed, cancelled
- Reported date is auto-set to current date

## Implementation Notes
- Create ServiceOrder model in modules/service-orders/Models/ServiceOrder.php
- Create migration in database/migrations/xxxx_xx_xx_create_service_orders_table.php
- Create controller in modules/service-orders/Http/Controllers/ServiceOrderController.php
- Create form request in modules/service-orders/Http/Requests/
- Create service in modules/service-orders/Services/
