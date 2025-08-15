<?php

namespace App\Filament\Resources\BarbeiroResource\Pages;

use App\Filament\Resources\BarbeiroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarbeiro extends EditRecord
{
    protected static string $resource = BarbeiroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
