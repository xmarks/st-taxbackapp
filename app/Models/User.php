<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    const ROLE_SUPER_ADMIN = 0;
    const ROLE_ADMIN = 10;
    const ROLE_MODERATOR = 50;
    const ROLE_REGISTERED = 100;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    public function isRegistered(): bool
    {
        return $this->role === self::ROLE_REGISTERED;
    }

    public function hasRole(int $role): bool
    {
        return $this->role === $role;
    }

    public function hasMinimumRole(int $role): bool
    {
        return $this->role <= $role;
    }

    public function getRoleName(): string
    {
        return match($this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MODERATOR => 'Moderator',
            self::ROLE_REGISTERED => 'Registered',
            default => 'Unknown'
        };
    }

    public static function getRoleOptions(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MODERATOR => 'Moderator',
            self::ROLE_REGISTERED => 'Registered',
        ];
    }

    public function scannedReceipts(): HasMany
    {
        return $this->hasMany(ScannedReceipt::class);
    }

    public function getTotalVatAmount(): float
    {
        return $this->scannedReceipts()->sum('total_vat_amount');
    }

    public function getReceiptCount(): int
    {
        return $this->scannedReceipts()->count();
    }
}
