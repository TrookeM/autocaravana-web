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
    /**
     * Muestra la página de detalle de una autocaravana.
     */
    public function show(Campervan $campervan)
    {
        // Gracias a la "magia" de Laravel, al llamar al parámetro $campervan
        // él solo ya busca en la BBDD la autocaravana por su ID.

        // Cargamos la nueva vista y le pasamos la autocaravana encontrada.
        return view('detail', [
            'campervan' => $campervan
        ]);
    }
}