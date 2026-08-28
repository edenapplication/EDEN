<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'is_new'];

    protected $casts = [
        'is_new' => 'boolean',
    ];

    // ═══ RELATIONS ═══

    public function lots()
    {
        return $this->hasMany(Lot::class);
    }

    public function dossiers()
    {
        return $this->hasMany(DossierClient::class);
    }

    // Compatibilité ancienne relation
    public function dossierClient()
    {
        return $this->hasOne(DossierClient::class);
    }

    public function paiements()
    {
        return $this->hasManyThrough(PaiementDossier::class, DossierClient::class);
    }

    public function visites()
    {
        return $this->hasMany(Visite::class, 'client_id');
    }

    // ═══ MÉTHODES POUR LE STATUT "NOUVEAU" ═══

    public function isNew(): bool
    {
        return (bool) $this->is_new;
    }

    public function markAsConfirmed(): self
    {
        $this->is_new = false;
        $this->save();
        return $this;
    }

    public function markAsNew(): self
    {
        $this->is_new = true;
        $this->save();
        return $this;
    }

    public function toggleNew(): bool
    {
        $this->is_new = !$this->is_new;
        $this->save();
        return $this->is_new;
    }

    // ═══ ACCESSOIRS ═══

    public function getIsNewLabelAttribute(): string
    {
        return $this->is_new ? '🆕 Nouveau' : '✅ Confirmé';
    }

    public function getIsNewBadgeAttribute(): string
    {
        if ($this->is_new) {
            return '<span class="badge badge-new">🆕 Nouveau</span>';
        }
        return '<span class="badge badge-secondary">Ancien</span>';
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->is_new) {
            return '<span class="badge badge-new">🆕 Nouveau client</span>';
        }
        return '<span class="badge badge-secondary">Client existant</span>';
    }

    // ═══ SCOPES ═══

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeNotNew($query)
    {
        return $query->where('is_new', false);
    }
}