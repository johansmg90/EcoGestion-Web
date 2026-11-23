# EcoGestión — Sistema Web de Trazabilidad de Residuos Industriales

Proyecto de Título desarrollado para la gestión, control operativo y auditoría inmutable de residuos industriales en la empresa **Logística Verde Sur**, cumpliendo con los estándares normativos del DS 148 y la Ley REP.

## 🌐 Acceso al Sistema en Producción
* **URL Pública (Hosting):** http://ecogestion-app.site.je
* **Credenciales Perfil Administrador:** RUT `12345678-9` | Contraseña `123456`
* **Credenciales Perfil Reciclador:** RUT `98765432-1` | Contraseña `123456`

## 🏗️ Arquitectura y Tecnologías
El sistema está implementado bajo una arquitectura Cliente-Servidor con patrón **MVC (Modelo-Vista-Controlador)**:
* **Frontend (Vista):** HTML5 semántico y CSS3 nativo (`style.css`) con diseño responsivo *Mobile First* (sin frameworks externos).
* **Backend (Controlador):** PHP 8.x con sentencias preparadas (`mysqli`), encriptación de contraseñas mediante `Bcrypt`, control de acceso basado en roles y transacciones ACID.
* **Base de Datos (Modelo):** MySQL 8.0 (Motor InnoDB) normalizado en **Tercera Forma Normal (3FN)** con 8 tablas relacionales e integridad referencial (`ON DELETE RESTRICT`).

## 📂 Estructura del Proyecto
* `database.sql` — Script DDL/DML con las 8 tablas en 3FN (`perfil`, `usuario`, `sucursal`, `categoria_residuo`, `residuo`, `solicitud_retiro`, `historial_estado`, `comprobante`) y datos iniciales.
* `conexion.php` — Módulo de conexión segura a MySQL.
* `style.css` — Hoja de estilos responsiva nativa.
* `index.php` / `login_process.php` / `logout.php` — Autenticación de usuarios y gestión de sesiones.
* `dashboard.php` — Panel Administrador: registro de acopio de residuos y control de integridad referencial en categorías.
* `solicitudes.php` — Panel Administrador: generación y asignación de órdenes de retiro a recicladores.
* `usuarios.php` — Panel Administrador: creación y administración de cuentas y perfiles de usuario.
* `reciclador.php` — Panel Reciclador: actualización de estados logísticos (`Pendiente`, `En Ruta`, `Completado`) y emisión automática de certificados.
* `auditoria.php` — Consulta transversal del *Audit Trail* inmutable y comprobantes de trazabilidad emitidos.

## ⚙️ Despliegue Local (XAMPP)
1. Copiar la carpeta del proyecto dentro de `C:\xampp\htdocs\EcoGestion`.
2. Iniciar **Apache** y **MySQL** desde XAMPP Control Panel.
3. Crear la base de datos `ecogestion_db` en `http://localhost/phpmyadmin` e importar el archivo `database.sql`.
4. Configurar las credenciales de servidor en `conexion.php` y acceder vía `http://localhost/EcoGestion/index.php`.