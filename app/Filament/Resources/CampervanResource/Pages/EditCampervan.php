<?php

namespace App\Filament\Resources\CampervanResource\Pages;

use App\Filament\Resources\CampervanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

// --- ¡LÍNEAS AÑADIDAS! ---
use App\Filament\Resources\CampervanResource\Widgets\CampervanStatsWidget;

class EditCampervan extends EditRecord
{
    protected static string $resource = CampervanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * ==========================================================
     * ¡ESTE ES EL MÉTODO CORRECTO PARA AÑADIR EL WIDGET!
     * ==========================================================
     */
    protected function getHeaderWidgets(): array
    {
        return [
            CampervanStatsWidget::class,
        ];
    }
}