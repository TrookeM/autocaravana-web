<?php

namespace App\Filament\Resources\DurationDiscountResource\Pages;

use App\Filament\Resources\DurationDiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDurationDiscount extends EditRecord
{
    protected static string $resource = DurationDiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
