<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Mail\PaymentReminderMail;
use App\Mail\BookingCancelledMail; // <-- ¡NUEVO IMPORT "AGRESIVO"!
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendPaymentReminders extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     */
    protected $signature = 'app:process-payment-deadlines';

    /**
     * La descripción del comando de consola.
     */
    protected $description = 'Envía recordatorios de pago (21 y 7 días) y cancela reservas vencidas.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('Iniciando procesamiento de fechas de pago...');

        $this->handlePassiveReminders_Stage1(); // Recordatorio de 3 semanas
        $this->handlePassiveReminders_Stage2(); // Recordatorio de 1 semana
        $this->handleAggressiveCancellations(); // Cancelaciones

        $this->info('Procesamiento completado.');
    }

    /**
     * Lógica "Pasiva 1": Enviar recordatorio 21 días antes.
     */
    private function handlePassiveReminders_Stage1()
    {
        $this->info('Buscando reservas para recordatorio (vencen en 21 días)...');
        $targetDate = Carbon::today()->addDays(21)->toDateString();

        // ==================================
        // ¡¡BUG CORREGIDO!!
        // ==================================
        $bookingsToRemind = Booking::where('payment_status', Booking::STATUS_DEPOSIT_PAID) // <-- CORREGIDO
            ->where('status', Booking::STATUS_CONFIRMED) // <-- Añadido para seguridad
            ->whereDate('payment_due_date', $targetDate)
            ->where('reminder_sent', 0)
            ->get();

        if ($bookingsToRemind->isEmpty()) {
            $this->info('No se encontraron reservas para el recordatorio de 21 días.');
            return;
        }

        $this->info("Se encontraron {$bookingsToRemind->count()} reservas. Enviando emails (Etapa 1)...");
        foreach ($bookingsToRemind as $booking) {
            $this->sendReminder($booking, 1);
        }
    }

    /**
     * Lógica "Pasiva 2": Enviar recordatorio 7 días antes.
     */
    private function handlePassiveReminders_Stage2()
    {
        $this->info('Buscando reservas para recordatorio (vencen en 7 días)...');
        $targetDate = Carbon::today()->addDays(7)->toDateString();

        // ==================================
        // ¡¡BUG CORREGIDO!!
        // ==================================
        $bookingsToRemind = Booking::where('payment_status', Booking::STATUS_DEPOSIT_PAID) // <-- CORREGIDO
            ->where('status', Booking::STATUS_CONFIRMED) // <-- Añadido
            ->whereDate('payment_due_date', $targetDate)
            ->where('reminder_sent', 1)
            ->get();

        if ($bookingsToRemind->isEmpty()) {
            $this->info('No se encontraron reservas para el recordatorio de 7 días.');
            return;
        }

        $this->info("Se encontraron {$bookingsToRemind->count()} reservas. Enviando emails (Etapa 2)...");
        foreach ($bookingsToRemind as $booking) {
            $this->sendReminder($booking, 2);
        }
    }

    /**
     * Lógica "Agresiva": Cancelar reservas vencidas.
     */
    private function handleAggressiveCancellations()
    {
        $this->info('Buscando reservas vencidas para cancelar...');

        $today = Carbon::today();

        $bookingsToCancel = Booking::where('payment_status', Booking::STATUS_DEPOSIT_PAID)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->whereNotNull('payment_due_date')
            ->whereDate('payment_due_date', '<', $today)
            ->get();

        if ($bookingsToCancel->isEmpty()) {
            $this->info('No se encontraron reservas vencidas para cancelar.');
            return;
        }

        $this->info("Se encontraron {$bookingsToCancel->count()} reservas vencidas para CANCELAR.");
        foreach ($bookingsToCancel as $booking) {
            try {
                // ==================================
                // ¡¡LÍNEA CORREGIDA!!
                // ==================================
                $booking->status = Booking::STATUS_CANCELLED;
                $booking->checkout_notes = 'Cancelada automáticamente por falta de pago final.'; // <-- Usamos el campo 'checkout_notes'
                $booking->save();
                // ==================================

                $reason = "Tu reserva ha sido cancelada automáticamente por falta de pago del importe restante.";
                Mail::to($booking->customer_email)->send(new BookingCancelledMail($booking, $reason));

                $this->warn(" - Reserva #{$booking->id} CANCELADA. Email enviado a: {$booking->customer_email}");
            } catch (\Exception $e) {
                $this->error(" - ERROR al cancelar la reserva #{$booking->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Función helper para enviar recordatorio e incrementar el contador
     */
    private function sendReminder(Booking $booking, int $stage)
    {
        try {
            Mail::to($booking->customer_email)->queue(new PaymentReminderMail($booking));
            $booking->increment('reminder_sent');
            $this->info("Recordatorio (Etapa $stage) enviado a: {$booking->customer_email} (Reserva #{$booking->id})");
        } catch (\Exception $e) {
            $this->error("Fallo al enviar (Etapa $stage) a: {$booking->customer_email}. Error: {$e->getMessage()}");
        }
    }
}
