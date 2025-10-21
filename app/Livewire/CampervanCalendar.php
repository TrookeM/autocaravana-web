<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Campervan;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CampervanCalendar extends Component
{
    public Campervan $campervan;
    public $currentMonth;
    public $currentYear;
    public array $unavailableDates = [];
    public string $unavailableDatesJson = '[]';

    // Para forzar re-renderizado completo
    public $timestamp;

    public function mount(Campervan $campervan)
    {
        $this->campervan = $campervan;
        
        $today = now();
        $this->currentMonth = $today->month;
        $this->currentYear = $today->year;
        $this->timestamp = now()->timestamp;
        
        $this->loadUnavailableDates();
    }

    public function loadUnavailableDates(): void
    {
        $bookings = $this->campervan->bookings()
            ->where('end_date', '>', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->get();

        $unavailable = [];
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->start_date);
            $end = Carbon::parse($booking->end_date);
            
            $current = $start->copy();
            while ($current->lt($end)) {
                $unavailable[$current->toDateString()] = true;
                $current->addDay();
            }
        }

        $this->unavailableDates = $unavailable;
        $this->unavailableDatesJson = json_encode(array_keys($unavailable));
    }

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
            
            $days->push([
                'date' => $dateString,
                'day_of_month' => $day,
                'is_today' => $date->isToday(),
                'is_disabled' => $isPast || $isUnavailable,
                'is_unavailable' => $isUnavailable,
            ]);
        }

        return $days;
    }

    public function render()
    {
        $currentMonthDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $nextMonthDate = $currentMonthDate->copy()->addMonth();

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
        ]);
    }
}