<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Click;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function testClick()
    {
        try {
            // Intentar crear un registro de prueba
            $click = Click::create([
                'email' => 'prueba@test.com',
                'id_img' => 1,
                'id_person' => 999,
                'created_at' => now(),
                'email_sent_at' => now()
            ]);
            
            // Eliminar el registro de prueba
            if ($click) {
                DB::table('clicks')->where('id_person', 999)->delete();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Prueba completada con éxito. El modelo Click funciona correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al probar el modelo Click: ' . $e->getMessage()
            ], 500);
        }
    }
} 