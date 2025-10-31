<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Booking;     // <-- Importante
use App\Models\Maintenance; // <-- Importante

class GlobalStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // --- 1. Calcular Ingresos Totales ---
        // Suma el 'total_price' de todas las reservas 'confirmed'
        $totalRevenue = Booking::where('status', 'confirmed') 
            ->sum('total_price');

        // --- 2. Calcular Gastos Totales ---
        // Suma el 'cost' de todos los mantenimientos
        $totalCosts = Maintenance::sum('cost');
        
        // --- 3. Calcular Beneficio Neto ---
        $netProfit = $totalRevenue - $totalCosts;

        // --- 4. Contar Reservas ---
        $totalBookings = Booking::where('status', 'confirmed')->count();

        // --- 5. Devolver las Tarjetas (Stats) ---
        return [
            Stat::make('Ingresos Totales (Global)', number_format($totalRevenue, 2) . ' €')
                ->description('Suma de todas las reservas confirmadas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('Gastos Totales (Global)', number_format($totalCosts, 2) . ' €')
                ->description('Suma de todos los mantenimientos')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            
            Stat::make('Beneficio Neto (Global)', number_format($netProfit, 2) . ' €')
                ->description('Ingresos - Gastos')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color($netProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Reservas Confirmadas', $totalBookings)
                ->description('Número total de reservas completadas')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
        ];
    }
}