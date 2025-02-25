<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Click;
use Illuminate\Support\Facades\DB;

class EstadisticasController extends Controller
{
    public function index()
    {
        $clicks = Click::all();
        $clicksCount = Click::count();

        // Datos para la gráfica circular
        $totalEmails = 100; // Cambiar por el número real de emails enviados
        $opened = $clicksCount;
        $notOpened = $totalEmails - $opened;

        // Datos para la gráfica por zonas (IP)
        $clicksByZone = Click::select('ip_address', DB::raw('count(*) as total'))
                            ->groupBy('ip_address')
                            ->get();

        return view('estadisticas', compact('clicks', 'clicksCount', 'opened', 'notOpened', 'clicksByZone', 'totalEmails'));
    }

    public function trackClick(Request $request)
    {
        $email = $request->query('email');
        $ip = $request->ip();

        Click::create([
            'email' => $email,
            'ip_address' => $ip
        ]);

        return response('Clic registrado', 200);
    }
}