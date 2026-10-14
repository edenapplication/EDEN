<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Auditable;

class User extends Authenticatable
{
    use Auditable;
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'actif', 'reference'];
    protected $hidden   = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'actif'             => 'boolean',
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // ✅ MÉTHODES DE RÔLE
    // ═══════════════════════════════════════════════════════════════
    public function isAdmin()      { return $this->role === 'admin'; }
    public function isRH()         { return $this->role === 'rh'; }
    public function isCommercial() { return $this->role === 'commercial'; }
    public function isGeometre()   { return $this->role === 'geometre'; }
    public function isComptable()  { return $this->role === 'comptable'; }  // ✅ AJOUTÉ

    // ═══════════════════════════════════════════════════════════════
    // ✅ ACCÈS AUX MODULES
    // ═══════════════════════════════════════════════════════════════
    public function canAccess(string $module): bool
    {
        return match($this->role) {
            'admin'      => true,
            'rh'         => in_array($module, ['rh']),
            'commercial' => in_array($module, ['commercial', 'suivi', 'visites']),
            'geometre'   => in_array($module, ['affectation-rapide', 'affectations', 'suivi', 'visites']),
            'comptable'  => in_array($module, ['comptable', 'paiement-technique', 'rapport-technique']),  // ✅ AJOUTÉ
            default      => false,
        };
    }

    // ═══════════════════════════════════════════════════════════════
    // ✅ LABEL DU RÔLE (pour l'affichage)
    // ═══════════════════════════════════════════════════════════════
    public function getRoleLabel(): string
    {
        return match($this->role) {
            'admin'      => '👑 Administrateur',
            'rh'         => '👥 RH',
            'commercial' => '💼 Commercial',
            'geometre'   => '📐 Géomètre',
            'comptable'  => '💰 Comptable',  // ✅ AJOUTÉ
            default      => 'Utilisateur',
        };
    }

    // ═══════════════════════════════════════════════════════════════
    // ✅ COULEUR DU RÔLE (pour l'affichage)
    // ═══════════════════════════════════════════════════════════════
    public function getRoleColor(): string
    {
        return match($this->role) {
            'admin'      => '#7c3aed',
            'rh'         => '#0891b2',
            'commercial' => '#16a34a',
            'geometre'   => '#ea580c',
            'comptable'  => '#dc2626',  // ✅ AJOUTÉ
            default      => '#64748b',
        };
    }
}