<?php

namespace App\Filament\Resources\CampervanResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model; // <-- Importante
use App\Models\Booking; // <-- Importante

class CampervanStatsWidget extends BaseWidget
{
    // Hacemos que el widget acepte el registro actual (la caravana)
    public ?Model $record = null;

    // Ocultamos el widget en la página principal del listado
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Si no estamos en un registro (ej. en la página de 'crear'), no mostramos nada.
        if (!$this->record) {
            return [];
        }

        // --- 1. Calcular Ingresos Totales ---
        // Suma el 'total_price' de todas las reservas 'confirmed'
        $totalRevenue = $this->record->bookings()
            ->where('status', 'confirmed') 
            ->sum('total_price');

        // --- 2. Calcular Gastos Totales ---
        // Suma el 'cost' de todos los mantenimientos
        $totalCosts = $this->record->maintenances()
            ->sum('cost');
        
        // --- 3. Calcular Beneficio Neto ---
        $netProfit = $totalRevenue - $totalCosts;

        // --- 4. Devolver las Tarjetas (Stats) ---
        return [
            Stat::make('Ingresos Totales (Reservas)', number_format($totalRevenue, 2) . ' €')
                ->description('Suma de todas las reservas confirmadas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('Gastos Totales (Mantenimiento)', number_format($totalCosts, 2) . ' €')
                ->description('Suma de todos los costes de mantenimiento')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            
            Stat::make('Beneficio Neto', number_format($netProfit, 2) . ' €')
                ->description('Ingresos Totales - Gastos Totales')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color($netProfit >= 0 ? 'success' : 'danger'),
        ];
    }

    /**
     * Define dónde debe mostrarse este widget.
     * Lo queremos en la página de Edición.
     */
    public static function canViewOnRecordPage(Model $record): bool
    {
        return true; 
    }
}