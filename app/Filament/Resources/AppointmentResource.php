<?php

namespace App\Filament\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Select::make('pet_id')
                        ->relationship('pet', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('status')
                        ->native(false)
                        ->options(AppointmentStatus::class)
                        ->visibleOn(
                            pages\EditAppointment::class
                        ),
                    DatePicker::make('date')
                        ->native(false)
                        ->required(true),
                    TimePicker::make('start')
                        ->required()
                        ->seconds(false)
                        ->displayFormat('h:i A')
                        ->minutesStep(10),
                    TimePicker::make('end')
                        ->required()
                        ->seconds(false)
                        ->displayFormat('h:i A')
                        ->minutesStep(10),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pet.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('start')
                    ->time('h:i A')
                    ->label('From')
                    ->sortable(),
                TextColumn::make('end')
                    ->time('h:i A')
                    ->label('To')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('primary'),

                    Tables\Actions\Action::make('Confirm')
                        ->action(function (Appointment $record) {
                            $record->status = AppointmentStatus::Confirmed;
                            $record->save();
                        })
                        ->visible(fn (Appointment $record) => $record->status == AppointmentStatus::Created)
                        ->color('success')
                        ->icon('heroicon-o-check'),

                    Tables\Actions\Action::make('Cancelled')
                        ->action(function (Appointment $record) {
                            $record->status = AppointmentStatus::Cancelled;
                            $record->save();
                        })
                        ->visible(fn (Appointment $record) => $record->status !== AppointmentStatus::Cancelled)
                        ->color('danger')
                        ->icon('heroicon-o-x-mark'),

                    Tables\Actions\EditAction::make()
                        ->color('warning'),
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
