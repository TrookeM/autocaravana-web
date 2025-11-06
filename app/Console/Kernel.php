<?php

namespace App\Console;

// Comandos Registrados
use App\Console\Commands\SendPaymentReminders;
use App\Console\Commands\SendReviewRequests; // <-- AÑADIDO

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define la programación de comandos de la aplicación.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // RF12.1 (Parte 1): Recordatorio de Pago
        // Se ejecuta todos los días a las 9:00 AM
        $schedule->command(SendPaymentReminders::class)->dailyAt('09:00');

        // RF12.1 (Parte 2): Solicitud de Reseña
        // Se ejecuta todos los días a las 10:00 AM
        $schedule->command(SendReviewRequests::class)->dailyAt('10:00'); // <-- AÑADIDO
    }

    /**
     * Registra los comandos para la aplicación.
     */
    protected function commands(): void
    {
        $this.load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}