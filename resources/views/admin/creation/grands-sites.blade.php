@extends('admin.affectations.layout')
@section('content')

<div class="container" style="max-width:600px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color:#1e3a5f;font-weight:800;margin:0;">🏢 Créer des Grands Sites</h2>
            <div style="font-size:13px;color:#64748b;">
                Créez un ou <strong>plusieurs</strong> Grands Sites à la fois
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

    <form method="POST" action="{{ route('admin.creation.grands-sites.store') }}"
          style="background:white;border-radius:14px;padding:24px;
                 box-shadow:0 2px 10px rgba(0,0,0,0.06);
                 border-left:4px solid #1e3a5f;">
        @csrf

        <label style="font-size:13px;font-weight:700;color:#475569;">
            🏢 Nom(s) du/des Grand Site(s) <span style="color:#dc2626;">*</span>
        </label>
        <textarea name="noms" required autofocus rows="4"
                  placeholder="Cité Verte; Bastos; Nkolbisson"
                  style="width:100%;padding:12px 14px;margin-top:6px;
                         border:1px solid #e2e8f0;border-radius:8px;
                         font-size:14px;font-family:inherit;">{{ old('noms') }}</textarea>

        <div style="margin-top:10px;padding:10px 14px;background:#eff6ff;
                    border-radius:8px;font-size:12px;color:#1e40af;">
            💡 <strong>Séparez les noms par un point-virgule (;)</strong><br>
            Exemple : <code>Cité Verte; Bastos; Nkolbisson</code>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px;">
            <button type="submit"
                    style="flex:1;background:linear-gradient(135deg,#1e3a5f,#1d4ed8);
                           color:white;border:none;font-weight:800;
                           padding:14px;border-radius:10px;font-size:14px;
                           box-shadow:0 4px 14px rgba(29,78,216,0.3);">
                💾 Créer les Grands Sites
            </button>
        </div>
    </form>

</div>

@endsection