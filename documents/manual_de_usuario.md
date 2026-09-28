# 📘 MANUAL DE USUARIO - GUÍA RÁPIDA

Esta guía rápida orienta al tribunal examinador para interactuar con las interfaces y flujos de autenticación del sistema en el entorno local.

---

## 🔑 1. Acceso y Autenticación Unificada

Para ingresar a la plataforma, el sistema implementa un formulario unificado que procesa credenciales cifradas y valida los accesos en la base de datos.

### Pasos para Abrir Sesión:

1. Abra su navegador web (Chrome o Firefox).
2. Ingrese a la dirección local del proyecto:
   `http://localhost/tu_proyecto/views/login/index.php`

---

## 👥 2. Credenciales de Prueba Disponibles

Utilice los siguientes usuarios semilla mapeados directamente desde la base de datos para validar las vistas y la redirección síncrona por rol:

1. **Rol Administrador (Control Total):**
   - **Usuario:** `admin`
   - **Contraseña:** `password`
2. **Rol Estudiante (Progreso de Expediente):**
   - **Usuario:** `estudiante1`
   - **Contraseña:** `password`
3. **Rol Tutor / Docente (Agenda de Citas):**
   - **Usuario:** `tutor1`
   - **Contraseña:** `password`

---

## 🚪 3. Cierre de Sesión Seguro

Para cerrar sesión de forma robusta, destruir las cookies de red en el servidor Apache y bloquear el botón "Atrás" del historial del navegador, realice la siguiente acción:

- Haga clic en el **icono de logout (Salir)** ubicado en la parte superior derecha de la ventana del navegador.

# 📦 4. ARQUITECTURA DE DIRECTORIOS Y SISTEMA DE CARPETAS

A continuación, se detalla de forma resumida el mapa de responsabilidades físicas de cada directorio del proyecto. El sistema está estructurado bajo una arquitectura limpia para garantizar el mantenimiento y la seguridad de la plataforma.

---

## 📂 Glosario Estructurado de Carpetas

- **`config/` (Conexión a la Base de Datos):** Contiene la lógica técnica y los parámetros necesarios para establecer la conexión síncrona y real con la base de datos local mediante PDO de forma segura.
- **`controllers/` (Validación de Información):** Son los encargados de interceptar, limpiar y validar toda la información que ingresa o sale del sistema (peticiones POST y GET), actuando como el cerebro que coordina el flujo del software.
- **`models/` (Extracción de Información):** Contiene las clases y funciones nativas que se encargan exclusivamente de extraer, insertar o actualizar información específica de la base de datos mediante sentencias preparadas de SQL.
- **`includes/` (Componentes Repetitivos):** Aloja las porciones de código que se utilizan repetidamente en múltiples pantallas del sistema (como el header, el footer o el navbar azul), evitando la redundancia de código basura en el servidor.
- **`views/` (Interfaz del Usuario):** Es la capa del frontend; la parte del código y el diseño visual HTML/Bootstrap que el usuario final puede ver e interactuar en caliente desde su navegador web.
