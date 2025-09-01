# Configuración de IA OpenAI para el Proyecto Modular

## Requisitos Previos

1. **Cuenta de OpenAI**: Necesitas una cuenta en [OpenAI](https://openai.com/)
2. **API Key**: Obtén tu clave API desde el [dashboard de OpenAI](https://platform.openai.com/api-keys)

## Configuración del Archivo .env

Crea un archivo `.env` en la raíz de tu proyecto con el siguiente contenido:

```env
APP_NAME="Proyecto Modular"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=proyecto_modular
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# OpenAI API Configuration
OPENAI_API_KEY=tu_api_key_aqui
```

## Pasos de Configuración

1. **Generar APP_KEY**:
   ```bash
   php artisan key:generate
   ```

2. **Configurar la base de datos**:
   - Modifica `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` según tu configuración
   - Ejecuta las migraciones: `php artisan migrate`

3. **Configurar OpenAI**:
   - Reemplaza `tu_api_key_aqui` con tu API key real de OpenAI
   - Ejemplo: `OPENAI_API_KEY=sk-1234567890abcdef...`

## Verificación de la Configuración

1. **Verificar que la API key esté configurada**:
   ```bash
   php artisan tinker
   echo env('OPENAI_API_KEY');
   ```

2. **Probar la funcionalidad de IA**:
   - Ve a la vista de correos (`/correos`)
   - Haz clic en "Obtener Recomendaciones de IA"
   - Deberías ver las recomendaciones generadas por la IA

## Costos de OpenAI

**IMPORTANTE**: OpenAI cobra por uso de la API. Los costos dependen del modelo usado:

- **GPT-3.5-turbo**: ~$0.002 por 1K tokens (más económico)
- **GPT-4**: ~$0.03 por 1K tokens (más potente)

### Estimación de Costos para el Proyecto

- **Recomendaciones automáticas**: ~500-800 tokens por sesión
- **Chat personalizado**: ~300-600 tokens por pregunta
- **Costo estimado por sesión**: $0.001 - $0.003 USD

### Recomendaciones para Reducir Costos

1. **Usar GPT-3.5-turbo** (ya configurado por defecto)
2. **Limitar el número de tokens** en las respuestas
3. **Implementar cache** para respuestas similares
4. **Monitorear el uso** de la API

## Funcionalidades Implementadas

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

## Solución de Problemas

### Error: "No se pudo obtener recomendaciones de la IA"

**Causas posibles**:
1. API key no configurada o incorrecta
2. Sin conexión a internet
3. Cuota de OpenAI agotada
4. Error en la API de OpenAI

**Soluciones**:
1. Verificar que `OPENAI_API_KEY` esté configurada en `.env`
2. Verificar conexión a internet
3. Revisar el dashboard de OpenAI para cuotas
4. Revisar logs de Laravel: `storage/logs/laravel.log`

### Error: "Error al comunicarse con la IA"

**Causas posibles**:
1. Problemas de red
2. API key inválida
3. Servidor de OpenAI no disponible

**Soluciones**:
1. Verificar conectividad de red
2. Regenerar API key en OpenAI
3. Esperar y reintentar

## Seguridad

1. **Nunca compartas tu API key** en código público
2. **El archivo .env debe estar en .gitignore**
3. **Usa variables de entorno** en producción
4. **Monitorea el uso** de tu API key

## Próximos Pasos

1. **Configurar el archivo .env** con tu API key
2. **Probar la funcionalidad** en la vista de correos
3. **Ajustar prompts** según tus necesidades específicas
4. **Implementar cache** para optimizar costos
5. **Monitorear métricas** de uso de la IA

## Soporte

Si tienes problemas con la configuración:
1. Revisa los logs de Laravel
2. Verifica la configuración del archivo .env
3. Confirma que tu API key de OpenAI sea válida
4. Verifica la conectividad de red
