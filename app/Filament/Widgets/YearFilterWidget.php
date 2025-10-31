<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <-- ¡Importante!

class YearFilterWidget extends Widget
{
    // 1. Apunta a la vista Blade
    protected static string $view = 'filament.widgets.year-filter-widget';
    
    // 2. Oculta el fondo de "tarjeta"
    protected static bool $isDiscovered = false; 

    // 3. Propiedad pública (string para aceptar "all")
    public ?string $selectedYear;

    // 4. Se ejecuta al cargar
    public function mount(): void
    {
        // Por defecto, selecciona el AÑO ACTUAL
        $this->selectedYear = (string) now()->year;
    }

    // 5. Opciones para el dropdown (Años)
    public function getYearsProperty(): array
    {
        $firstBookingYear = Booking::query()->orderBy('start_date', 'asc')->value(DB::raw('YEAR(start_date)'));
        
        if (!$firstBookingYear) {
            return [now()->year];
        }
        
        $currentYear = now()->year;
        return range($firstBookingYear, $currentYear);
    }

    // 6. Se ejecuta CADA VEZ que cambias el dropdown
    public function updatedSelectedYear($value): void
    {
        $this->dispatch('yearChanged', year: $value);
    }
}