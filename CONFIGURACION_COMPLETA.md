# 🔧 Configuración Completa del Proyecto Modular

## 📋 Archivo .env Completo

Crea un archivo `.env` en la raíz del proyecto con la siguiente configuración:

```ini
APP_NAME="Proyecto Modular"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# ===== CONFIGURACIÓN DE BASE DE DATOS =====
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=proyecto_modular
DB_USERNAME=root
DB_PASSWORD=

# ===== CONFIGURACIÓN DE CACHE Y SESIONES =====
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# ===== CONFIGURACIÓN DE REDIS (OPCIONAL) =====
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# ===== CONFIGURACIÓN DE CORREO =====
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# ===== CONFIGURACIÓN DE IA (LM STUDIO) =====
# URL base del servidor LM Studio (puerto por defecto: 1234)
LLM_BASE_URL=http://localhost:1234/v1

# API Key para LM Studio (puede ser cualquier valor)
LLM_API_KEY=lm-studio

# Modelo de IA a usar (debe coincidir con el modelo cargado en LM Studio)
LLM_MODEL=deepseek-r1-0528-qwen3-8b

# ===== CONFIGURACIÓN LEGACY (OPENAI) =====
# Solo usar si prefieres OpenAI en lugar de LM Studio
# OPENAI_API_KEY=tu_api_key_de_openai_aqui

# ===== CONFIGURACIÓN DE AWS (OPCIONAL) =====
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# ===== CONFIGURACIÓN DE PUSHER (OPCIONAL) =====
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# ===== CONFIGURACIÓN DE VITE =====
VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

## 🚀 Pasos de Instalación Detallados

### **1. Preparar el Entorno**

```bash
# Clonar el repositorio
git clone https://github.com/AndreZM09/Proyecto-Modular.git
cd Proyecto-Modular

# Instalar dependencias de PHP
composer install

# Instalar dependencias de JavaScript
npm install
```

### **2. Configurar Base de Datos**

```bash
# Crear base de datos en MySQL
mysql -u root -p
CREATE DATABASE proyecto_modular;
EXIT;

# Configurar archivo .env (usar la configuración de arriba)
# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Opcional: Cargar datos de ejemplo
php artisan db:seed
```

### **3. Configurar IA (LM Studio)**

1. **Descargar LM Studio**: [lmstudio.ai](https://lmstudio.ai/)
2. **Descargar modelo DeepSeek** en LM Studio
3. **Cargar modelo** en la pestaña "Chat"
4. **Iniciar servidor** en la pestaña "Local Server" (puerto 1234)
5. **Probar conexión**:
   ```bash
   php artisan llm:test --provider=lmstudio
   ```

### **4. Compilar Assets**

```bash
# Para desarrollo
npm run dev

# Para producción
npm run build
```

### **5. Iniciar Servidor**

```bash
# Opción 1: Servidor de desarrollo Laravel
php artisan serve

# Opción 2: Con XAMPP
# Asegúrate de que Apache y MySQL estén ejecutándose
# Accede a: http://localhost/Proyecto-Modular/public
```

## 🧪 Verificación de Instalación

### **1. Probar Conexión con IA**
```bash
php artisan llm:test --provider=lmstudio
```

**Salida esperada:**
```
✅ Conexión exitosa con LM Studio!
📝 Respuesta de prueba: Conexión exitosa
🔑 Configuración válida
🚀 La funcionalidad de IA está lista para usar
```

### **2. Probar en el Navegador**
1. Ve a `http://localhost/Proyecto-Modular/public`
2. Inicia sesión
3. Ve a "Correos"
4. Prueba los botones de IA

### **3. Verificar Base de Datos**
```bash
php artisan migrate:status
```

## 🔧 Comandos de Mantenimiento

### **Desarrollo**
```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Regenerar autoload
composer dump-autoload

# Ver rutas disponibles
php artisan route:list
```

### **Base de Datos**
```bash
# Ver estado de migraciones
php artisan migrate:status

# Rollback última migración
php artisan migrate:rollback

# Rollback todas las migraciones
php artisan migrate:reset

# Recrear base de datos completa
php artisan migrate:fresh --seed
```

### **IA y Testing**
```bash
# Probar LM Studio
php artisan llm:test --provider=lmstudio

# Probar OpenAI (si está configurado)
php artisan llm:test --provider=openai

# Ver logs de la aplicación
tail -f storage/logs/laravel.log
```

## 🐛 Solución de Problemas Comunes

### **Error: "No such file or directory"**
```bash
# Verificar permisos
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Regenerar autoload
composer dump-autoload
```

### **Error: "Connection refused" (IA)**
1. Verificar que LM Studio esté ejecutándose
2. Verificar que el puerto 1234 esté libre
3. Verificar que el modelo esté cargado

### **Error: "Table doesn't exist"**
```bash
# Ejecutar migraciones
php artisan migrate

# Si persiste, recrear base de datos
php artisan migrate:fresh --seed
```

### **Error: "Class not found"**
```bash
# Limpiar cache y regenerar autoload
php artisan cache:clear
composer dump-autoload
```

## 📊 Optimización de Rendimiento

### **Para IA**
- Usar GPU si está disponible
- Cerrar otras aplicaciones que consuman RAM
- Considerar modelos más pequeños si hay poca RAM

### **Para Laravel**
- Usar `npm run build` para producción
- Configurar cache de configuración
- Optimizar consultas de base de datos

## 🎯 Próximos Pasos

1. **Personalizar diseño** en `resources/views/`
2. **Ajustar prompts de IA** en `app/Http/Controllers/EstadisticasController.php`
3. **Configurar correo real** en lugar de Mailpit
4. **Implementar autenticación** más robusta
5. **Agregar más funcionalidades** de IA

---

**¡Tu sistema de marketing con IA está listo para usar! 🎉**
