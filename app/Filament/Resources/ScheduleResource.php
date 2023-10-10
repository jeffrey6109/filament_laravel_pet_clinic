<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Slot;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $doctorRole = Role::whereName('doctor')->first();
        return $form
            ->schema([
                Section::make([
                    Forms\Components\DatePicker::make('date')
                        ->native(false)
                        ->required(),
                    Forms\Components\Select::make('owner_id')
                        ->options(
                            Filament::getTenant()
                                ->users()
                                ->whereBelongsTo($doctorRole)
                                ->get()
                                ->pluck('name', 'id')
                        )
                        ->native(false)
                        ->required(),
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
            ->groups([
                Tables\Grouping\Group::make('date')
                    ->collapsible(),
            ])
            ->defaultGroup('date')
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
                Tables\Columns\TextColumn::make('slots.start')
                    ->label('Appointment Time')
                    ->badge()
                    ->formatStateUsing(fn (Carbon $state) => $state->format('h:i A')),
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
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->before(fn (Schedule $record) => $record->slots()->delete()),
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
