<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'actif','reference'];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'actif'             => 'boolean',
        ];
    }

    public function isAdmin()      { return $this->role === 'admin'; }
    public function isRH()         { return $this->role === 'rh'; }
    public function isCommercial() { return $this->role === 'commercial'; }

    public function canAccess(string $module): bool
    {
        return match($this->role) {
            'admin'      => true,
            'rh'         => in_array($module, ['rh']),
            'commercial' => in_array($module, ['commercial', 'suivi', 'visites']),
            default      => false,
        };
    }
}