<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\ScheduleResource\Pages;
use App\Filament\Doctor\Resources\ScheduleResource\RelationManagers;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Slot;
use App\Models\User;
use Filament\Forms;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

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
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (Schedule $record) => $record->date->format('M d, Y')),
            ])
            ->defaultGroup('date')
            ->groupsInDropdownOnDesktop()
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('slots')
                    ->label('Appointment Time')
                    ->badge()
                    ->formatStateUsing(fn (Slot $state) => $state->formatted_time),
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
                    Tables\Actions\EditAction::make(),
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
