# 📬 Proyecto Modular - Sistema de Marketing por Correo Electrónico

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![AI Powered](https://img.shields.io/badge/AI-LM%20Studio-green.svg)](https://lmstudio.ai)

Un sistema completo de marketing por correo electrónico desarrollado en Laravel con **Inteligencia Artificial integrada** usando LM Studio y DeepSeek. Incluye análisis de campañas, tracking de clics, redirección móvil y recomendaciones automáticas de IA.

## 🚀 Características Principales

### 📧 **Gestión de Campañas**
- Envío masivo de correos electrónicos
- Plantillas personalizables con imágenes
- Listas de correos desde archivos Excel/CSV
- Seguimiento de aperturas y clics en tiempo real

### 🤖 **Inteligencia Artificial Integrada**
- **IA Completamente Gratuita** usando LM Studio + DeepSeek
- Recomendaciones automáticas para diseño de correos
- Chat personalizado con IA para consultas específicas
- Análisis predictivo de rendimiento de campañas
- Consejos de optimización basados en datos históricos

### 📊 **Analytics y Estadísticas**
- Dashboard completo con métricas en tiempo real
- Gráficos interactivos de rendimiento
- Análisis de tasa de apertura y clics
- Predicciones de IA para futuras campañas

### 📱 **Optimización Móvil**
- Redirección automática para dispositivos móviles
- Tracking optimizado para smartphones
- Interfaz responsive y moderna

## 🛠️ Requisitos del Sistema

### **Requisitos Mínimos**
- **PHP:** 8.1 o superior
- **MySQL:** 8.0 o superior
- **Composer:** Última versión
- **Node.js:** 16.x o superior
- **RAM:** 8GB mínimo (16GB recomendado para IA)
- **Espacio:** 5GB libres

### **Para Funcionalidad de IA**
- **LM Studio:** [Descargar aquí](https://lmstudio.ai/)
- **Modelo DeepSeek:** ~2-4GB de espacio adicional
- **RAM:** 16GB+ recomendado para mejor rendimiento

## 📥 Instalación Rápida

### 1. **Clonar el Repositorio**

```bash
git clone https://github.com/AndreZM09/Proyecto-Modular.git
cd Proyecto-Modular
```

### 2. **Instalar Dependencias**

```bash
# Dependencias de PHP
composer install

# Dependencias de JavaScript
npm install
```

### 3. **Configurar Entorno**

```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 4. **Configurar Base de Datos**

Edita el archivo `.env` con tus credenciales de MySQL:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=proyecto_modular
DB_USERNAME=root
DB_PASSWORD=
```

### 5. **Ejecutar Migraciones**

```bash
# Crear tablas de la base de datos
php artisan migrate

# Opcional: Cargar datos de ejemplo
php artisan db:seed
```

### 6. **Compilar Assets**

```bash
npm run dev
```

### 7. **Iniciar Servidor**

```bash
# Opción 1: Servidor de desarrollo Laravel
php artisan serve

# Opción 2: Con XAMPP (recomendado)
# Asegúrate de que Apache y MySQL estén ejecutándose
# Accede a: http://localhost/Proyecto-Modular/public
```

## 🤖 Configuración de IA (LM Studio)

### **Paso 1: Instalar LM Studio**
1. Descarga LM Studio desde [lmstudio.ai](https://lmstudio.ai/)
2. Instala la aplicación

### **Paso 2: Descargar Modelo DeepSeek**
1. Abre LM Studio
2. Ve a la pestaña "Models"
3. Busca "DeepSeek" o "deepseek-r1"
4. Descarga el modelo (recomendado: `deepseek-r1-0528-qwen3-8b`)

### **Paso 3: Cargar Modelo y Iniciar Servidor**
1. Ve a la pestaña "Chat" en LM Studio
2. Selecciona el modelo DeepSeek descargado
3. Haz clic en "Load Model"
4. Ve a la pestaña "Local Server"
5. Haz clic en "Start Server" (puerto 1234)

### **Paso 4: Configurar Variables de Entorno**

Agrega estas líneas a tu archivo `.env`:

```ini
# Configuración de IA (LM Studio)
LLM_BASE_URL=http://localhost:1234/v1
LLM_API_KEY=lm-studio
LLM_MODEL=deepseek-r1-0528-qwen3-8b
```

### **Paso 5: Probar Conexión**

```bash
php artisan llm:test --provider=lmstudio
```

## 🎯 Uso del Sistema

### **Acceso al Sistema**
1. Ve a `http://localhost/Proyecto-Modular/public`
2. Inicia sesión con las credenciales por defecto
3. Navega a "Correos" para gestionar campañas

### **Funcionalidades de IA**

#### **Recomendaciones Automáticas**
- Haz clic en "Obtener Recomendaciones de IA"
- Recibe consejos sobre colores, tipografías y diseño
- Análisis basado en tu historial de campañas

#### **Chat Personalizado**
- Haz clic en "Hacer Pregunta Específica"
- Pregunta sobre mejores horas para enviar correos
- Consulta sobre optimización de contenido
- Análisis de rendimiento de campañas

### **Ejemplos de Preguntas para la IA**

```
¿Cuáles son las mejores horas para enviar correos según mis datos?
¿Qué colores debo usar para una campaña de descuento?
¿Cómo puedo mejorar la tasa de apertura de mis correos?
¿Qué tipo de contenido genera más clics?
```

## 📁 Estructura del Proyecto

```
Proyecto-Modular/
├── app/
│   ├── Http/Controllers/     # Controladores principales
│   ├── Models/              # Modelos de datos
│   └── Console/Commands/    # Comandos Artisan
├── database/
│   ├── migrations/          # Migraciones de BD
│   └── seeders/            # Datos iniciales
├── resources/
│   ├── views/              # Vistas Blade
│   └── css/                # Estilos personalizados
├── routes/
│   └── web.php             # Rutas de la aplicación
├── storage/
│   └── app/public/         # Archivos subidos
└── public/
    ├── css/                # CSS compilado
    └── imagenes/           # Imágenes estáticas
```

## 🔧 Comandos Útiles

### **Desarrollo**
```bash
# Limpiar cache
php artisan cache:clear

# Regenerar autoload
composer dump-autoload

# Ver rutas
php artisan route:list
```

### **Base de Datos**
```bash
# Ejecutar migraciones
php artisan migrate

# Rollback migraciones
php artisan migrate:rollback

# Ejecutar seeders
php artisan db:seed
```

### **IA y Testing**
```bash
# Probar conexión con LM Studio
php artisan llm:test --provider=lmstudio

# Probar conexión con OpenAI
php artisan llm:test --provider=openai
```

## 🎨 Personalización

### **Estilos CSS**
- Edita archivos en `public/css/`
- Los estilos se compilan automáticamente

### **Funcionalidad de IA**
- Modifica prompts en `app/Http/Controllers/EstadisticasController.php`
- Ajusta parámetros de temperatura y tokens
- Personaliza respuestas del sistema

### **Templates de Correo**
- Edita vistas en `resources/views/`
- Personaliza diseño y contenido

## 🐛 Solución de Problemas

### **Error de Conexión con IA**
```bash
# Verificar que LM Studio esté ejecutándose
php artisan llm:test --provider=lmstudio

# Verificar logs
tail -f storage/logs/laravel.log
```

### **Error de Base de Datos**
```bash
# Verificar conexión
php artisan migrate:status

# Recrear base de datos
php artisan migrate:fresh --seed
```

### **Error de Permisos**
```bash
# Dar permisos a storage
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

## 📊 Rendimiento y Optimización

### **Para Mejor Rendimiento de IA**
- Usa GPU si está disponible en LM Studio
- Cierra otras aplicaciones que consuman RAM
- Considera usar modelos más pequeños si tienes poca RAM

### **Para Mejor Rendimiento General**
- Usa `npm run build` para producción
- Configura cache de Laravel
- Optimiza imágenes antes de subirlas

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👥 Equipo

- **Desarrollo Principal:** [AndreZM09](https://github.com/AndreZM09)
- **IA Integration:** Configuración LM Studio + DeepSeek
- **Frontend:** Bootstrap 5 + CSS personalizado
- **Backend:** Laravel 11 + MySQL

## 📞 Soporte

Si tienes problemas o preguntas:

1. **Revisa la documentación** en este README
2. **Consulta los logs** en `storage/logs/laravel.log`
3. **Verifica la configuración** de LM Studio
4. **Abre un issue** en GitHub

## 🎉 ¡Disfruta de tu IA Gratuita!

Este proyecto te permite usar Inteligencia Artificial de forma **completamente gratuita** para optimizar tus campañas de marketing por correo electrónico. ¡No hay límites de uso ni costos ocultos!

---

**⭐ Si te gusta este proyecto, ¡dale una estrella en GitHub!**