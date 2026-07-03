@extends('admin.layout')
@section('content')

<style>
.fiche-row { background:white; border-radius:12px; padding:14px 16px; margin-bottom:8px; box-shadow:0 2px 8px rgba(0,0,0,0.05); border-left:4px solid #16a34a; }
.fiche-row.non-vue { border-left-color:#dc2626; }
.badge-new { background:#fee2e2; color:#b91c1c; font-size:10px; padding:2px 8px; border-radius:12px; font-weight:700; }
.badge-vue { background:#dcfce7; color:#15803d; font-size:10px; padding:2px 8px; border-radius:12px; font-weight:700; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.feb.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← FEB</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">📋 Toutes les Fiches</h2>
        <div style="font-size:13px;color:#64748b;">{{ $fiches->total() }} fiche(s) soumise(s)</div>
    </div>
</div>

{{-- Filtres --}}
<form method="GET" style="background:white;border-radius:12px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.05);margin-bottom:16px;">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Utilisateur</label>
            <select name="utilisateur_id" class="form-control form-control-sm">
                <option value="">Tous</option>
                @foreach($utilisateurs as $u)
                    <option value="{{ $u->id }}" {{ request('utilisateur_id')==$u->id?'selected':'' }}>
                        {{ $u->nom }} {{ $u->prenom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Agence</label>
            <select name="agence_id" class="form-control form-control-sm">
                <option value="">Toutes</option>
                @foreach($agences as $a)
                    <option value="{{ $a->id }}" {{ request('agence_id')==$a->id?'selected':'' }}>{{ $a->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Du</label>
            <input type="date" name="du" class="form-control form-control-sm" value="{{ request('du') }}">
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Au</label>
            <input type="date" name="au" class="form-control form-control-sm" value="{{ request('au') }}">
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Statut lecture</label>
            <select name="vue" class="form-control form-control-sm">
                <option value="">Tous</option>
                <option value="0" {{ request('vue')==='0'?'selected':'' }}>Non vues</option>
                <option value="1" {{ request('vue')==='1'?'selected':'' }}>Vues</option>
            </select>
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button class="btn btn-primary btn-sm">🔍</button>
            <a href="{{ route('admin.feb.fiches.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
    </div>
</form>

@forelse($fiches as $f)
<div class="fiche-row {{ !$f->vue_admin ? 'non-vue' : '' }}">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div style="font-weight:700;font-size:14px;color:#1e3a5f;">
                {{ $f->titre }}
                @if(!$f->vue_admin)
                    <span class="badge-new ms-2">NOUVEAU</span>
                @else
                    <span class="badge-vue ms-2">✅ Vue</span>
                @endif
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:3px;">
                👤 <strong>{{ $f->utilisateur?->nom_complet }}</strong>
                — 🏢 {{ $f->utilisateur?->agence?->nom ?? '-' }}
                — {{ $f->utilisateur?->poste ?? '-' }}
                &nbsp;·&nbsp; 📅 {{ $f->soumise_at?->format('d/m/Y à H:i') }}
                &nbsp;·&nbsp; {{ $f->sections->count() ?? 0 }} section(s)
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.feb.fiches.show', $f->id) }}" class="btn btn-primary btn-sm" style="font-size:11px;">👁 Consulter</a>
            <a href="{{ route('admin.feb.fiches.pdf',  $f->id) }}" class="btn btn-outline-danger btn-sm" style="font-size:11px;">🖨️ PDF</a>
        </div>
    </div>
</div>
@empty
<div style="background:white;border-radius:12px;padding:40px;text-align:center;color:#94a3b8;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <div style="font-size:40px;">📭</div>
    <div style="font-size:15px;font-weight:700;margin-top:10px;">Aucune fiche soumise</div>
</div>
@endforelse

<div class="mt-3">{{ $fiches->links() }}</div>

@endsection