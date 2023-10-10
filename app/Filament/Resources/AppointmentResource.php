<?php

namespace App\Filament\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Role;
use App\Models\Slot;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        $doctorRole = Role::whereName('doctor')->first();
        return $form
            ->schema([
                Section::make([
                    Select::make('pet_id')
                        ->relationship('pet', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    DatePicker::make('date')
                        ->native(false)
                        ->required()
                        ->live(),
                    Select::make('doctor_id')
                        ->options(function (Get $get) use ($doctorRole) {
                            return Filament::getTenant()
                                ->users()
                                ->whereBelongsTo($doctorRole)
                                ->whereHas('schedules', function (Builder $query) use ($get) {
                                    $query->where('date', $get('date'));
                                })
                                ->get()
                                ->pluck('name', 'id');
                        })
                        ->native(false)
                        ->hidden(fn (Get $get) => blank($get('date'))),
                    Select::make('slot_id')
                        ->native(false)
                        ->relationship(name: 'slot', titleAttribute: 'start')
                        ->getOptionLAbelFromRecordUsing(fn (Slot $record) => $record->start->format('h:i A')),
                    Select::make('status')
                        ->native(false)
                        ->options(AppointmentStatus::class)
                        ->visibleOn(
                            pages\EditAppointment::class
                        ),
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
