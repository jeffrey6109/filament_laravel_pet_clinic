<?php

namespace App\Models;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    use HasFactory;

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    public function formattedTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attribute) => Carbon::parse($attribute['start'])->format('h:i A') . ' - ' .
            Carbon::parse($attribute['end'])->format('h:i A')
        );
    }

    protected $fillable = [
        'start','end'
    ];

    public function appointment(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
