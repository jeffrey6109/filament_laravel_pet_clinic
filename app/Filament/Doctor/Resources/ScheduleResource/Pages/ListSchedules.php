<?php

namespace App\Filament\Doctor\Resources\ScheduleResource\Pages;

use App\Enums\DaysOfTheWeek;
use App\Filament\Doctor\Resources\ScheduleResource;
use App\Models\Schedule;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Sunday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Sunday))
                ->badge(Schedule::query()->where('day_of_week', DaysOfTheWeek::Sunday)->count() ? : null),

            'Monday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Monday))
                ->badge(Schedule::query()->where('day_of_week', DaysOfTheWeek::Monday)->count()? : null),

            'Tuesday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Tuesday))
                ->badge(Schedule::query()->where('day_of_week', DaysOfTheWeek::Tuesday)->count()? : null),

            'Wednesday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Wednesday))
                ->badge(Schedule::query()->where('day_of_week', DaysOfTheWeek::Wednesday)->count()? : null),

            'Thursday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Thursday))
                ->badge(Schedule::query()->where('day_of_week', DaysOfTheWeek::Thursday)->count()? : null),

            'Friday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Friday))
                ->badge(Schedule::query()->where('day_of_week', DaysOfTheWeek::Friday)->count()? : null),

            'Saturday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Saturday))
                ->badge(Schedule::query()->where('day_of_week', DaysOfTheWeek::Saturday)->count()? : null),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return Carbon::today()->format('l');
    }
}
