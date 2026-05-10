<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'admin_role_id',
    'first_name',
    'last_name',
    'username',
    'gender',
    'phone_country_code',
    'phone_number',
    'birth_date',
    'social_url',
    'bio',
    'google_id',
    'avatar',
    'point_balance',
    'lifetime_points',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function adminRole(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class);
    }

    public function pointHistories(): HasMany
    {
        return $this->hasMany(PointHistory::class);
    }

    public function canAccessAdminPanel(): bool
    {
        return strtolower((string) $this->role) === 'admin' || !is_null($this->admin_role_id);
    }

    public function hasAdminPermission(string $permission): bool
    {
        if (strtolower((string) $this->role) === 'admin') {
            return true;
        }

        $permissions = $this->adminRole?->permissions ?? [];

        return in_array($permission, $permissions, true);
    }
}
