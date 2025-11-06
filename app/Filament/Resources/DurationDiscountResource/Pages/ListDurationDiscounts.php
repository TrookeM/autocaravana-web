<?php

namespace App\Filament\Resources\DurationDiscountResource\Pages;

use App\Filament\Resources\DurationDiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDurationDiscounts extends ListRecords
{
    protected static string $resource = DurationDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
