<?php

namespace App\Filament\Doctor\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Doctor\Resources\AppointmentResource\Pages;
use App\Filament\Doctor\Resources\AppointmentResource\RelationManagers\NotesRelationManager;
use App\Models\Appointment;
use App\Models\Role;
use App\Models\Slot;
use App\Models\Pet;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use App\Support\AvatarOptions;


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
                        ->columnSpanFull()
                        ->preload()
                        ->helperText(
                            fn () =>
                            Filament::getTenant()->pets->isEmpty() ? new HtmlString(
                                '<span class="text-sm text-danger-600 dark:text-danger-400">
                                    No pets available for this clinic.
                                </span>'
                            ) : ''
                        )
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

                    DatePicker::make('date')
                        ->native(false)
                        ->displayFormat('M d, Y')
                        ->closeOnDateSelection()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('slot_id', null)),

                    Select::make('slot_id')
                        ->native(false)
                        ->label('Slot')
                        ->required()
                        ->options(function (Get $get) {
                            $clinic = Filament::getTenant();
                            $doctor = Filament::auth()->user()->id;
                            $dayOfTheWeek = Carbon::parse($get('date'))->dayOfWeek;
                            return Slot::availableFor($doctor, $dayOfTheWeek, $clinic->id)->get()->pluck('formattedTime', 'id');
                        })
                        ->helperText(function (Select $component) {
                            if(! $component->getOptions()) {
                                return new HtmlString(
                                    '<span class="text-sm text-danger-600 dark:text-danger-400">
                                        No slots available. Please select a different Date
                                    </span>'
                                );
                            }

                            return '';
                        })
                        ->hidden(fn (Get $get) => blank($get('date'))),

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
            NotesRelationManager::class,
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
        return static::getModel()::new()->count() ;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning' ;
    }
}
