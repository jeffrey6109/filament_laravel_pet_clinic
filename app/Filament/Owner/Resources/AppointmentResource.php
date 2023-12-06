<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources\AppointmentResource\Pages;
use App\Models\Pet;
use App\Models\User;

use App\Support\AvatarOptions;
use App\Models\Appointment;
use App\Models\Slot;
use App\Models\Role;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use App\Enums\AppointmentStatus;


class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
