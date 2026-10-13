<?php

namespace App\Traits;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Boot : écoute les événements Eloquent
     */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            AuditService::created($model);
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            $original = [];

            foreach (array_keys($changes) as $key) {
                $original[$key] = $model->getOriginal($key);
            }

            if (!empty($changes)) {
                AuditService::updated($model, $original, $changes);
            }
        });

        static::deleted(function (Model $model) {
            AuditService::deleted($model);
        });
    }
}