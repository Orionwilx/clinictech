**PLAN DE TRABAJO DEL PROYECTO**

**Sistema de Gestión de Equipos INGSOLN**

*Documento de alcance y planificación preliminar*

  --------------------------------------------------------------------------
  **Dato**          **Definición**
  ----------------- --------------------------------------------------------
  Proyecto          Sistema de gestión empresarial para administración de
                    clientes, equipos, órdenes de trabajo, técnicos,
                    capacitaciones, mantenimiento y reportes.

  Versión           1.0 -- Planificación preliminar

  Modalidad         Desarrollo por fases con validaciones y entregas
                    parciales

  Infraestructura   AWS, con definición de titularidad y costos en el
                    acuerdo comercial

  Responsable       Desarrollador principal / líder técnico del proyecto
  técnico           

  Proveedor         
  contractual       
  --------------------------------------------------------------------------

# 1. Objetivo del proyecto

Diseñar, desarrollar e implementar una plataforma web de gestión que
permita administrar clientes, equipos, inventario, órdenes de trabajo,
técnicos, capacitaciones, mantenimientos y reportes, incorporando un
panel administrativo y un panel de acceso para clientes.

# 2. Enfoque de trabajo

El proyecto se ejecutará de forma incremental. Antes del desarrollo se
realizará una etapa de levantamiento y validación de requisitos. Cada
fase tendrá entregables concretos y podrá ser validada por el cliente
antes de avanzar a la siguiente. El alcance definitivo, cronograma
contractual y criterios de aceptación deberán cerrarse después de la
fase de análisis.

# 3. Estructura funcional

-   Panel administrativo: administración de usuarios, clientes, equipos,
    órdenes de trabajo, inventario, técnicos, capacitaciones,
    mantenimientos y reportes.

-   Panel cliente: consulta y gestión de la información que corresponda
    a cada cliente, incluyendo hojas de vida de equipos, órdenes,
    técnicos y capacitaciones según los permisos definidos.

-   Control de acceso: autenticación, roles y permisos, con separación
    de información entre clientes.

-   Infraestructura: ambientes de desarrollo/pruebas y producción, con
    despliegue en AWS y controles básicos de seguridad, copias de
    respaldo y monitoreo.

# 4. Plan de trabajo por fases

  -----------------------------------------------------------------------------------
  **Fase**         **Duración     **Objetivo**        **Entregables principales**
                   estimada***                       
  ---------------- -------------- ------------------- -------------------------------
  0. Análisis y   1 semana       Comprender          Documento de requisitos;
  requisitos                      operación, reglas   usuarios/roles; flujos; alcance
                                  de negocio y        MVP; criterios de aceptación.
                                  alcance.            

  1. Arquitectura 1 semana       Construir           Estructura del proyecto;
  y base                          fundamentos         autenticación; roles/permisos;
                                  técnicos.           base de datos inicial;
                                                      ambientes.

  2. Clientes     1--2 semanas   Centralizar         CRUD de clientes; áreas de
                                  información de      trabajo; contactos; adjuntos;
                                  clientes.           recordatorios; relación con
                                                      equipos y OT.

  3. Equipos e    1--2 semanas   Gestionar equipos y CRUD; estados; equipos
  inventario                      su trazabilidad.    eliminados visibles solo para
                                                      admin; inventario; hoja de
                                                      vida; historial y adjuntos.

  4. Órdenes de   2 semanas      Gestionar el ciclo  Creación, asignación, estados,
  trabajo                         completo de las OT. técnicos, evidencias,
                                                      diagnóstico, solución, cierre e
                                                      historial.

  5. Técnicos y   1 semana       Administrar hojas   Fichas de técnicos; documentos;
  capacitaciones                  de vida y           especialidades; capacitaciones;
                                  formación.          vencimientos y consultas.

  6.              1 semana       Gestionar           Programación; seguimiento;
  Mantenimientos                  mantenimiento       recordatorios; relación con
                                  preventivo y        equipos y OT.
                                  correctivo.         

  7. Reportes     1 semana       Transformar datos   Reportes por cliente, equipo,
                                  operativos en       OT, técnico y mantenimiento;
                                  información útil.   filtros y exportaciones según
                                                      alcance.

  8. Panel        1--2 semanas   Dar al cliente      Dashboard; equipos; hojas de
  cliente                         acceso controlado a vida; OT; mantenimientos;
                                  su información.     técnicos; capacitaciones;
                                                      permisos por cliente.

  9. AWS y        1 semana       Preparar operación  Despliegue; SSL; base de datos;
  producción                      real.               backups; secretos; logs;
                                                      monitoreo; configuración
                                                      productiva.

  10. Pruebas y   1--2 semanas   Validar estabilidad Pruebas funcionales;
  entrega                         y cerrar entrega.   correcciones; migración
                                                      acordada; acta de entrega;
                                                      capacitación inicial.
  -----------------------------------------------------------------------------------

* Las duraciones son preliminares para planificación. El cronograma
contractual definitivo dependerá del alcance aprobado, disponibilidad
del cliente, calidad de la información entregada, integraciones,
migración de datos y cambios de alcance.

# 5. Alcance funcional preliminar

## 5.1 Módulo de Administrador

-   Gestión de usuarios y roles.

-   Gestión global de clientes, equipos, órdenes, técnicos,
    capacitaciones y reportes.

-   Consulta de equipos eliminados o inactivos según reglas definidas.

-   Configuraciones generales y control administrativo.

## 5.2 Módulo de Órdenes

-   Creación y seguimiento de órdenes de trabajo.

-   Estados, asignación de técnicos, fechas y responsables.

-   Relación con cliente y equipo.

-   Diagnóstico, actividades realizadas, evidencias, adjuntos y cierre.

## 5.3 Módulo de Equipos e Inventario

-   CRUD de equipos.

-   Registro y consulta de inventario.

-   Estados de equipo.

-   Listado de equipos borrados/eliminados para administración.

-   Hoja de vida e historial de intervenciones, OT y mantenimientos.

## 5.4 Módulo de Clientes

-   Datos y contactos.

-   Áreas de trabajo.

-   Recordatorios.

-   Adjuntos.

-   Capacitaciones.

-   Equipos asociados.

-   Órdenes pendientes y en proceso.

-   Mantenimientos preventivos y correctivos.

## 5.5 Módulo de Reportes

-   Consultas operativas y reportes según información generada por el
    sistema.

-   Filtros por cliente, equipo, técnico, orden y mantenimiento.

-   Exportaciones y reportes avanzados sujetos al alcance final.

## 5.6 Módulo de Usuarios

-   Usuarios administrativos, técnicos y clientes.

-   Roles y permisos.

-   Activación, desactivación y recuperación de acceso.

## 5.7 Panel Cliente

-   Hoja de vida de equipos asociados.

-   Consulta de técnicos relacionados con su operación, según permisos.

-   Consulta de capacitaciones.

-   Consulta de órdenes, mantenimientos y documentos que correspondan.

# 6. Flujos principales a validar

  -----------------------------------------------------------------------
  **Flujo**           **Secuencia preliminar**
  ------------------- ---------------------------------------------------
  Gestión de cliente  Crear cliente → configurar áreas → registrar
                      equipos → asociar usuarios/técnicos → gestionar OT
                      y mantenimientos.

  Gestión de equipo   Registrar equipo → asignarlo a cliente/área →
                      actualizar estado → generar OT o mantenimiento →
                      consolidar historial en hoja de vida.

  Orden de trabajo    Crear OT → asignar técnico → pasar a proceso →
                      registrar diagnóstico/actividades → adjuntar
                      evidencias → finalizar/cerrar.

  Mantenimiento       Definir plan → programar fecha → generar
  preventivo          seguimiento/recordatorio → ejecutar OT preventiva →
                      actualizar hoja de vida.

  Mantenimiento       Reportar falla → generar OT → diagnóstico →
  correctivo          intervención → cierre → registrar resultado en hoja
                      de vida.

  Capacitación        Registrar capacitación → asignar técnico/cliente →
                      adjuntar soporte → registrar fecha de vencimiento →
                      generar recordatorio si aplica.
  -----------------------------------------------------------------------

# 7. Requisitos técnicos y no funcionales preliminares

-   Seguridad: autenticación segura, roles y permisos, protección de
    sesiones y separación de datos por cliente.

-   Infraestructura: ambientes separados para desarrollo/pruebas y
    producción.

-   Backups: definición de frecuencia, retención y recuperación antes de
    salir a producción.

-   Trazabilidad: registro de cambios críticos y operaciones
    administrativas cuando el alcance lo requiera.

-   Escalabilidad: arquitectura preparada para crecimiento razonable de
    usuarios, equipos, órdenes y documentos.

-   Documentación: instrucciones básicas de operación y documentación
    técnica mínima de despliegue.

# 8. Supuestos y dependencias

-   El cliente entregará oportunamente información, reglas de negocio,
    logotipos, catálogos, bases de datos y accesos necesarios.

-   La migración de datos dependerá de la estructura, calidad y volumen
    de la información suministrada.

-   Las integraciones con terceros (por ejemplo correo, WhatsApp,
    facturación, calendarios o APIs) se considerarán únicamente si son
    expresamente incluidas en el alcance final.

-   Los costos recurrentes de AWS, dominios, servicios externos,
    mensajería u otros proveedores deberán estar claramente definidos en
    la propuesta comercial.

-   Las nuevas funcionalidades solicitadas después de la aprobación del
    alcance serán tratadas mediante control de cambios y podrán
    modificar costo y plazo.

# 9. Control de cambios

Para proteger el alcance y el cronograma, toda solicitud que no esté
contemplada en el alcance aprobado deberá registrarse, evaluarse
técnicamente y, cuando corresponda, cotizarse antes de su
implementación. La aprobación del cambio deberá quedar documentada.

# 10. Cronograma de alto nivel

  -----------------------------------------------------------------------
  **Etapa**                               **Semanas estimadas**
  --------------------------------------- -------------------------------
  Análisis y definición                   1

  Desarrollo núcleo administrativo        2--4

  Módulos operativos (equipos, OT,        5--9
  técnicos, mantenimiento)                

  Reportes y panel cliente                10--12

  Producción, pruebas y entrega           13--15
  -----------------------------------------------------------------------

# 11. Criterios de aceptación preliminares

-   Las funcionalidades definidas en el alcance aprobado deben estar
    disponibles según el rol correspondiente.

-   Un cliente solo podrá consultar información a la que tenga
    autorización.

-   Las operaciones críticas de equipos y órdenes deberán reflejarse
    correctamente en sus historiales.

-   Las pruebas acordadas deben ejecutarse sin errores críticos abiertos
    que impidan la operación del sistema.

-   La salida a producción se realizará una vez validadas las
    condiciones técnicas y comerciales correspondientes.

# 12. Próximos pasos

1.  Realizar reunión de levantamiento detallado con el cliente.

2.  Confirmar usuarios, roles y permisos.

3.  Definir flujo exacto de órdenes y estados.

4.  Detallar campos de equipos, clientes, técnicos y capacitaciones.

5.  Validar la estructura y volumen de datos existentes.

6.  Identificar integraciones y requisitos de notificaciones.

7.  Definir propiedad y administración de la infraestructura AWS.

8.  Cerrar alcance, cronograma definitivo, valor del proyecto, soporte y
    plan anual.

9.  Convertir este documento en anexo técnico del contrato comercial.


# NOTAS DEL USUARIO 
Estadnarizar todos los  datables para el uso de ogis y manejo correcto 
Mejora en el manejo de la imagenes, ayque aunque persisten en public no se visualizan entre sesioens
Mejora en las ot y panel de opciones 