<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    //use HasFactory<\Database\Factories\UserFactory>
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_banned',
        'reputation',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
        ];
    }

    // Relations

    public function colocations(): BelongsToMany
    {
        return $this->belongsToMany(Colocation::class, 'colocation_user')
            ->withPivot('role', 'joined_at', 'left_at')
            ->withTimestamps();
    }

    public function ownedColocations(): HasMany
    {
        return $this->hasMany(Colocation::class, 'owner_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'paid_by');
    }

    public function settlementsOwed(): HasMany
    {
        return $this->hasMany(Settlement::class, 'from_user_id');
    }

    public function settlementsReceivable(): HasMany
    {
        return $this->hasMany(Settlement::class, 'to_user_id');
    }

    //Helpers

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    //Get the user's active colocation (not left, not cancelled).
    public function activeColocation(): ?Colocation
    {
        return $this->colocations()
            ->wherePivot('left_at', null)
            ->where('status', 'active')
            ->first();
    }

    public function hasActiveColocation(): bool
    {
        return $this->activeColocation() !== null;
    }
}
