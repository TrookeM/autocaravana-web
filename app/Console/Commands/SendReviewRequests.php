<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Mail\ReviewRequestMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendReviewRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-review-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca reservas finalizadas y envía un email pidiendo una reseña.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando reservas finalizadas para solicitar reseña...');

        // 1. Reservas que terminaron hace 2 días
        $targetEndDate = Carbon::now()->subDays(2)->toDateString();

        // 2. Buscar reservas que cumplan las condiciones
        $bookingsToRequest = Booking::where('end_date', $targetEndDate)
            ->where('status', Booking::STATUS_COMPLETED)
            ->doesntHave('review')
            ->get();

        if ($bookingsToRequest->isEmpty()) {
            $this->info('No se encontraron reservas que necesiten solicitud de reseña.');
            return;
        }

        $this->info("Se encontraron {$bookingsToRequest->count()} reservas. Enviando emails...");

        foreach ($bookingsToRequest as $booking) {
            try {
                Mail::to($booking->customer_email)
                    ->queue(new ReviewRequestMail($booking));

                $this->info("Solicitud de reseña enviada a: {$booking->customer_email} (Reserva #{$booking->id})");

            } catch (\Exception $e) {
                $this->error("Fallo al enviar a: {$booking->customer_email}. Error: {$e->getMessage()}");
            }
        }

        $this->info('Proceso de solicitud de reseñas completado.');
    }
}
