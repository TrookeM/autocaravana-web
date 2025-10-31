<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Booking;
use App\Models\Maintenance;
use Filament\Widgets\Concerns\InteractsWithPageFilters; 

class GlobalStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2; // Después del filtro

    // Propiedad pública (debe coincidir con el default del filtro)
    public ?string $selectedYear;
    
    // Escucha el evento 'yearChanged'
    protected $listeners = ['yearChanged' => 'updateYear'];

    // Se ejecuta al cargar
    public function mount(): void
    {
        $this->selectedYear = (string) now()->year;
    }

    // Actualiza la propiedad cuando el filtro cambia
    public function updateYear(string $year): void
    {
        $this->selectedYear = $year;
    }
    
    protected function getStats(): array
    {
        $yearLabel = $this->selectedYear === 'all' ? 'Global' : $this->selectedYear;
        
        $revenueQuery = Booking::whereIn('status', ['confirmed', 'completed']);
        $bookingsQuery = Booking::whereIn('status', ['confirmed', 'completed']);

        if ($this->selectedYear !== 'all') {
            $revenueQuery->whereYear('start_date', $this->selectedYear);
            $bookingsQuery->whereYear('start_date', $this->selectedYear);
        }

        $totalRevenue = $revenueQuery->sum('total_price');
        $totalBookings = $bookingsQuery->count();
        $totalCosts = Maintenance::sum('cost');
        $netProfit = $totalRevenue - $totalCosts;

        return [
            Stat::make('Ingresos (' . $yearLabel . ')', number_format($totalRevenue, 2) . ' €')
                ->description('Reservas en ' . $yearLabel)
                ->color('success'),
            
            Stat::make('Reservas (' . $yearLabel . ')', $totalBookings)
                ->description('Reservas en ' . $yearLabel)
                ->color('info'),

            Stat::make('Gastos (Histórico)', number_format($totalCosts, 2) . ' €')
                ->description('Todos los mantenimientos')
                ->color('danger'),
            
            Stat::make('Beneficio (' . $yearLabel . ')', number_format($netProfit, 2) . ' €')
                ->description('Ingresos ' . $yearLabel . ' - Gastos Históricos')
                ->color($netProfit >= 0 ? 'success' : 'danger'),
        ];
    }
}