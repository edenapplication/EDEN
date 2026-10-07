@extends('admin.affectations.layout')
@section('content')

<style>
.form-section {
    background:white; border-radius:14px; padding:20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    margin-bottom:16px;
    border-left:4px solid #e2e8f0;
}
.form-section.gs   { border-left-color:#1e3a5f; background:linear-gradient(135deg,#f8fafc,#eff6ff); }
.form-section.site { border-left-color:#16a34a; background:linear-gradient(135deg,#f0fdf4,#dcfce7); }
.form-section.tf   { border-left-color:#92400e; background:linear-gradient(135deg,#fef3c7,#fef9c3); }

.form-section h5 {
    margin:0 0 14px 0; font-weight:800; font-size:15px;
    display:flex; align-items:center; gap:8px;
}
.form-section .badge-optionnel {
    background:#e2e8f0; color:#64748b;
    padding:2px 8px; border-radius:6px;
    font-size:10px; font-weight:800;
    text-transform:uppercase;
}
.form-section label {
    font-size:12px; font-weight:700; color:#475569;
    margin-bottom:4px; display:block;
}
.form-section input {
    width:100%; padding:10px 14px;
    border:1px solid #e2e8f0; border-radius:8px;
    font-size:14px;
    transition:0.2s;
}
.form-section input:focus {
    outline:none; border-color:#7c3aed;
    box-shadow:0 0 0 3px rgba(124,58,237,0.1);
}
</style>

<div class="container" style="max-width:600px;">

    {{-- EN-TÊTE --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color:#1e3a5f;font-weight:800;margin:0;">⚡ Création rapide</h2>
            <div style="font-size:13px;color:#64748b;">
                Créez un Grand Site, un Site et un TF en un seul formulaire
            </div>
        </div>
        <a href="{{ route('affectations.index') }}" class="btn btn-outline-secondary btn-sm">
            ← Retour
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.creation-rapide.store') }}">
        @csrf

        {{-- 🏢 GRAND SITE --}}
        <div class="form-section gs">
            <h5>
                🏢 1. Grand Site
                <span style="color:#dc2626;font-size:12px;">*</span>
            </h5>
            <label>📝 Nom du Grand Site <span style="color:#dc2626;">*</span></label>
            <input type="text" name="gs_nom" required
                   value="{{ old('gs_nom') }}"
                   placeholder="Ex : Cité Verte"
                   autofocus>
        </div>

        {{-- 📍 SITE --}}
        <div class="form-section site">
            <h5>
                📍 2. Site
                <span class="badge-optionnel">Optionnel</span>
            </h5>
            <label>📝 Nom du Site</label>
            <input type="text" name="site_nom"
                   value="{{ old('site_nom') }}"
                   placeholder="Ex : Site A">
        </div>

        {{-- 📄 TF --}}
        <div class="form-section tf">
            <h5>
                📄 3. Titre Foncier (TF)
                <span class="badge-optionnel">Optionnel</span>
            </h5>
            <label>📝 Titre du TF</label>
            <input type="text" name="tf_titre"
                   value="{{ old('tf_titre') }}"
                   placeholder="Ex : TF-001">

            <div style="margin-top:12px;padding:10px 14px;background:white;border-radius:8px;font-size:11.5px;color:#92400e;">
                💡 <strong>Note :</strong> Le TF nécessite qu'un Site soit créé en même temps.
            </div>
        </div>

        {{-- ACTIONS --}}
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button type="submit"
                    class="btn"
                    style="background:linear-gradient(135deg,#7c3aed,#6d28d9);
                           color:white;border:none;font-weight:800;
                           padding:12px 32px;border-radius:10px;
                           box-shadow:0 4px 14px rgba(124,58,237,0.3);
                           flex:1;">
                💾 Tout créer
            </button>
            <a href="{{ route('affectations.index') }}"
               class="btn btn-outline-secondary"
               style="padding:12px 24px;border-radius:10px;">
                Annuler
            </a>
        </div>

    </form>
</div>

@endsection