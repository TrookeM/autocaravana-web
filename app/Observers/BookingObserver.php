<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\InvoiceService;

class BookingObserver
{
    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged('status') && $booking->status === 'confirmed') {
            
            if ($booking->invoice()->exists()) {
                return;
            }

            $invoiceService = app(InvoiceService::class);
            $invoiceService->generate($booking);
        }
    }
}