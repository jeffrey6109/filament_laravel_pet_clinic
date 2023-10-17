<?php

namespace App\Filament\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Slot;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Illuminate\Support\Carbon;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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

    protected static?int $navigationSort = 1;

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

                    Select::make('clinic_id')
                        ->relationship('clinic', 'name')
                        ->preload()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            // $set('date', null);
                            $set('doctor', null);
                        }),

                    DatePicker::make('date')
                        ->native(false)
                        ->displayFormat('M d, Y')
                        ->closeOnDateSelection()
                        ->required()
                        ->default(fn($record) => $record->date)
                        ->live()
                        ->afterStateHydrated(fn ($record) => $record->date->format('M d, Y'))
                        ->afterStateUpdated(fn (Set $set) => $set('doctor_id', null)),

                    Select::make('doctor')
                        ->options(function (Get $get) use ($doctorRole) {
                            return User::whereBelongsTo($doctorRole)
                                ->whereHas('schedules', function (Builder $query) use ($get) {
                                    $dayOfTheWeek = Carbon::parse($get('date'))->dayOfWeek;
                                    $query
                                        ->where('day_of_week', $dayOfTheWeek)
                                        ->where('clinic_id', $get('clinic_id'));
                                })
                                ->get()
                                ->pluck('name', 'id');
                        })
                        ->live()
                        ->native(false)
                        ->required()
                        ->afterStateUpdated(fn (Set $set) => $set('slot_id', null))
                        ->hidden(fn (Get $get) => blank($get('date'))),

                    Select::make('slot_id')
                        ->native(false)
                        ->label('Slot')
                        ->required()
                        // TODO :move this to the Slot Model
                        // ->options(fn () => Slot::getAvailable())
                        ->relationship(
                            name: 'slot',
                            titleAttribute: 'start',
                            modifyQueryUsing: function (Builder $query, Get $get) {
                                $doctor = User::find($get('doctor'));
                                $dayOfTheWeek = Carbon::parse($get('date'))->dayOfWeek;
                                $query->whereHas('schedule', function (Builder $query) use ($doctor, $dayOfTheWeek, $get) {
                                    $query
                                        ->where('clinic_id', $get('clinic_id'))
                                        ->where('day_of_week', $dayOfTheWeek)
                                        ->whereBelongsTo($doctor, 'owner');
                                });
                            }
                        )
                        ->hidden(fn (Get $get) => blank($get('doctor')))
                        ->getOptionLAbelFromRecordUsing(fn (Slot $record) => $record->formattedTime),

                    Select::make('status')
                        ->native(false)
                        ->options(AppointmentStatus::class)
                        ->visibleOn(
                            pages\EditAppointment::class
                        ),

                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull()
                        ->required(),
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

                TextColumn::make('slot.schedule.owner.name')
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
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
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
                    // Tables\Actions\DeleteAction::make()
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
