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
    protected $signature = 'openai:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the OpenAI API connection';

    /**
     * Execute the console command.
     */
    public function handle()
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
                    $this->info('💡 Verifica tu API key en el dashboard de OpenAI');
                } elseif ($response->status() === 429) {
                    $this->error('⏰ Límite de tasa excedido');
                    $this->info('💡 Espera un momento y vuelve a intentar');
                } elseif ($response->status() === 402) {
                    $this->error('💰 Cuota de API agotada');
                    $this->info('💡 Verifica tu saldo en el dashboard de OpenAI');
                }
                
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error de conexión: ' . $e->getMessage());
            Log::error('OpenAI Test Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
