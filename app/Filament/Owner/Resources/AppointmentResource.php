<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Support\Enums\Alignment;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    Stack::make([
                        ImageColumn::make('pet.avatar')
                            ->size(50)
                            ->circular()
                            ->grow(false),

                        TextColumn::make('pet.name')
                            ->searchable()
                            ->sortable()
                            ->grow(false),
                    ])
                    ->space(1)
                    ->alignment(Alignment::Center),

                    TextColumn::make('status')
                        ->searchable()
                        ->sortable()
                        ->badge(),

                    TextColumn::make('description')
                        ->searchable(),

                    TextColumn::make('doctor.name')
                        ->label('Doctor')
                        ->searchable()
                        ->sortable(),

                    TextColumn::make('clinic.name')
                        ->label('Clinic')
                        ->searchable()
                        ->sortable(),

                    TextColumn::make('date')
                        ->label('Appointment Date')
                        ->date('M d, Y')
                        ->sortable(),

                    TextColumn::make('slot.formattedTime')
                        ->label('Appointment Time')
                        ->badge()
                        ->sortable(),
                ])
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
