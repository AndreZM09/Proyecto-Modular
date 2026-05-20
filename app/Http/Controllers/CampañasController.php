<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\EmailImage;
use App\Models\Click;

class CampañasController extends Controller
{
    public function index()
    {
        $imagenes = DB::table('email_images')->orderBy('created_at', 'desc')->get();
        return view('campañas', compact('imagenes'));
    }

    /**
     * Elimina una campaña completa: registro de email_images, clicks asociados
     * y el archivo de imagen físico en disco.
     *
     * El borrado se hace dentro de una transacción para garantizar atomicidad:
     * si algo falla, no quedan registros huérfanos. El archivo físico se borra
     * DESPUÉS del commit para que si la transacción se revierte, no perdamos
     * la imagen.
     */
    public function destroy($id)
    {
        try {
            // 1) Cargar la campaña (404 si no existe)
            $campaign = EmailImage::find($id);
            if (!$campaign) {
                return response()->json([
                    'success' => false,
                    'message' => 'La campaña no existe o ya fue eliminada.',
                ], 404);
            }

            // Guardar datos antes del borrado para usarlos después
            $filename = $campaign->filename;
            $subject  = $campaign->subject;

            // 2) Transacción: borrar clicks + email_images de forma atómica
            DB::transaction(function () use ($id) {
                $deletedClicks = Click::where('id_img', $id)->delete();
                EmailImage::where('id', $id)->delete();

                Log::info('CampañasController::destroy - registros borrados', [
                    'campaign_id' => $id,
                    'clicks_borrados' => $deletedClicks,
                ]);
            });

            // 3) Borrar archivo físico (solo si el commit fue exitoso).
            // Si falla el unlink no es crítico — los registros ya se fueron.
            // Solo lo logueamos.
            $deletedFile = false;
            if ($filename) {
                $path = storage_path('app/public/email_images/' . $filename);
                if (file_exists($path)) {
                    $deletedFile = @unlink($path);
                    if (!$deletedFile) {
                        Log::warning('CampañasController::destroy - no se pudo borrar el archivo físico', [
                            'path' => $path,
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Campaña "' . $subject . '" eliminada correctamente.',
                'file_deleted' => $deletedFile,
            ]);

        } catch (\Exception $e) {
            Log::error('CampañasController::destroy - error', [
                'campaign_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la campaña: ' . $e->getMessage(),
            ], 500);
        }
    }
}
