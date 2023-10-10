<?php

namespace App\Models;

use App\Enums\PetSpecies;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pet extends Model
{
    use HasFactory;

    protected $casts = [
        'species' => PetSpecies::class
    ];

    protected $fillable = [
        'name','date_of_birth','species','type', 'owner_id', 'avatar'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function appointment(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function clinic(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class);
    }
}