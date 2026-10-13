<?php

use App\Services\AuditService;

if (!function_exists('audit')) {
    /**
     * 🎯 Helper global d'audit
     * 
     * Usage :
     *   audit('event', 'Description', ['meta' => 'data'], 'info');
     */
    function audit(string $event, string $description, array $metadata = [], string $severity = 'info'): \App\Models\Audit
    {
        return AuditService::action($event, $description, $metadata, $severity);
    }
}