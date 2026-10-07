@extends('admin.affectations.layout')
@section('content')

<div class="container" style="max-width:600px;">

    {{-- ÉTAPES --}}
    <div style="display:flex;gap:8px;margin-bottom:20px;">
        <div style="flex:1;text-align:center;padding:10px;border-radius:10px;
                    background:#dcfce7;color:#166534;font-weight:800;font-size:12px;">
            ✅ Grand Site
        </div>
        <div style="flex:1;text-align:center;padding:10px;border-radius:10px;
                    background:#dcfce7;color:#166534;font-weight:800;font-size:12px;">
            ✅ Site
        </div>
        <div style="flex:1;text-align:center;padding:10px;border-radius:10px;
                    background:linear-gradient(135deg,#d97706,#f59e0b);color:white;
                    font-weight:800;font-size:12px;">
            3️⃣ TF
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color:#1e3a5f;font-weight:800;margin:0;">⚡ Création — Étape 3</h2>
            <div style="font-size:13px;color:#64748b;">
                Créez un ou <strong>plusieurs</strong> TFs pour <strong style="color:#d97706;">{{ $site->name }}</strong>
            </div>
        </div>
        <a href="{{ route('affectations.index') }}" class="btn btn-outline-secondary btn-sm">
            ← Annuler
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;
                padding:12px 16px;margin-bottom:16px;font-size:13px;color:#92400e;">
        🏢 Grand Site : <strong>{{ $site->grandSite?->nom ?? '—' }}</strong><br>
        📍 Site : <strong>{{ $site->name }}</strong>
    </div>

    <form method="POST" action="{{ route('admin.creation.etape-3.store', $site->id) }}"
          style="background:white;border-radius:14px;padding:24px;
                 box-shadow:0 2px 10px rgba(0,0,0,0.06);
                 border-left:4px solid #d97706;">
        @csrf

        <label style="font-size:13px;font-weight:700;color:#475569;">
            📄 Titre(s) du/des TF(s) <span style="color:#dc2626;">*</span>
        </label>
        <textarea name="tf_titres" required autofocus rows="4"
                  placeholder="TF-001; TF-002; TF-003"
                  style="width:100%;padding:12px 14px;margin-top:6px;
                         border:1px solid #e2e8f0;border-radius:8px;
                         font-size:14px;font-family:inherit;">{{ old('tf_titres') }}</textarea>

        <div style="margin-top:10px;padding:10px 14px;background:#fef3c7;
                    border-radius:8px;font-size:12px;color:#92400e;">
            💡 <strong>Séparez les titres par un point-virgule (;)</strong><br>
            Exemple : <code>TF-001; TF-002; TF-003</code>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px;">
            <button type="submit"
                    style="flex:1;background:linear-gradient(135deg,#d97706,#f59e0b);
                           color:white;border:none;font-weight:800;
                           padding:12px;border-radius:10px;font-size:14px;
                           box-shadow:0 4px 14px rgba(217,119,6,0.3);">
                🎉 Tout créer et terminer
            </button>
        </div>
    </form>

</div>

@endsection