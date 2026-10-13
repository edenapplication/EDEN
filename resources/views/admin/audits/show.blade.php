@extends('admin.layout')

@section('content')

<style>
.audit-detail-card {
    background:white; border-radius:14px;
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    overflow:hidden; margin-bottom:20px;
}

.audit-detail-header {
    padding:20px 24px;
    border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; gap:14px;
    flex-wrap:wrap;
}
.audit-detail-header .ico {
    width:56px; height:56px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:26px; flex-shrink:0;
}
.audit-detail-header .title {
    font-size:18px; font-weight:900; color:#1e3a5f;
    text-transform:uppercase; letter-spacing:0.4px;
    margin:0;
}
.audit-detail-header .subtitle {
    font-size:13px; color:#64748b; margin-top:2px;
}

.audit-detail-body { padding:24px; }

.audit-info-grid {
    display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));
    gap:14px; margin-bottom:24px;
}

.audit-info-item {
    background:#f8fafc; border-radius:10px;
    padding:12px 16px; border-left:3px solid #cbd5e1;
}
.audit-info-item .lbl {
    font-size:10px; font-weight:800; color:#64748b;
    text-transform:uppercase; letter-spacing:0.5px;
    margin-bottom:4px;
}
.audit-info-item .val {
    font-size:13.5px; color:#1e293b; font-weight:600;
    word-break:break-word;
}
.audit-info-item .val code {
    background:#e2e8f0; padding:2px 6px;
    border-radius:4px; font-size:12px;
    font-family:monospace;
}

.audit-diff-grid {
    display:grid; grid-template-columns:1fr 1fr;
    gap:16px; margin-bottom:20px;
}
@media (max-width:768px) {
    .audit-diff-grid { grid-template-columns:1fr; }
}

.audit-diff-col {
    border-radius:10px; padding:16px;
    border:2px solid;
}
.audit-diff-col.avant {
    background:#fef2f2; border-color:#fca5a5;
}
.audit-diff-col.apres {
    background:#f0fdf4; border-color:#86efac;
}

.audit-diff-col h4 {
    font-size:13px; font-weight:900;
    text-transform:uppercase; letter-spacing:0.5px;
    margin-bottom:12px;
    display:flex; align-items:center; gap:6px;
}
.audit-diff-col.avant h4 { color:#991b1b; }
.audit-diff-col.apres h4 { color:#166534; }

.audit-diff-col pre {
    font-size:11.5px; line-height:1.5;
    white-space:pre-wrap; word-break:break-word;
    margin:0; font-family:monospace;
    color:#1e293b;
}
.audit-diff-col.avant pre { color:#7f1d1d; }
.audit-diff-col.apres pre { color:#14532d; }

.audit-champs-modifies {
    background:#eff6ff; border-radius:10px;
    padding:16px 20px; border-left:3px solid #1d4ed8;
    margin-bottom:20px;
}
.audit-champs-modifies h4 {
    font-size:13px; font-weight:900; color:#1d4ed8;
    text-transform:uppercase; letter-spacing:0.5px;
    margin-bottom:12px;
}
.audit-champ-line {
    display:flex; align-items:center; gap:10px;
    padding:6px 0; border-bottom:1px solid #dbeafe;
    font-size:13px;
}
.audit-champ-line:last-child { border-bottom:none; }
.audit-champ-line .name {
    font-weight:800; color:#1e3a5f;
    min-width:150px; font-size:12px;
}
.audit-champ-line .old {
    color:#991b1b; text-decoration:line-through;
    font-family:monospace; font-size:12px;
}
.audit-champ-line .arrow {
    color:#94a3b8; font-weight:900;
}
.audit-champ-line .new {
    color:#166534; font-weight:700;
    font-family:monospace; font-size:12px;
}

.audit-metadata {
    background:#f8fafc; border-radius:10px;
    padding:16px 20px; border-left:3px solid #7c3aed;
    margin-bottom:20px;
}
.audit-metadata h4 {
    font-size:13px; font-weight:900; color:#6b21a8;
    text-transform:uppercase; letter-spacing:0.5px;
    margin-bottom:12px;
}
.audit-metadata pre {
    font-size:11.5px; line-height:1.5;
    white-space:pre-wrap; word-break:break-word;
    margin:0; font-family:monospace; color:#1e293b;
}

.audit-badge-severity {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 12px; border-radius:20px;
    font-size:11px; font-weight:800;
    text-transform:uppercase; letter-spacing:0.4px;
}
.audit-badge-severity.info     { background:#dbeafe; color:#1e40af; }
.audit-badge-severity.warning  { background:#fef3c7; color:#92400e; }
.audit-badge-severity.critical { background:#fee2e2; color:#991b1b; }

.audit-btn-back {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 16px; border-radius:8px;
    background:white; color:#64748b;
    border:2px solid #e2e8f0;
    font-size:13px; font-weight:700;
    text-decoration:none; transition:0.2s;
}
.audit-btn-back:hover { border-color:#1d4ed8; color:#1d4ed8; }
</style>

{{-- BOUTON RETOUR --}}
<div style="margin-bottom:20px;">
    <a href="{{ route('audits.index') }}" class="audit-btn-back">
        ← Retour au journal
    </a>
</div>

{{-- CARTE PRINCIPALE --}}
<div class="audit-detail-card">

    {{-- EN-TÊTE --}}
    <div class="audit-detail-header" style="border-left:5px solid {{ $audit->couleur }};">
        <div class="ico" style="background:{{ $audit->couleur }}20;color:{{ $audit->couleur }};">
            {{ $audit->icone }}
        </div>
        <div style="flex:1;">
            <h3 class="title">{{ $audit->event }}</h3>
            <div class="subtitle">
                {{ $audit->created_at->format('d/m/Y à H:i:s') }}
                <span style="color:#cbd5e1;">•</span>
                <span style="color:#64748b;">#{{ $audit->id }}</span>
            </div>
        </div>
        <div>
            <span class="audit-badge-severity {{ $audit->severity }}">
                @if($audit->severity === 'critical') ⚠️ Critique
                @elseif($audit->severity === 'warning') ⚠️ Avertissement
                @else ℹ️ Information
                @endif
            </span>
        </div>
    </div>

    {{-- CORPS --}}
    <div class="audit-detail-body">

        {{-- DESCRIPTION PRINCIPALE --}}
        @if($audit->description)
        <div style="background:#eff6ff;padding:16px 20px;border-radius:10px;margin-bottom:24px;border-left:4px solid #1d4ed8;">
            <div style="font-size:11px;color:#1e40af;font-weight:800;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px;">
                📝 Description
            </div>
            <div style="font-size:15px;color:#1e293b;font-weight:600;line-height:1.5;">
                {{ $audit->description }}
            </div>
        </div>
        @endif

        {{-- INFOS GÉNÉRALES --}}
        <h4 style="font-size:14px;font-weight:900;color:#1e3a5f;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;">
            📋 Informations générales
        </h4>

        <div class="audit-info-grid">
            <div class="audit-info-item" style="border-left-color:#1d4ed8;">
                <div class="lbl">👤 Utilisateur</div>
                <div class="val">
                    {{ $audit->user_name ?? 'Système' }}
                    @if($audit->user_role)
                        <span style="font-size:11px;color:#64748b;text-transform:uppercase;margin-left:6px;">
                            ({{ $audit->user_role }})
                        </span>
                    @endif
                </div>
            </div>

            <div class="audit-info-item" style="border-left-color:#7c3aed;">
                <div class="lbl">📁 Module</div>
                <div class="val">{{ $audit->module ?? '—' }}</div>
            </div>

            @if($audit->auditable_type)
            <div class="audit-info-item" style="border-left-color:#16a34a;">
                <div class="lbl">🎯 Élément concerné</div>
                <div class="val">
                    <code>{{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}</code>
                    @if($audit->auditable_label)
                        <div style="font-size:12px;color:#64748b;margin-top:4px;font-style:italic;">
                            {{ $audit->auditable_label }}
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <div class="audit-info-item" style="border-left-color:#f59e0b;">
                <div class="lbl">🌐 Méthode HTTP</div>
                <div class="val"><code>{{ $audit->method ?? '—' }}</code></div>
            </div>

            <div class="audit-info-item" style="border-left-color:#0ea5e9;">
                <div class="lbl">🔗 URL</div>
                <div class="val" style="font-size:12px;font-family:monospace;">
                    {{ $audit->url ?? '—' }}
                </div>
            </div>

            <div class="audit-info-item" style="border-left-color:#94a3b8;">
                <div class="lbl">📍 Adresse IP</div>
                <div class="val"><code>{{ $audit->ip ?? '—' }}</code></div>
            </div>

            @if($audit->user_agent)
            <div class="audit-info-item" style="border-left-color:#64748b;grid-column:1/-1;">
                <div class="lbl">💻 Navigateur</div>
                <div class="val" style="font-size:11.5px;color:#64748b;">
                    {{ \Illuminate\Support\Str::limit($audit->user_agent, 150) }}
                </div>
            </div>
            @endif
        </div>

        {{-- CHAMPS MODIFIÉS (résumé clair) --}}
        @php
            $champsModifies = $audit->champs_modifies;
        @endphp

        @if(count($champsModifies) > 0)
        <div class="audit-champs-modifies">
            <h4>📊 Champs modifiés ({{ count($champsModifies) }})</h4>
            @foreach($champsModifies as $champ => $diff)
            <div class="audit-champ-line">
                <span class="name">{{ $champ }}</span>
                <span class="old">{{ \Illuminate\Support\Str::limit(is_array($diff['avant']) ? json_encode($diff['avant']) : ($diff['avant'] ?? '—'), 40) }}</span>
                <span class="arrow">→</span>
                <span class="new">{{ \Illuminate\Support\Str::limit(is_array($diff['apres']) ? json_encode($diff['apres']) : ($diff['apres'] ?? '—'), 40) }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- DIFF AVANT / APRÈS --}}
        @if($audit->old_values || $audit->new_values)
        <h4 style="font-size:14px;font-weight:900;color:#1e3a5f;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;">
            🔄 Valeurs avant / après
        </h4>

        <div class="audit-diff-grid">
            <div class="audit-diff-col avant">
                <h4>❌ Avant</h4>
                @if($audit->old_values)
                    <pre>{{ json_encode($audit->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <em style="color:#94a3b8;font-size:12px;">— Vide (création) —</em>
                @endif
            </div>

            <div class="audit-diff-col apres">
                <h4>✅ Après</h4>
                @if($audit->new_values)
                    <pre>{{ json_encode($audit->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <em style="color:#94a3b8;font-size:12px;">— Vide (suppression) —</em>
                @endif
            </div>
        </div>
        @endif

        {{-- MÉTADONNÉES --}}
        @if($audit->metadata)
        <div class="audit-metadata">
            <h4>📦 Métadonnées additionnelles</h4>
            <pre>{{ json_encode($audit->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif

    </div>

</div>

{{-- BOUTON SUPPRIMER (optionnel) --}}
<div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
    <form method="POST" action="{{ route('audits.destroy', $audit) }}"
          onsubmit="return confirm('⚠️ Supprimer cette entrée d\'audit ?\n\nCette action est irréversible.');"
          style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit"
                style="padding:10px 20px;border-radius:8px;background:#fee2e2;color:#991b1b;border:2px solid #fca5a5;font-size:13px;font-weight:800;cursor:pointer;transition:0.2s;"
                onmouseover="this.style.background='#dc2626';this.style.color='white';"
                onmouseout="this.style.background='#fee2e2';this.style.color='#991b1b';">
            🗑️ Supprimer cette entrée
        </button>
    </form>
</div>

@endsection