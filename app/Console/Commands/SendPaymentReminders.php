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
        $this->info('Buscando reservas para enviar recordatorios...');

        // 1. Define la fecha objetivo (exactamente 2 DÍAS desde hoy)
        $targetDate = Carbon::now()->addDays(2)->toDateString();

        // 2. Busca las reservas que cumplen las condiciones
        $bookingsToRemind = Booking::where('start_date', $targetDate)
            // Usamos la columna correcta (no el 'status' general)
            ->where('payment_status', Booking::STATUS_DEPOSIT_PAID)
            ->where('reminder_sent', false) // Que no hayamos enviado ya
            ->get();

        if ($bookingsToRemind->isEmpty()) {
            $this->info('No se encontraron reservas que necesiten recordatorio.');
            return;
        }

        $this->info("Se encontraron {$bookingsToRemind->count()} reservas. Enviando emails...");

        foreach ($bookingsToRemind as $booking) {
            try {
                Mail::to($booking->customer_email)->send(new PaymentReminderMail($booking));

                $booking->update(['reminder_sent' => true]);

                $this->info("Recordatorio enviado a: {$booking->customer_email}");
            } catch (\Exception $e) {
                $this->error("Fallo al enviar a: {$booking->customer_email}. Error: {$e->getMessage()}");
            }
        }

        $this->info('Proceso de recordatorios completado.');
    }
}
