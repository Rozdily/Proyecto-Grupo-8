# 🐳 Guía de Desarrollo Remoto Avanzado: VS Code, SSH y Dev Containers (UPDS v5.1)

Esta guía detalla el procedimiento de ingeniería para conectar el entorno de desarrollo local con un servidor remoto mediante un túnel seguro, administrando y compilando el sistema directamente dentro de los contenedores de Docker desde la interfaz del editor [INDEX].

---

## 🛠️ Requisitos Previos Obligatorios

Antes de iniciar la sincronización, asegúrese de que el servidor y el cliente cuenten con las siguientes configuraciones base [INDEX]:

1. **Docker en el Servidor:** El demonio de Docker y Docker Compose deben estar instalados, activos y en ejecución en el servidor remoto (Ubuntu Server) [INDEX].
2. **Llaves SSH Configuradas:** Debe configurar una llave pública (`SSH Key`) en el servidor para permitir accesos cifrados automáticos sin necesidad de ingresar contraseñas manualmente en cada petición [INDEX].
3. **Máquina Cliente:** Visual Studio Code instalado en su computadora local.

---

## 🚀 Pasos para Configurar e Iniciar el Sistema en Contenedores

Siga estrictamente este orden secuencial para levantar el entorno de desarrollo integrado:

### Paso 1: Instalar Extensiones de Control en Local

Abre tu Visual Studio Code local e instala el paquete de extensiones oficiales de Microsoft desde el Marketplace [INDEX]:

- **Remote - SSH** (Para la conexión física y segura al servidor Ubuntu) [INDEX].
- **Dev Containers** (Para aislar y meter el entorno de VS Code dentro de los contenedores) [INDEX].
- **Docker Extension** (Para monitorear volúmenes, imágenes y contenedores de forma visual) [INDEX].

### Paso 2: Conectar al Servidor Remoto

1. Presione la tecla `F1` (o la combinación `Ctrl + Shift + P`) para abrir la paleta de comandos de VS Code [INDEX].
2. Busque y ejecute el comando: `Remote-SSH: Connect to Host...` [INDEX]
3. Introduzca su cadena de acceso seguro tradicional (por ejemplo: `usuario@ip_del_servidor`) [INDEX].
4. Se abrirá de forma automática una nueva ventana de VS Code vinculada al servidor remoto [INDEX].

### Paso 3: Abrir la Carpeta del Sistema

1. En la nueva ventana remota que se acaba de abrir, diríjase al menú superior: **Archivo > Abrir carpeta** [INDEX].
2. Seleccione la ruta física en el servidor donde se encuentra almacenado tu proyecto o donde vive tu archivo maestro de orquestación `docker-compose.yml` [INDEX].

### ⚠️ CONTINGENCIA EN EL PASO 3: ¿Qué hacer si NO existe el archivo 'docker-compose.yml'?

Si al abrir la carpeta del proyecto nota que el archivo maestro de orquestación `docker-compose.yml` no se encuentra en el directorio raíz, el entorno de desarrollo se bloqueará. Para solucionarlo de inmediato y levantar los servicios sin arruinar la instalación, aplique este protocolo de emergencia:

1. **Localizar el Respaldo SQL:** Ingrese a la carpeta `database/` y verifique que el script de respaldo original **`init.sql`** esté intacto (este archivo contiene la estructura nativa para recrear tus 36 tablas desde cero).
2. **Reconstrucción del Archivo Orquestador:** Cree un nuevo archivo vacío en la raíz de VS Code, nómbrelo exactamente `docker-compose.yml` y péguele la estructura de servicios base de tu contenedor (Apache/PHP + MySQL).
3. **Mapeo del Volumen de Arranque:** Asegúrese de que en la sección de volúmenes del servicio de base de datos apunte correctamente a la ruta del script de inicialización: `./database/init.sql:/docker-entrypoint-initdb.d/init.sql`. Esto forzará a Docker a leer tu archivo e inyectar todas las relaciones de forma automática al momento de encender.

### Paso 4: Lanzar el Contenedor y Ejecución Remota

1. Con la carpeta ya cargada en la barra izquierda, vuelva a presionar la tecla `F1` [INDEX].
2. Seleccione el comando oficial: `Dev Containers: Reopen in Container` [INDEX].
3. **Compilación Síncrona:** Visual Studio Code leerá las configuraciones, compilará el entorno virtualizado de forma automática y levantará el sistema directamente dentro de los contenedores del servidor, dejándote el entorno listo para codificar sin tocar la consola [INDEX].
