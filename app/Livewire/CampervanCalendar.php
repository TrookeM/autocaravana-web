<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Campervan;
use App\Models\Booking;
use App\Models\Blocking;
use App\Models\DurationDiscount; // <-- AÑADIDO (RF12.2)
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use App\Services\PriceCalculatorService;

class CampervanCalendar extends Component
{
    public Campervan $campervan;
    public $currentMonth;
    public $currentYear;
    // Estos arrays son para el lookup interno
    public array $unavailableDates = [];
    protected array $maintenanceDatesLookup = []; // <-- NUEVA PROPIEDAD PROTEGIDA

    // Estos JSON son para Alpine.js
    public string $unavailableDatesJson = '[]';
    public string $maintenanceDatesJson = '[]';

    public $timestamp;
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

    // ... (loadUnavailableDates, nextMonth, previousMonth, getCanGoBackProperty, getDatesForMonth no cambian) ...
    
    public function loadUnavailableDates(): void
    {
        // ... (sin cambios)
        // ... (código de bookings, blockings, etc)
        $bookings = $this->campervan->bookings()
            ->where('end_date', '>', now()->subMonth()->startOfMonth())
            ->where('status', '!=', 'cancelled')
            ->get();
 
        $unavailable = [];
        $maintenance = []; 
 
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->start_date);
            $end = Carbon::parse($booking->end_date);
            $current = $start->copy();
            while ($current->lt($end)) {
                $unavailable[$current->toDateString()] = true;
                $current->addDay();
            }
        }
 
        if ($this->campervan->no_checkout_booking) {
            foreach ($bookings as $booking) {
                $endDateString = Carbon::parse($booking->end_date)->toDateString();
                $unavailable[$endDateString] = true;
            }
        }
 
        $blockings = $this->campervan->blockings()
            ->where('end_date', '>=', now()->startOfDay())
            ->get();
 
        foreach ($blockings as $blocking) {
            $start = Carbon::parse($blocking->start_date);
            $end = Carbon::parse($blocking->end_date);
            $current = $start->copy();
            while ($current->lte($end)) {
                $dateString = $current->toDateString();
                $unavailable[$dateString] = true; 
                $maintenance[$dateString] = true; 
                $current->addDay();
            }
        }
 
        $this->unavailableDates = $unavailable; 
        $this->maintenanceDatesLookup = $maintenance;
 
        $this->unavailableDatesJson = json_encode(array_keys($unavailable));
        $this->maintenanceDatesJson = json_encode(array_keys($maintenance));
    }
 
    public function nextMonth(): void
    {
        // ... (sin cambios)
        $currentDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $nextDate = $currentDate->copy()->addMonth();
 
        $this->currentMonth = $nextDate->month;
        $this->currentYear = $nextDate->year;
        $this->timestamp = now()->timestamp;
 
        $this->loadUnavailableDates();
 
        $this->dispatch('dates-updated', 
            unavailable: $this->unavailableDatesJson, 
            maintenance: $this->maintenanceDatesJson
        );
    }
 
    public function previousMonth(): void
    {
        // ... (sin cambios)
        $currentDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $today = now()->startOfMonth();
 
        if ($currentDate->gt($today)) {
            $prevDate = $currentDate->copy()->subMonth();
            $this->currentMonth = $prevDate->month;
            $this->currentYear = $prevDate->year;
            $this->timestamp = now()->timestamp;
 
            $this->loadUnavailableDates();
            
            $this->dispatch('dates-updated', 
                unavailable: $this->unavailableDatesJson, 
                maintenance: $this->maintenanceDatesJson
            );
        }
    }
 
    public function getCanGoBackProperty()
    {
        // ... (sin cambios)
        $currentDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
        return $currentDate->gt(now()->startOfMonth());
    }
 
 
    protected function getDatesForMonth(int $month, int $year): Collection
    {
        // ... (sin cambios)
        $startOfMonth = Carbon::create($year, $month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
        $startOfWeek = $startOfMonth->dayOfWeekIso;
 
        $days = collect();
 
        for ($i = 1; $i < $startOfWeek; $i++) {
            $days->push(['date' => null, 'day_of_month' => null]);
        }
 
        $today = now()->startOfDay();
 
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day)->startOfDay();
            $dateString = $date->toDateString();
 
            $isPast = $date->lt($today);
            $isUnavailable = isset($this->unavailableDates[$dateString]);
            $isMaintenance = isset($this->maintenanceDatesLookup[$dateString]);
 
            $price = $this->priceCalculator->getPriceForDate($this->campervan, $date);
 
            $days->push([
                'date' => $dateString,
                'day_of_month' => $day,
                'is_today' => $date->isToday(),
                'is_disabled' => $isPast || $isUnavailable,
                'is_unavailable' => $isUnavailable,
                'is_maintenance' => $isMaintenance,
                'price' => $price,
            ]);
        }
 
        return $days;
    }


    public function render()
    {
        $currentMonthDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $nextMonthDate = $currentMonthDate->copy()->addMonth();

        $dayNames = collect(range(0, 6))->map(function ($day) {
            $date = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays($day);
            if ($date->dayOfWeekIso === 3) {
                return 'mie.';
            }
            return $date->locale('es')->translatedFormat('D');
        });

        // ==========================================================
        // AÑADIDO (RF12.2 - Marketing)
        // ==========================================================
        // Obtenemos todos los tramos de descuento y los pasamos a JSON
        $allDiscountTiers = DurationDiscount::orderBy('min_nights', 'asc')->get();
        // ==========================================================

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
            'dayNames' => $dayNames,

            'unavailableDatesJson' => $this->unavailableDatesJson,
            'maintenanceDatesJson' => $this->maintenanceDatesJson,
            
            // Pasamos la lista de descuentos a la vista
            'allDiscountTiersJson' => $allDiscountTiers->toJson(), // <-- AÑADIDO
        ]);
    }
}