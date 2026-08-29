<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Alerte extends Model
{
    protected $table = 'rh_alertes';

    protected $fillable = [
        'employe_id',
        'user_id',
        'type',
        'titre',
        'message',
        'lien',
        'data',
        'statut',
        'priorite',
        'date_lecture',
        'date_traitement',
        'date_expiration',
    ];

    protected $casts = [
        'data' => 'array',
        'date_lecture' => 'datetime',
        'date_traitement' => 'datetime',
        'date_expiration' => 'datetime',
    ];

    // ===== CONSTANTES =====
    const TYPES = [
        'contrat' => 'Contrat',
        'paie' => 'Paie',
        'cnps' => 'CNPS',
        'absence' => 'Absence',
        'conge' => 'Congé',
        'retard' => 'Retard',
        'sanction' => 'Sanction',
        'depart' => 'Départ',
        'document' => 'Document',
        'visite_medicale' => 'Visite médicale',
    ];

    const STATUTS = [
        'non_lu' => 'Non lu',
        'lu' => 'Lu',
        'traite' => 'Traité',
        'ignore' => 'Ignoré',
    ];

    const PRIORITES = [
        'basse' => 'Basse',
        'normale' => 'Normale',
        'haute' => 'Haute',
        'critique' => 'Critique',
    ];

    const PRIORITES_COLORS = [
        'basse' => '#94a3b8',
        'normale' => '#3b82f6',
        'haute' => '#f59e0b',
        'critique' => '#dc2626',
    ];

    // ===== RELATIONS =====
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'alerte_id');
    }

    // ===== SCOPES =====
    public function scopeNonLu($query)
    {
        return $query->where('statut', 'non_lu');
    }

    public function scopeLu($query)
    {
        return $query->where('statut', 'lu');
    }

    public function scopeTraite($query)
    {
        return $query->where('statut', 'traite');
    }

    public function scopeActives($query)
    {
        return $query->whereIn('statut', ['non_lu', 'lu']);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePriorite($query, $priorite)
    {
        return $query->where('priorite', $priorite);
    }

    // ===== ACCESSORS =====
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getPrioriteLabelAttribute(): string
    {
        return self::PRIORITES[$this->priorite] ?? $this->priorite;
    }

    public function getPrioriteColorAttribute(): string
    {
        return self::PRIORITES_COLORS[$this->priorite] ?? '#94a3b8';
    }

    // ===== MÉTHODES =====
    public function marquerLu(): void
    {
        $this->update([
            'statut' => 'lu',
            'date_lecture' => now(),
        ]);
    }

    public function marquerTraite(): void
    {
        $this->update([
            'statut' => 'traite',
            'date_traitement' => now(),
        ]);
    }

    public function marquerIgnore(): void
    {
        $this->update(['statut' => 'ignore']);
    }

    public function notifier($userId, $canal = 'interface'): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'alerte_id' => $this->id,
            'canal' => $canal,
            'sujet' => $this->titre,
            'message' => $this->message,
        ]);
    }

    public static function createAlerte(array $data): self
    {
        return self::create([
            'employe_id' => $data['employe_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'type' => $data['type'],
            'titre' => $data['titre'],
            'message' => $data['message'],
            'lien' => $data['lien'] ?? null,
            'data' => $data['data'] ?? null,
            'priorite' => $data['priorite'] ?? 'normale',
            'date_expiration' => $data['date_expiration'] ?? null,
        ]);
    }
}