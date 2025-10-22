<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // Import para el envío de correos
use Illuminate\Support\Facades\Log;  // Import para registrar errores
use App\Mail\ContactMail;            // Import de la clase que crea el correo
use App\Models\Campervan;            // Necesario si se usa en otros métodos

class HomeController extends Controller
{
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

    public function contact()
    {
        return view('contacto');
    }

    /**
     * Procesa el formulario de contacto y envía el correo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeContactForm(Request $request)
    {
        // 1. Validación de los datos del formulario (IMPORTANTE)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string',
        ]);

        // 2. Definir el destinatario
        $recipientEmail = env('CONTACT_RECIPIENT_EMAIL', 'default@example.com');

        // 3. Enviar el correo
        try {
            Mail::to($recipientEmail)->send(new ContactMail($validated));

            // 4. Redireccionar con un mensaje de éxito
            return redirect()->route('contact')->with('success', '¡Tu mensaje ha sido enviado con éxito! Te responderemos lo antes posible.');
        } catch (\Exception $e) {
            // 5. Manejo de errores - Usa Log::error para registrar fallos en el envío de correo
            Log::error('Mail Error in storeContactForm: ' . $e->getMessage());
            return redirect()->route('contact')->with('error', 'Hubo un problema al enviar tu mensaje. Por favor, inténtalo de nuevo o llámanos.')->withInput();
        }
    }
}
