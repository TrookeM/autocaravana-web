<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// Importamos los comandos por su nombre de clase
use App\Console\Commands\SendPaymentReminders;
use App\Console\Commands\SendReviewRequests;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ¡¡Asegúrate de que usa el signature correcto!!
        $schedule->command('app:process-payment-deadlines')->dailyAt('04:00'); 
        
        $schedule->command(SendReviewRequests::class)->dailyAt('10:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}