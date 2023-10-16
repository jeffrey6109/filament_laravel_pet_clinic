<?php

namespace App\Filament\Resources;

use App\Enums\DaysOfTheWeek;
use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $doctorRole = Role::whereName('doctor')->first();
        return $form
            ->schema([
                Section::make([
                    Forms\Components\DatePicker::make('date')
                        ->native(false)
                        ->closeOnDateSelection()
                        ->required(),

                    Forms\Components\Select::make('clinic_id')
                        ->relationship('clinic', 'name')
                        ->preload()
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('owner_id', null)),

                    Forms\Components\Select::make('owner_id')
                        ->label('Doctor')
                        ->options(function (Get $get) use ($doctorRole): array|Collection {
                            return Clinic::find($get('clinic_id'))
                                ?->users()
                                ->whereBelongsTo($doctorRole)
                                ->get()
                                ->pluck('name', 'id')?? [];
                        })
                        ->native(false)
                        ->required()
                        ->live(),

                    Forms\Components\Select::make('day_of_week')
                        ->options(DaysOfTheWeek::class)
                        ->native(false),

                    Forms\Components\Repeater::make('slots')
                            ->relationship()
                            ->ColumnSpanFull()
                            ->schema([
                                Forms\Components\TimePicker::make('start')
                                    ->seconds(false)
                                    ->required(),
                                Forms\Components\TimePicker::make('end')
                                    ->seconds(false)
                                    ->required(),
                            ])->columns(2)
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup(
                Tables\Grouping\Group::make('clinic.name')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
            )
            ->groupsInDropdownOnDesktop()
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Doctor')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('day_of_week')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slots')
                    ->label('Schedules Time')
                    ->badge()
                    ->formatStateUsing(fn (Slot $state) => $state->formattedTime),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->before(fn (Schedule $record) => $record->slots()->delete()),
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
