<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campervan; // <-- Importamos el modelo

class HomeController extends Controller
{
    /**
     * Muestra la página de inicio.
     */
    public function index()
    {
        // 1. Busca en la BBDD todas las autocaravanas...
        $campervans = Campervan::where('is_visible', true) // ...que estén marcadas como visibles
                                ->orderBy('price_per_night', 'asc') // ...ordenadas por precio
                                ->get(); // ...y tráelas

        // 2. Carga la vista (el HTML) y pásale los datos
        return view('welcome', [
            'campervans' => $campervans // Pasa la variable a la vista
        ]);
    }
}