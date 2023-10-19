<?php

namespace App\Models;

use App\Enums\DaysOfTheWeek;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $casts = [
        'day_of_week' => DaysOfTheWeek::class
    ];

    protected $fillable = [
      'owner_id','clinic_id','day_of_week'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
