<?php

namespace App\Filament\Resources\CampervanResource\Pages;

use App\Filament\Resources\CampervanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCampervans extends ListRecords
{
    protected static string $resource = CampervanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
