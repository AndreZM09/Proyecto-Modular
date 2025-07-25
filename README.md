# Proyecto Modular - Guía de Configuración y Ejecución

Este documento proporciona las instrucciones necesarias para configurar y ejecutar el proyecto modular de Laravel desde cero. Sigue estos pasos cuidadosamente para asegurar una instalación exitosa.

## 1. Requisitos Previos

Asegúrate de tener instalado el siguiente software en tu sistema:

*   **PHP:** Versión 8.1 o superior. Puedes verificar tu versión con `php -v`.
*   **Composer:** Administrador de paquetes para PHP. Descárgalo desde [getcomposer.org](https://getcomposer.org/download/).
*   **Node.js y npm:** Para la gestión de dependencias frontend. Descárgalos desde [nodejs.org](https://nodejs.org/en/download/).
*   **Servidor Web (Apache/Nginx):** Se recomienda usar XAMPP para un entorno de desarrollo fácil. Descarga XAMPP desde [apachefriends.org](https://www.apachefriends.org/index.html).
*   **Base de Datos (MySQL):** XAMPP incluye MySQL. Asegúrate de que el servicio de MySQL esté corriendo.

## 2. Clonar el Repositorio

Primero, clona el repositorio del proyecto en tu máquina local. Si estás usando XAMPP, se recomienda clonar el proyecto dentro del directorio `htdocs` de XAMPP (ej. `C:\xampp\htdocs\Proyecto-Modular`).

```bash
git clone <URL_DEL_REPOSITORIO>
cd Proyecto-Modular
```

**Nota:** Reemplaza `<URL_DEL_REPOSITORIO>` con la URL real de tu repositorio Git.

## 3. Instalación de Dependencias

### 3.1. Dependencias de PHP (Composer)

Navega al directorio raíz del proyecto y ejecuta Composer para instalar las dependencias de PHP:

```bash
composer install
```

### 3.2. Dependencias de JavaScript (NPM)

Luego, instala las dependencias de Node.js:

```bash
npm install
```

## 4. Configuración del Entorno

### 4.1. Crear el Archivo `.env`

Laravel utiliza un archivo `.env` para la configuración del entorno. Copia el archivo de ejemplo:

```bash
cp .env.example .env
```

### 4.2. Generar la Clave de Aplicación

Genera una clave de aplicación única. Esta clave se utiliza para la encriptación de sesiones y otros datos sensibles:

```bash
php artisan key:generate
```

### 4.3. Configurar la Base de Datos

Abre el archivo `.env` y configura los parámetros de conexión a tu base de datos MySQL. Asegúrate de que el nombre de la base de datos (`DB_DATABASE`) exista en tu servidor MySQL.

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_de_datos
DB_USERNAME=tu_usuario_de_mysql
DB_PASSWORD=tu_contraseña_de_mysql
```

## 5. Migraciones y Seeders de la Base de Datos

Ejecuta las migraciones para crear las tablas de la base de datos. Si tienes seeders para datos iniciales, puedes ejecutarlos también:

```bash
php artisan migrate
php artisan db:seed
```

**Nota:** El comando `php artisan db:seed` ejecutará todos los seeders. Si solo quieres ejecutar un seeder específico, usa `php artisan db:seed --class=NombreDelSeeder` (ej. `UsersTableSeeder`).

## 6. Compilar Assets Frontend

Compila los assets CSS y JavaScript del proyecto:

```bash
npm run dev
```

Para producción, puedes usar:

```bash
npm run build
```

## 7. Ejecutar el Servidor de Desarrollo

Si no estás utilizando un servidor web como Apache/Nginx directamente, puedes usar el servidor de desarrollo integrado de Laravel:

```bash
php artisan serve
```

Esto iniciará el servidor en `http://127.0.0.1:8000` (o un puerto similar). Si estás usando XAMPP, asegúrate de que Apache y MySQL estén corriendo, y accede al proyecto a través de la URL configurada en tu servidor web (ej. `http://localhost/Proyecto-Modular/public`).
