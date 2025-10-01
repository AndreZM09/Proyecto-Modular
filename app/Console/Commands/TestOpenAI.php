<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestOpenAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'llm:test {--provider=lmstudio : Provider to test (lmstudio, openai)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the LLM API connection (LM Studio or OpenAI)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $provider = $this->option('provider');
        
        if ($provider === 'lmstudio') {
            return $this->testLMStudio();
        } elseif ($provider === 'openai') {
            return $this->testOpenAI();
        } else {
            $this->error('❌ Proveedor no válido. Usa: lmstudio o openai');
            return 1;
        }
    }

    private function testLMStudio()
    {
        $this->info('🧪 Probando conexión con LM Studio...');
        
        // Verificar configuración de LM Studio
        $baseUrl = env('LLM_BASE_URL', 'http://localhost:1234/v1');
        $apiKey = env('LLM_API_KEY', 'lm-studio');
        $model = env('LLM_MODEL', 'deepseek-r1:latest');
        
        $this->info("🔗 URL base: {$baseUrl}");
        $this->info("🤖 Modelo: {$model}");
        
        // Probar la conexión con LM Studio
        try {
            $this->info('🌐 Enviando solicitud de prueba a LM Studio...');
            
            $endpoint = rtrim($baseUrl, '/') . '/chat/completions';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($endpoint, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un asistente útil.'],
                    ['role' => 'user', 'content' => 'Responde solo con "Conexión exitosa"']
                ],
                'max_tokens' => 10,
                'temperature' => 0,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['choices'][0]['message']['content'])) {
                    $this->info('✅ Conexión exitosa con LM Studio!');
                    $this->info('📝 Respuesta de prueba: ' . $data['choices'][0]['message']['content']);
                    $this->info('🔑 Configuración válida');
                    $this->info('🚀 La funcionalidad de IA está lista para usar');
                    
                    // Mostrar información de uso
                    $this->newLine();
                    $this->info('📊 Información de uso:');
                    $this->line('• Modelo: ' . $model);
                    $this->line('• URL: ' . $endpoint);
                    $this->line('• Costo: $0.00 USD (gratuito local)');
                    
                    return 0;
                } else {
                    $this->error('❌ Respuesta inesperada de LM Studio');
                    $this->line('Respuesta completa: ' . json_encode($data, JSON_PRETTY_PRINT));
                    return 1;
                }
            } else {
                $this->error('❌ Error en la respuesta de LM Studio');
                $this->line('Status: ' . $response->status());
                $this->line('Respuesta: ' . $response->body());
                $this->newLine();
                $this->info('💡 Verifica que:');
                $this->line('• LM Studio esté ejecutándose');
                $this->line('• El modelo DeepSeek esté cargado');
                $this->line('• El servidor local esté en http://localhost:1234');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error de conexión: ' . $e->getMessage());
            $this->newLine();
            $this->info('💡 Verifica que:');
            $this->line('• LM Studio esté ejecutándose');
            $this->line('• El modelo DeepSeek esté cargado');
            $this->line('• El servidor local esté en http://localhost:1234');
            return 1;
        }
    }

    private function testOpenAI()
    {
        $this->info('🧪 Probando conexión con OpenAI...');
        
        // Verificar si la API key está configurada
        $apiKey = env('OPENAI_API_KEY');
        
        if (!$apiKey) {
            $this->error('❌ OPENAI_API_KEY no está configurada en el archivo .env');
            $this->info('💡 Crea un archivo .env en la raíz del proyecto y agrega: OPENAI_API_KEY=tu_api_key_aqui');
            return 1;
        }
        
        if ($apiKey === 'tu_api_key_aqui') {
            $this->error('❌ OPENAI_API_KEY no ha sido configurada con una clave real');
            $this->info('💡 Reemplaza "tu_api_key_aqui" con tu API key real de OpenAI');
            return 1;
        }
        
        $this->info('✅ API Key encontrada');
        
        // Probar la conexión con OpenAI
        try {
            $this->info('🌐 Enviando solicitud de prueba a OpenAI...');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un asistente útil.'],
                    ['role' => 'user', 'content' => 'Responde solo con "Conexión exitosa"']
                ],
                'max_tokens' => 10,
                'temperature' => 0,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['choices'][0]['message']['content'])) {
                    $this->info('✅ Conexión exitosa con OpenAI!');
                    $this->info('📝 Respuesta de prueba: ' . $data['choices'][0]['message']['content']);
                    $this->info('🔑 API Key válida');
                    $this->info('🚀 La funcionalidad de IA está lista para usar');
                    
                    // Mostrar información de uso
                    $this->newLine();
                    $this->info('📊 Información de uso:');
                    $this->line('• Modelo: gpt-3.5-turbo');
                    $this->line('• Tokens usados: ' . ($data['usage']['total_tokens'] ?? 'N/A'));
                    $this->line('• Costo estimado: ~$0.001 USD');
                    
                    return 0;
                } else {
                    $this->error('❌ Respuesta inesperada de OpenAI');
                    $this->line('Respuesta completa: ' . json_encode($data, JSON_PRETTY_PRINT));
                    return 1;
                }
            } else {
                $this->error('❌ Error en la respuesta de OpenAI');
                $this->line('Código de estado: ' . $response->status());
                $this->line('Respuesta: ' . $response->body());
                
                // Interpretar errores comunes
                if ($response->status() === 401) {
                    $this->error('🔑 API Key inválida o expirada');
                } elseif ($response->status() === 429) {
                    $this->error('⏰ Límite de cuota excedido');
                } elseif ($response->status() === 500) {
                    $this->error('🔧 Error interno del servidor de OpenAI');
                }
                
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error de conexión: ' . $e->getMessage());
            return 1;
        }
    }
}