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
                    background:linear-gradient(135deg,#16a34a,#15803d);color:white;
                    font-weight:800;font-size:12px;">
            2️⃣ Site
        </div>
        <div style="flex:1;text-align:center;padding:10px;border-radius:10px;
                    background:#f1f5f9;color:#94a3b8;font-weight:800;font-size:12px;">
            3️⃣ TF
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color:#1e3a5f;font-weight:800;margin:0;">⚡ Création — Étape 2</h2>
            <div style="font-size:13px;color:#64748b;">
                Créez un ou <strong>plusieurs</strong> Sites
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

    <form method="POST" action="{{ route('admin.creation.etape-2.store') }}"
          style="background:white;border-radius:14px;padding:24px;
                 box-shadow:0 2px 10px rgba(0,0,0,0.06);
                 border-left:4px solid #16a34a;">
        @csrf

        {{-- 🏢 CHOIX DU GRAND SITE --}}
        <label style="font-size:13px;font-weight:700;color:#475569;">
            🏢 Grand Site parent <span style="color:#dc2626;">*</span>
        </label>
        <select name="grand_site_id" required
                style="width:100%;padding:12px 14px;margin-top:6px;
                       border:1px solid #e2e8f0;border-radius:8px;
                       font-size:14px;background:white;">
            <option value="">— Choisir un Grand Site —</option>
            @foreach($grandSites as $gs)
                <option value="{{ $gs->id }}" {{ old('grand_site_id') == $gs->id ? 'selected' : '' }}>
                    🏢 {{ $gs->nom }}
                </option>
            @endforeach
        </select>

        {{-- 📍 NOMS DES SITES --}}
        <div style="margin-top:20px;">
            <label style="font-size:13px;font-weight:700;color:#475569;">
                📍 Nom(s) du/des Site(s) <span style="color:#dc2626;">*</span>
            </label>
            <textarea name="site_noms" required rows="4"
                      placeholder="Site A; Site B; Site C"
                      style="width:100%;padding:12px 14px;margin-top:6px;
                             border:1px solid #e2e8f0;border-radius:8px;
                             font-size:14px;font-family:inherit;">{{ old('site_noms') }}</textarea>

            <div style="margin-top:10px;padding:10px 14px;background:#f0fdf4;
                        border-radius:8px;font-size:12px;color:#166534;">
                💡 <strong>Séparez les noms par un point-virgule (;)</strong><br>
                Exemple : <code>Site A; Site B; Site C</code>
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px;">
            <button type="submit"
                    style="flex:1;background:linear-gradient(135deg,#16a34a,#15803d);
                           color:white;border:none;font-weight:800;
                           padding:12px;border-radius:10px;font-size:14px;
                           box-shadow:0 4px 14px rgba(22,163,74,0.3);">
                ➡️ Créer et passer aux TFs
            </button>
        </div>
    </form>

</div>

@endsection