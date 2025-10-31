<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Booking;
use Carbon\Carbon;

class BookingsChart extends ChartWidget
{
    protected static ?string $heading = null; 
    protected static ?int $sort = 3; 
    
    // Propiedad para guardar el año
    public ?string $selectedYear;
    
    // Escucha el evento
    protected $listeners = ['yearChanged' => 'updateYear'];

    public function mount(): void
    {
        $this->selectedYear = (string) now()->year;
    }

    // Actualiza el año y fuerza la recarga del gráfico
    public function updateYear(string $year): void
    {
        $this->selectedYear = $year;
        $this->updateChartData(); 
    }

    // Título dinámico
    public function getHeading(): string // <-- Corregido a public
    {
        if ($this->selectedYear === 'all') {
            return 'Ingresos por Mes (Selecciona un año específico para ver)';
        }
        return 'Ingresos por Mes (' . $this->selectedYear . ')';
    }

    protected function getData(): array
    {
        if ($this->selectedYear === 'all') {
            return [
                'datasets' => [['label' => 'Ingresos', 'data' => []]],
                'labels' => []
            ];
        }

        $currentYear = (int) $this->selectedYear;
        
        $data = Booking::whereIn('status', ['confirmed', 'completed'])
            ->whereYear('start_date', $currentYear) 
            ->orderBy('start_date')
            ->get()
            ->groupBy(function ($booking) {
                return Carbon::parse($booking->start_date)->format('n');
            });

        $labels = [];
        $values = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::createFromDate($currentYear, $i, 1)->translatedFormat('M Y');
            $labels[] = $monthName;
            $values[] = $data->get($i, collect())->sum('total_price');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos por Mes',
                    'data' => $values,
                    'backgroundColor' => 'rgba(22, 163, 74, 0.5)',
                    'borderColor' => 'rgba(22, 163, 74, 1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}