# Configuración de LM Studio con DeepSeek para el Proyecto Modular

## 🚀 Configuración Gratuita y Local

Esta configuración te permite usar IA de forma **completamente gratuita** usando LM Studio con el modelo DeepSeek ejecutándose localmente en tu computadora.

## Requisitos Previos

1. **LM Studio**: Descarga e instala desde [lmstudio.ai](https://lmstudio.ai/)
2. **Modelo DeepSeek**: Descarga el modelo DeepSeek desde LM Studio
3. **RAM**: Mínimo 8GB (recomendado 16GB+)
4. **GPU**: Opcional pero recomendada para mejor rendimiento

## Pasos de Configuración

### 1. Instalar y Configurar LM Studio

1. **Descargar LM Studio**:
   - Ve a [lmstudio.ai](https://lmstudio.ai/)
   - Descarga la versión para Windows
   - Instala la aplicación

2. **Descargar el Modelo DeepSeek**:
   - Abre LM Studio
   - Ve a la pestaña "Models"
   - Busca "DeepSeek" o "deepseek-r1"
   - Descarga el modelo (aproximadamente 2-4GB)
   - Recomendado: `deepseek-r1:latest` o `deepseek-r1-distill-q4_k_m`

### 2. Configurar el Servidor Local

1. **Cargar el Modelo**:
   - En LM Studio, ve a la pestaña "Chat"
   - Selecciona el modelo DeepSeek descargado
   - Haz clic en "Load Model"

2. **Iniciar el Servidor**:
   - Ve a la pestaña "Local Server"
   - Asegúrate de que el puerto sea `1234`
   - Haz clic en "Start Server"
   - Deberías ver: "Server running on http://localhost:1234"

### 3. Configurar el Archivo .env

Crea un archivo `.env` en la raíz de tu proyecto con el siguiente contenido:

```env
APP_NAME="Proyecto Modular"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Configuración de base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=proyecto_modular
DB_USERNAME=root
DB_PASSWORD=

# Configuración de LM Studio (REEMPLAZA OPENAI)
LLM_BASE_URL=http://localhost:1234/v1
LLM_API_KEY=lm-studio
LLM_MODEL=deepseek-r1:latest

# Configuración legacy (mantener para compatibilidad)
OPENAI_API_KEY=not_used
```

### 4. Generar APP_KEY

```bash
cd Proyecto-Modular
php artisan key:generate
```

### 5. Configurar la Base de Datos

```bash
# Ejecutar migraciones
php artisan migrate

# Opcional: ejecutar seeders
php artisan db:seed
```

## Verificación de la Configuración

### Probar la Conexión

```bash
# Probar LM Studio (recomendado)
php artisan llm:test --provider=lmstudio

# Probar OpenAI (si tienes API key)
php artisan llm:test --provider=openai
```

### Verificar en el Navegador

1. Ve a `http://localhost/correos`
2. Haz clic en "Obtener Recomendaciones de IA"
3. Deberías ver las recomendaciones generadas por DeepSeek

## Ventajas de LM Studio

### ✅ **Completamente Gratuito**
- Sin costos por tokens
- Sin límites de uso
- Sin necesidad de API keys

### ✅ **Privacidad Total**
- Todo se ejecuta localmente
- Tus datos no salen de tu computadora
- Control total sobre la información

### ✅ **Rendimiento Excelente**
- Respuestas rápidas (sin latencia de red)
- Funciona sin conexión a internet
- Personalizable según tu hardware

### ✅ **Modelos de Calidad**
- DeepSeek es comparable a GPT-4
- Actualizaciones gratuitas
- Múltiples modelos disponibles

## Solución de Problemas

### Error: "Connection refused"

**Causa**: LM Studio no está ejecutándose o el servidor no está iniciado.

**Solución**:
1. Abre LM Studio
2. Carga el modelo DeepSeek
3. Ve a "Local Server" y haz clic en "Start Server"
4. Verifica que aparezca "Server running on http://localhost:1234"

### Error: "Model not found"

**Causa**: El modelo especificado no está cargado en LM Studio.

**Solución**:
1. En LM Studio, ve a "Chat"
2. Selecciona el modelo DeepSeek
3. Haz clic en "Load Model"
4. Verifica que el modelo esté completamente cargado

### Error: "Timeout"

**Causa**: El modelo está tardando mucho en responder.

**Solución**:
1. Verifica que tengas suficiente RAM disponible
2. Cierra otras aplicaciones que consuman memoria
3. Considera usar un modelo más pequeño si tienes poca RAM

### Respuestas Lentas

**Optimizaciones**:
1. **Usar GPU**: En LM Studio, habilita "Use GPU" si tienes una tarjeta gráfica
2. **Modelo más pequeño**: Usa `deepseek-r1-distill-q4_k_m` en lugar de `deepseek-r1:latest`
3. **Más RAM**: Cierra otras aplicaciones

## Configuración Avanzada

### Cambiar el Modelo

Para usar un modelo diferente, modifica en tu `.env`:

```env
LLM_MODEL=deepseek-r1-distill-q4_k_m
```

### Cambiar el Puerto

Si el puerto 1234 está ocupado:

1. En LM Studio, cambia el puerto en "Local Server"
2. Actualiza tu `.env`:
```env
LLM_BASE_URL=http://localhost:PUERTO_NUEVO/v1
```

### Configuración de Rendimiento

En LM Studio, puedes ajustar:
- **Context Length**: 4096 (recomendado)
- **Temperature**: 0.7 (para creatividad)
- **Max Tokens**: 512 (para respuestas concisas)

## Comparación de Costos

| Servicio | Costo por 1K tokens | Costo mensual estimado |
|----------|---------------------|------------------------|
| **LM Studio** | **$0.00** | **$0.00** |
| OpenAI GPT-3.5 | $0.002 | $5-20 |
| OpenAI GPT-4 | $0.03 | $50-200 |

## Funcionalidades Disponibles

### 1. Recomendaciones Automáticas de IA
- Analiza el historial de campañas
- Proporciona recomendaciones para mejorar imágenes de correo
- Incluye consejos sobre colores, tipografías, estructura, etc.

### 2. Chat Personalizado con IA
- Permite hacer preguntas específicas sobre contenido
- Considera el contexto de la imagen actual
- Responde en tiempo real con recomendaciones personalizadas

### 3. Análisis de Rendimiento
- Basado en datos históricos reales de campañas
- Métricas de tasa de apertura y clics
- Predicciones para futuras campañas

## Comandos Útiles

```bash
# Probar conexión
php artisan llm:test --provider=lmstudio

# Limpiar cache
php artisan cache:clear

# Ver logs
tail -f storage/logs/laravel.log

# Verificar configuración
php artisan config:show llm
```

## Soporte

Si tienes problemas:

1. **Verifica los logs**: `storage/logs/laravel.log`
2. **Prueba la conexión**: `php artisan llm:test --provider=lmstudio`
3. **Reinicia LM Studio**: Cierra y abre la aplicación
4. **Verifica la configuración**: Revisa que el archivo `.env` esté correcto

---

**¡Disfruta de tu IA gratuita y local! 🎉**

