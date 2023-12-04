<?php

namespace App\Filament\Owner\Resources\PetResource\Pages;

use App\Filament\Owner\Resources\PetResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePet extends CreateRecord
{
    protected static string $resource = PetResource::class;
}
