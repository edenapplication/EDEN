<?php

namespace App\Services;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * ═══════════════════════════════════════════════════════════
     * 📝 ENREGISTRER UNE ACTION D'AUDIT
     * ═══════════════════════════════════════════════════════════
     */
    public static function log(
        string $event,
        ?Model $model = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        string $severity = 'info',
        ?string $module = null,
        ?string $label = null,
        ?array $tags = null
    ): Audit {
        try {
            $user = Auth::user();

            // Détection auto du module
            if (!$module && $model) {
                $module = self::detectModule($model);
            }

            // Détection auto du label
            if (!$label && $model) {
                $label = self::detectLabel($model);
            }

            return Audit::create([
                'user_id'         => $user?->id,
                'user_name'       => $user?->name ?? 'Système',
                'user_role'       => $user?->role ?? null,
                'event'           => $event,
                'module'          => $module,
                'auditable_type'  => $model ? get_class($model) : null,
                'auditable_id'    => $model?->getKey(),
                'auditable_label' => $label,
                'description'     => $description,
                'old_values'      => $oldValues ? self::filterValues($oldValues) : null,
                'new_values'      => $newValues ? self::filterValues($newValues) : null,
                'metadata'        => $metadata,
                'url'             => substr(Request::fullUrl(), 0, 500),
                'method'          => Request::method(),
                'ip'              => Request::ip(),
                'user_agent'      => substr(Request::userAgent() ?? '', 0, 500),
                'severity'        => $severity,
                'tags'            => $tags ? implode(',', $tags) : null,
            ]);

        } catch (\Exception $e) {
            // ✅ Ne jamais faire planter l'app à cause de l'audit
            \Log::error('❌ Erreur AuditService::log', [
                'event'   => $event,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            // Retourner un Audit vide pour ne pas casser le flux
            return new Audit();
        }
    }

    // ═══════════════════════════════════════════════════════════
    // 🎯 MÉTHODES PRÊTES À L'EMPLOI
    // ═══════════════════════════════════════════════════════════

    public static function created(Model $model, ?string $description = null): Audit
    {
        return self::log(
            'created',
            $model,
            $description ?? "Création : " . class_basename($model) . " #{$model->getKey()}",
            null,
            $model->getAttributes(),
            null,
            'info'
        );
    }

    public static function updated(Model $model, array $old, array $new): Audit
    {
        $diff = self::computeDiff($old, $new);

        return self::log(
            'updated',
            $model,
            "Modification : " . class_basename($model) . " #{$model->getKey()}",
            $diff['old'],
            $diff['new'],
            ['changed_fields' => array_keys($diff['new'])],
            'info'
        );
    }

    public static function deleted(Model $model, ?string $description = null): Audit
    {
        return self::log(
            'deleted',
            $model,
            $description ?? "Suppression : " . class_basename($model) . " #{$model->getKey()}",
            $model->getAttributes(),
            null,
            null,
            'warning'
        );
    }

    public static function login($user, string $guard = 'web'): Audit
    {
        return self::log(
            'login',
            $user,
            "Connexion de « {$user->name} »",
            null, null,
            ['guard' => $guard],
            'info',
            'auth'
        );
    }

    public static function logout($user): Audit
    {
        return self::log(
            'logout',
            $user,
            "Déconnexion de « {$user->name} »",
            null, null, null,
            'info',
            'auth'
        );
    }

    public static function action(
        string $event,
        string $description,
        array $metadata = [],
        string $severity = 'info',
        ?string $module = null
    ): Audit {
        return self::log($event, null, $description, null, null, $metadata, $severity, $module);
    }

    // ═══════════════════════════════════════════════════════════
    // 🛠️ HELPERS PRIVÉS
    // ═══════════════════════════════════════════════════════════

    private static function detectModule(Model $model): string
    {
        return match (class_basename($model)) {
            'Affectation'           => 'affectations',
            'Client'                => 'clients',
            'Beneficiaire'          => 'beneficiaires',
            'DossierClient'         => 'dossiers',
            'LotAffectation'        => 'lots',
            'Bloc'                  => 'blocs',
            'Site'                  => 'sites',
            'GrandSite'             => 'grand_sites',
            'Tf'                    => 'tfs',
            'User'                  => 'utilisateurs',
            'DocumentPdf'           => 'documents',
            'HistoriqueAffectation' => 'historique',
            'Utilisateur'           => 'feb',
            'ZoneGroupe'            => 'zones',
            'PaiementDossier'       => 'paiements',
            'PaiementTechnique'     => 'paiements',
            'PaiementMorcellement'  => 'paiements',
            'BonPaiement'           => 'bons',
            default                 => 'general',
        };
    }

    private static function detectLabel(Model $model): ?string
    {
        foreach (['nom', 'name', 'titre', 'title', 'code', 'numero', 'libelle', 'identifiant', 'reference'] as $field) {
            if (!empty($model->$field)) {
                return substr((string) $model->$field, 0, 150);
            }
        }
        return class_basename($model) . ' #' . $model->getKey();
    }

    private static function filterValues(array $values): array
    {
        $exclude = ['password', 'password_confirmation', 'remember_token', 'created_at', 'updated_at', 'deleted_at'];

        return collect($values)
            ->except($exclude)
            ->map(function ($value) {
                if (is_string($value) && strlen($value) > 500) {
                    return substr($value, 0, 500) . '...';
                }
                if (is_object($value) || is_array($value)) {
                    return json_encode($value);
                }
                return $value;
            })
            ->toArray();
    }

    private static function computeDiff(array $old, array $new): array
    {
        $diffOld = [];
        $diffNew = [];

        foreach ($new as $key => $value) {
            $oldValue = $old[$key] ?? null;

            if ($oldValue !== $value) {
                $diffOld[$key] = $oldValue;
                $diffNew[$key] = $value;
            }
        }

        return ['old' => $diffOld, 'new' => $diffNew];
    }
}