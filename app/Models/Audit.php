<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Audit extends Model
{
    protected $table = 'audits';

    protected $fillable = [
        'user_id', 'user_name', 'user_role',
        'event', 'module',
        'auditable_type', 'auditable_id', 'auditable_label',
        'description', 'old_values', 'new_values', 'metadata',
        'url', 'method', 'ip', 'user_agent',
        'severity', 'tags',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════════════════
    // RELATIONS
    // ═══════════════════════════════════════════════════════
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // ═══════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    // ═══════════════════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════════════════
    public function getIconeAttribute(): string
    {
        return match ($this->event) {
            'created'       => '🆕',
            'updated'       => '✏️',
            'deleted'       => '🗑️',
            'login'         => '🔑',
            'logout'        => '🚪',
            'login_failed'  => '⚠️',
            'login_blocked' => '🚫',
            'pdf_genere'    => '📄',
            'rapport_genere'=> '📊',
            'accepted'      => '✅',
            'refused'       => '❌',
            'exported'      => '📤',
            'imported'      => '📥',
            'http_post'     => '🌐',
            'http_put'      => '🌐',
            'http_patch'    => '🌐',
            'http_delete'   => '🌐',
            default         => '📝',
        };
    }

    public function getCouleurAttribute(): string
    {
        return match ($this->severity) {
            'critical' => '#dc2626',
            'warning'  => '#f59e0b',
            default    => match ($this->event) {
                'created', 'accepted' => '#16a34a',
                'deleted', 'refused'  => '#dc2626',
                'updated'             => '#f59e0b',
                default               => '#1d4ed8',
            },
        };
    }

    public function getChampsModifiesAttribute(): array
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];

        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;

            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'avant' => $oldValue,
                    'apres' => $newValue,
                ];
            }
        }

        return $changes;
    }
}