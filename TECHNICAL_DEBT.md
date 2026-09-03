# Deuda Técnica para Producción

Registro de decisiones tomadas en modo prototipo que **deben revisarse antes de salir a producción**.

---

## Almacenamiento de archivos

**Problema:** Los logos de clientes se guardan en disco local (`storage/app/public/logos/`).  
**Impacto:** En AWS con múltiples instancias (Elastic Beanstalk, ECS) o cualquier deploy que no garantice sistema de archivos persistente, los archivos se pierden entre deploys o no se comparten entre instancias.  
**Solución:** Cambiar el disk de `'public'` a `'s3'` en `ClientController` (líneas ~40 y ~88) y configurar las variables de entorno:
```
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_URL=           # URL pública del bucket (CloudFront o bucket público)
```
El disk `s3` ya está definido en `config/filesystems.php`. El método `Client::logoUrl()` usa `asset('storage/...')` — debe cambiarse a `Storage::disk('s3')->url($this->logo_path)`.  
**Archivos afectados:** `app/Http/Controllers/Admin/ClientController.php`, `app/Models/Client.php::logoUrl()`.

---

## Migraciones fragmentadas (consolidar antes de producción)

**Problema:** Según la política de desarrollo temprano del proyecto, se editan migraciones originales en lugar de crear `add_x_to_y`. Esto es correcto en desarrollo pero el historial de migraciones debe quedar limpio antes del primer deploy.  
**Candidatas a fusionar:**
- `add_soft_deletes_to_users_table` → fusionar en `create_users_table`
- `add_is_active_to_users_table` → fusionar en `create_users_table`

**Acción:** Ejecutar `migrate:fresh --seed` una última vez con las migraciones consolidadas antes del deploy inicial.

---

## Base de datos SQLite → MySQL/PostgreSQL

**Problema:** El proyecto usa SQLite (`database/database.sqlite`), adecuado para desarrollo local.  
**Impacto en producción:** SQLite no soporta concurrencia de escritura real ni es adecuado para múltiples conexiones simultáneas.  
**Solución:** Configurar en `.env` de producción:
```
DB_CONNECTION=mysql   # o pgsql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```
Revisar que no haya consultas que aprovechen particularidades de SQLite.

---

## Admin de arranque con credenciales fijas

**Problema:** El seeder crea el admin con `admin@ingsoln.com` y contraseña hardcodeada en `UserSeeder`.  
**Impacto:** Credencial conocida en repositorio.  
**Solución:** En producción, usar variables de entorno para la contraseña del admin inicial o cambiarla inmediatamente post-deploy. Revisar `database/seeders/UserSeeder.php`.

---

## Confirmaciones de eliminación con `confirm()` nativo del navegador

**Problema:** Los diálogos de eliminación usan `onsubmit="return confirm(...)"` — bloqueante y no estilizable.  
**Impacto:** UX inferior en producción, sin posibilidad de branding.  
**Solución:** Reemplazar con un modal Alpine.js o similar antes del lanzamiento público.  
**Archivos afectados:** Todos los `index.blade.php` del admin.

---

## Logs y monitoreo

**Problema:** Actualmente `LOG_CHANNEL=stack` por defecto de Laravel, sin configuración de alertas.  
**Solución para producción:** Configurar un canal centralizado (CloudWatch, Papertrail, Sentry) y alertas ante errores 500.

---

*Última actualización: automatizado — agregar entradas cada vez que se toma una decisión de prototipo consciente.*
