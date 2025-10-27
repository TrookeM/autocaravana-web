<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Campervan;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Services\PriceCalculatorService; // Importar el servicio

class CampervanCalendar extends Component
{
    public Campervan $campervan;
    public $currentMonth;
    public $currentYear;
    public array $unavailableDates = [];
    public string $unavailableDatesJson = '[]';

    // Para forzar re-renderizado completo
    public $timestamp;

    // Inyectar el servicio
    protected PriceCalculatorService $priceCalculator;

    public function boot(PriceCalculatorService $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    public function mount(Campervan $campervan)
    {
        $this->campervan = $campervan;

        $today = now();
        $this->currentMonth = $today->month;
        $this->currentYear = $today->year;
        $this->timestamp = now()->timestamp;

        $this->loadUnavailableDates();
    }

    /**
     * ===============================================================
     * FUNCIÓN MODIFICADA (Revertida a tu lógica de bucle original)
     * ===============================================================
     */
    public function loadUnavailableDates(): void
    {
        $bookings = $this->campervan->bookings()
            ->where('end_date', '>', now()->subMonth()->startOfMonth()) // Cargar un rango relevante
            ->where('status', '!=', 'cancelled')
            ->get();

        $unavailable = [];
        
        // 1. Bucle para bloquear los RANGOS (tu lógica original)
        // Este bucle 'while ($current->lt($end))' ya excluye el día de check-out
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->start_date);
            $end = Carbon::parse($booking->end_date);

            $current = $start->copy();
            while ($current->lt($end)) { // "lt" = Less Than (menor que)
                $unavailable[$current->toDateString()] = true;
                $current->addDay();
            }
        }

        // 2. LÓGICA AÑADIDA (RF6.4): Bloquear los días de CHECK-OUT si está activado
        if ($this->campervan->no_checkout_booking) {
            // Recorremos las reservas de nuevo y añadimos SÓLO el día de 'end_date'
            foreach ($bookings as $booking) {
                $endDateString = Carbon::parse($booking->end_date)->toDateString();
                $unavailable[$endDateString] = true;
            }
        }
        // ===============================================================

        $this->unavailableDates = $unavailable;
        $this->unavailableDatesJson = json_encode(array_keys($unavailable));
    }
    // ===============================================================
    // FIN DE LA MODIFICACIÓN
    // ===============================================================

    public function nextMonth(): void
    {
        $currentDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $nextDate = $currentDate->copy()->addMonth();

        $this->currentMonth = $nextDate->month;
        $this->currentYear = $nextDate->year;
        $this->timestamp = now()->timestamp;

        $this->loadUnavailableDates();
    }

    public function previousMonth(): void
    {
        $currentDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $today = now()->startOfMonth();

        if ($currentDate->gt($today)) {
            $prevDate = $currentDate->copy()->subMonth();
            $this->currentMonth = $prevDate->month;
            $this->currentYear = $prevDate->year;
            $this->timestamp = now()->timestamp;

            $this->loadUnavailableDates();
        }
    }

    public function getCanGoBackProperty()
    {
        $currentDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
        return $currentDate->gt(now()->startOfMonth());
    }

    protected function getDatesForMonth(int $month, int $year): Collection
    {
        $startOfMonth = Carbon::create($year, $month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
        $startOfWeek = $startOfMonth->dayOfWeekIso; // 1 (Lunes) - 7 (Domingo)

        $days = collect();

        // Relleno para los días antes del día 1
        for ($i = 1; $i < $startOfWeek; $i++) {
            $days->push(['date' => null, 'day_of_month' => null]);
        }

        $today = now()->startOfDay();

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day)->startOfDay();
            $dateString = $date->toDateString();

            $isPast = $date->lt($today);
            $isUnavailable = isset($this->unavailableDates[$dateString]);
            
            $price = $this->priceCalculator->getPriceForDate($this->campervan, $date);

            $days->push([
                'date' => $dateString,
                'day_of_month' => $day,
                'is_today' => $date->isToday(),
                'is_disabled' => $isPast || $isUnavailable,
                'is_unavailable' => $isUnavailable,
                'price' => $price,
            ]);
        }

        return $days;
    }

    public function render()
    {
        $currentMonthDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $nextMonthDate = $currentMonthDate->copy()->addMonth();

        // Generamos los nombres de los días L, M, X, J, V, S, D
        $dayNames = collect(range(0, 6))->map(function ($day) {
            $date = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays($day);
            if ($date->dayOfWeekIso === 3) { // Miércoles
                return 'X';
            }
            return $date->locale('es')->translatedFormat('D');
        });

        return view('livewire.campervan-calendar', [
            'currentMonthName' => $currentMonthDate->locale('es')->translatedFormat('F'),
            'currentYear' => $this->currentYear,
            'currentDates' => $this->getDatesForMonth($this->currentMonth, $this->currentYear),

            'nextMonth' => $nextMonthDate->month,
            'nextMonthName' => $nextMonthDate->locale('es')->translatedFormat('F'),
            'nextYear' => $nextMonthDate->year,
            'nextDates' => $this->getDatesForMonth($nextMonthDate->month, $nextMonthDate->year),

            'canGoBack' => $this->canGoBack,
            'timestamp' => $this->timestamp,

            'dayNames' => $dayNames
        ]);
    }
}
