<?php

namespace App\Filament\Resources\BlockingResource\Pages;

use App\Filament\Resources\BlockingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlockings extends ListRecords
{
    protected static string $resource = BlockingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
