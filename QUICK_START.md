# 🚀 Inicio Rápido - Proyecto Modular

## ⚡ Instalación en 5 Minutos

### **1. Clonar y Configurar**
```bash
git clone https://github.com/AndreZM09/Proyecto-Modular.git
cd Proyecto-Modular
composer install
npm install
```

### **2. Configurar Base de Datos**
```bash
# Crear archivo .env
cp .env.example .env

# Editar .env con tus credenciales de MySQL
# Generar clave de aplicación
php artisan key:generate

# Crear base de datos y tablas
php artisan migrate --seed
```

### **3. Configurar IA (Opcional pero Recomendado)**
1. **Instalar LM Studio**: [lmstudio.ai](https://lmstudio.ai/)
2. **Descargar modelo DeepSeek** en LM Studio
3. **Iniciar servidor** en puerto 1234
4. **Probar conexión**:
   ```bash
   php artisan llm:test --provider=lmstudio
   ```

### **4. Iniciar Servidor**
```bash
# Compilar assets
npm run dev

# Iniciar servidor
php artisan serve
# O usar XAMPP: http://localhost/Proyecto-Modular/public
```

## 🎯 **¡Listo!** 

Accede a `http://localhost:8000` y disfruta de tu sistema de marketing con IA gratuita.

## 📚 **Documentación Completa**

- [README.md](README.md) - Documentación principal
- [CONFIGURACION_COMPLETA.md](CONFIGURACION_COMPLETA.md) - Configuración detallada
- [CONFIGURACION_LM_STUDIO.md](CONFIGURACION_LM_STUDIO.md) - Configuración de IA

## 🆘 **¿Problemas?**

1. **Error de conexión IA**: Verifica que LM Studio esté ejecutándose
2. **Error de base de datos**: Ejecuta `php artisan migrate:fresh --seed`
3. **Error de permisos**: Ejecuta `chmod -R 775 storage/`

---

**¡Disfruta de tu IA gratuita! 🤖✨**
