<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function pet(): BelongsToMany
    {
        return $this->belongsToMany(Pet::class);
    }

    public function appointment(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class);
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class);
    }
}
