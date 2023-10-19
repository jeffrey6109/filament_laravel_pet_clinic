<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $formatted_time
 */
class Slot extends Model
{
    use HasFactory;

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    protected $fillable = [
        'start','end'
    ];

    /**
     * @return Attribute<string, never>
     */
    protected function formattedTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attribute) =>
                Carbon::parse($attribute['start'])->format('h:i A') . ' - ' .
                Carbon::parse($attribute['end'])->format('h:i A')
        );
    }

    public function scopeAvailableFor(Builder $query, User $doctor, int $dayOfTheWeek, int $clinicId): void
    {
        $query->whereHas('schedule', function (Builder $query) use ($doctor, $dayOfTheWeek, $clinicId) {
            $query
                ->where('clinic_id', $clinicId)
                ->where('day_of_week', $dayOfTheWeek)
                ->whereBelongsTo($doctor, 'owner');
        });
    }

    public function appointment(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
