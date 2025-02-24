<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Click;

class EstadisticasController extends Controller
{
    public function index()
    {
        $clicks = Click::all(); // Obtener todos los registros
        $clicksCount = Click::count(); // Contar registros

        return view('estadisticas', compact('clicks', 'clicksCount'));
    }
}
