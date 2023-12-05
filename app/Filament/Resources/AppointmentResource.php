<?php

namespace App\Filament\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Role;
use App\Models\Slot;
use App\Models\User;
use App\Models\Pet;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Support\AvatarOptions;
use Illuminate\Support\HtmlString;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $doctorRole = Role::whereName('doctor')->first();
        return $form
            ->schema([
                Section::make([
                    Select::make('pet_id')
                        ->label('Pet')
                        ->allowHtml()
                        ->required()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull()
                        ->getSearchResultsUsing(function (string $search) {
                            $pets = Pet::where('name', 'like', "%{$search}%")->limit(50)->get();

                            return $pets->mapWithKeys(function ($pet) {
                                return [$pet->getKey() => AvatarOptions::getOptionString($pet)];
                            })->toArray();
                        })
                        ->options(function (): array {
                            $pets = Pet::all();

                            return $pets->mapWithKeys(function ($pet) {
                                return [$pet->getKey() => AvatarOptions::getOptionString($pet)];
                            })->toArray();
                        }),

                    Select::make('clinic_id')
                        ->relationship('clinic', 'name')
                        ->preload()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            $set('date', null);
                            $set('doctor', null);
                        }),

                    DatePicker::make('date')
                        ->native(false)
                        ->displayFormat('M d, Y')
                        ->closeOnDateSelection()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('doctor_id', null))
                        ->hidden(fn (Get $get) => blank($get('clinic_id'))),

                    Select::make('doctor_id')
                        ->label('Doctor')
                        ->searchable()
                        ->allowHtml()
                        ->options(function (Get $get) use ($doctorRole) {
                            $doctors = User::whereBelongsTo($doctorRole)
                                ->whereHas('schedules', function (Builder $query) use ($get) {
                                    $dayOfTheWeek = Carbon::parse($get('date'))->dayOfWeek;
                                    $query
                                        ->where('day_of_week', $dayOfTheWeek)
                                        ->where('clinic_id', $get('clinic_id'));
                                })->get();

                            return $doctors->mapWithKeys(function ($doctor) {
                                return [$doctor->getKey() => AvatarOptions::getOptionString($doctor)];
                            })->toArray();
                        })
                        ->live()
                        ->native(false)
                        ->required()
                        ->afterStateUpdated(fn (Set $set) => $set('slot_id', null))
                        ->hidden(fn (Get $get) => blank($get('date')))
                        ->helperText(function (Select $component) {
                            if(! $component->getOptions()) {
                                return new HtmlString(
                                    '<span class="text-sm text-danger-600 dark:text-danger-400">
                                        No Doctors available. Please select a different Clinic or Date
                                    </span>'
                                );
                            }

                            return '';
                        }),

                    Select::make('slot_id')
                        ->native(false)
                        ->label('Slot')
                        ->required()
                        ->options(function (Get $get) {
                            $doctor = User::find($get('doctor_id'));
                            $dayOfTheWeek = Carbon::parse($get('date'))->dayOfWeek;
                            $clinicId = $get('clinic_id');

                            return $clinicId ? Slot::availableFor($doctor, $dayOfTheWeek, $clinicId)->get()->pluck('formattedTime', 'id') : [];
                        })
                        ->hidden(fn (Get $get) => blank($get('doctor_id')))
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
        $doctorRole = Role::whereName('doctor')->first();

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
                SelectFilter::make('clinics')
                    ->relationship('clinic', 'name')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('doctors')
                    //ToDo: rework the $query into a private function
                    ->relationship('doctor', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('role_id', $doctorRole->id))
                    ->multiple()
                    ->preload(),

                SelectFilter::make('status')
                    ->options(AppointmentStatus::class),
            ]) ->filtersFormColumns(2)
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::new()->count();
    }
}
