<?php

namespace App\Models\RH;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Notification extends Model
{
    protected $table = 'rh_notifications';

    protected $fillable = [
        'user_id',
        'alerte_id',
        'canal',
        'sujet',
        'message',
        'envoyee',
        'date_envoi',
        'erreur',
    ];

    protected $casts = [
        'envoyee' => 'boolean',
        'date_envoi' => 'datetime',
    ];

    // ===== CONSTANTES =====
    const CANAUX = ['email', 'interface', 'sms'];

    // ===== RELATIONS =====
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alerte()
    {
        return $this->belongsTo(Alerte::class);
    }

    // ===== SCOPES =====
    public function scopeNonEnvoyee($query)
    {
        return $query->where('envoyee', false);
    }

    public function scopeEnvoyee($query)
    {
        return $query->where('envoyee', true);
    }

    public function scopeCanal($query, $canal)
    {
        return $query->where('canal', $canal);
    }

    // ===== MÉTHODES =====
    public function marquerEnvoyee(): void
    {
        $this->update([
            'envoyee' => true,
            'date_envoi' => now(),
        ]);
    }

    public function enregistrerErreur(string $erreur): void
    {
        $this->update(['erreur' => $erreur]);
    }
}