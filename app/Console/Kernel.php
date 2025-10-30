<?php

namespace App\Console;

// AÑADE ESTA LÍNEA EN LOS 'use'
use App\Console\Commands\SendPaymentReminders;

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

        // AÑADE ESTA LÍNEA:
        // Ejecuta tu comando 'app:send-payment-reminders' todos los días a las 9:00 AM
        $schedule->command(SendPaymentReminders::class)->dailyAt('09:00');
    }

    /**
     * Registra los comandos para la aplicación.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}