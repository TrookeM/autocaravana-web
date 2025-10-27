<?php

namespace App\Filament\Resources\BlockingResource\Pages;

use App\Filament\Resources\BlockingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlocking extends EditRecord
{
    protected static string $resource = BlockingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
