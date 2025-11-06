<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Mail\PaymentReminderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendPaymentReminders extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     * (Este es el nombre que usaremos en el CRON)
     */
    protected $signature = 'app:send-payment-reminders';

    /**
     * La descripción del comando de consola.
     */
    protected $description = 'Busca reservas que necesiten un recordatorio de pago y lo envía.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('Buscando reservas para enviar recordatorios de pago...');

        // ==========================================================
        // LÓGICA CORREGIDA (RF12.1)
        // ==========================================================
        
        // 1. Define la fecha objetivo:
        //    Buscamos pagos que venzan exactamente en 7 días.
        //    (Puedes cambiar addDays(7) a addDays(3) si prefieres 3 días)
        $targetPaymentDueDate = Carbon::now()->addDays(7)->toDateString();

        // 2. Busca las reservas que cumplen las condiciones
        $bookingsToRemind = Booking::where('payment_due_date', $targetPaymentDueDate)
            // Solo reservas con pago parcial
            ->where('payment_status', Booking::STATUS_DEPOSIT_PAID)
            // Que no hayamos enviado ya
            ->where('reminder_sent', false) 
            ->get();
        // ==========================================================


        if ($bookingsToRemind->isEmpty()) {
            $this->info('No se encontraron reservas que necesiten recordatorio para esa fecha.');
            return;
        }

        $this->info("Se encontraron {$bookingsToRemind->count()} reservas. Enviando emails...");

        foreach ($bookingsToRemind as $booking) {
            try {
                // (Usamos queue() por si son muchos emails, 
                // pero si tienes QUEUE_CONNECTION=sync funcionará al instante)
                Mail::to($booking->customer_email)->queue(new PaymentReminderMail($booking));

                $booking->update(['reminder_sent' => true]);

                $this->info("Recordatorio enviado a: {$booking->customer_email}");
            } catch (\Exception $e) {
                $this->error("Fallo al enviar a: {$booking->customer_email}. Error: {$e->getMessage()}");
            }
        }

        $this->info('Proceso de recordatorios completado.');
    }
}