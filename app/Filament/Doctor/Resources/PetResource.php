<?php

namespace App\Filament\Doctor\Resources;

use App\Enums\PetSpecies;
use App\Filament\Doctor\Resources\PetResource\Pages;
use App\Models\Pet;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PetResource extends Resource
{
    protected static ?string $model = Pet::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?int $navigationSort = 3;

    protected static ?string $tenantOwnershipRelationshipName = 'clinics';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    FileUpload::make('avatar')
                        ->image()
                        ->directory('avatars')
                        ->imageEditor()
                        ->columnSpanFull(),

                    TextInput::make('name')
                        ->required(),

                    DatePicker::make('date_of_birth')
                        ->required()
                        ->native(false)
                        ->closeOnDateSelection()
                        ->displayFormat('d M Y'),

                    Select::make('species')
                        ->label("Pet's species")
                        ->placeholder('e.g. Dog, Cat, Lizard, Tortoise')
                        ->required()
                        ->native(false)
                        ->searchable()
                        ->preload()
                        ->options(PetSpecies::class),

                    TextInput::make('type')
                        ->label("Pet's type")
                        ->placeholder('e.g. Bulldog, Persian cat, Gecko, Sulcata Tortoise')
                        ->required(),

                    Select::make('owner_id')
                        ->relationship('owner', 'name')
                        ->native(false)
                        ->searchable()
                        ->preload(),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->size(50)
                    ->circular(),

                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable(),

                TextColumn::make('species')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('owner.name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->date()
                    ->label('Register on')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('primary'),
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->before(function (Pet $record) {
                            // Delete file
                            Storage::delete('public/' . $record->avatar);
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPets::route('/'),
            'create' => Pages\CreatePet::route('/create'),
            'edit' => Pages\EditPet::route('/{record}/edit'),
        ];
    }
}
