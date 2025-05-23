<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampañasController extends Controller
{
    public function index()
    {
        $imagenes = DB::table('email_images')->orderBy('created_at', 'desc')->get();
        return view('campañas', compact('imagenes'));
    }
}
