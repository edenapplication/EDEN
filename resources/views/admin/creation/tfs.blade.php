@extends('admin.affectations.layout')
@section('content')

<div class="container" style="max-width:600px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color:#1e3a5f;font-weight:800;margin:0;">📄 Créer des TFs</h2>
            <div style="font-size:13px;color:#64748b;">
                Créez un ou <strong>plusieurs</strong> TFs pour un Site
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

    @if($grandSites->isEmpty())
        <div class="alert alert-warning">
            ⚠️ Aucun Grand Site disponible. Créez d'abord un Grand Site et un Site.
        </div>
    @else

    <form method="POST" action="{{ route('admin.creation.tfs.store') }}"
          style="background:white;border-radius:14px;padding:24px;
                 box-shadow:0 2px 10px rgba(0,0,0,0.06);
                 border-left:4px solid #d97706;">
        @csrf

        {{-- GRAND SITE --}}
        <label style="font-size:13px;font-weight:700;color:#475569;">
            🏢 Grand Site parent <span style="color:#dc2626;">*</span>
        </label>
        <select id="selectGrandSite" required
                style="width:100%;padding:12px 14px;margin-top:6px;
                       border:1px solid #e2e8f0;border-radius:8px;
                       font-size:14px;background:white;">
            <option value="">— Choisir un Grand Site —</option>
            @foreach($grandSites as $gs)
                <option value="{{ $gs->id }}">🏢 {{ $gs->nom }}</option>
            @endforeach
        </select>

        {{-- SITE (dépend du GS) --}}
        <div style="margin-top:20px;">
            <label style="font-size:13px;font-weight:700;color:#475569;">
                📍 Site parent <span style="color:#dc2626;">*</span>
            </label>
            <select name="site_id" id="selectSite" required disabled
                    style="width:100%;padding:12px 14px;margin-top:6px;
                           border:1px solid #e2e8f0;border-radius:8px;
                           font-size:14px;background:white;">
                <option value="">— Choisir d'abord un Grand Site —</option>
            </select>
            <div id="siteLoader" style="display:none;font-size:11px;color:#94a3b8;margin-top:4px;">
                ⏳ Chargement des sites...
            </div>
        </div>

        {{-- TITRES DES TFS --}}
        <div style="margin-top:20px;">
            <label style="font-size:13px;font-weight:700;color:#475569;">
                📄 Titre(s) du/des TF(s) <span style="color:#dc2626;">*</span>
            </label>
            <textarea name="titres" required rows="4"
                      placeholder="TF-001; TF-002; TF-003"
                      style="width:100%;padding:12px 14px;margin-top:6px;
                             border:1px solid #e2e8f0;border-radius:8px;
                             font-size:14px;font-family:inherit;">{{ old('titres') }}</textarea>

            <div style="margin-top:10px;padding:10px 14px;background:#fef3c7;
                        border-radius:8px;font-size:12px;color:#92400e;">
                💡 <strong>Séparez les titres par un point-virgule (;)</strong><br>
                Exemple : <code>TF-001; TF-002; TF-003</code>
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px;">
            <button type="submit"
                    style="flex:1;background:linear-gradient(135deg,#d97706,#f59e0b);
                           color:white;border:none;font-weight:800;
                           padding:14px;border-radius:10px;font-size:14px;
                           box-shadow:0 4px 14px rgba(217,119,6,0.3);">
                💾 Créer les TFs
            </button>
        </div>
    </form>

    @endif
</div>

<script>
// 🔄 Select dynamique : charger les sites du grand site choisi
document.addEventListener('DOMContentLoaded', () => {
    const selectGs   = document.getElementById('selectGrandSite');
    const selectSite = document.getElementById('selectSite');
    const loader     = document.getElementById('siteLoader');

    if (!selectGs || !selectSite) return;

    selectGs.addEventListener('change', function() {
        const gsId = this.value;

        selectSite.innerHTML = '<option value="">— Chargement... —</option>';
        selectSite.disabled = true;

        if (!gsId) {
            selectSite.innerHTML = '<option value="">— Choisir d\'abord un Grand Site —</option>';
            return;
        }

        loader.style.display = 'block';

        fetch(`/admin/api/grand-sites/${gsId}/sites`, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(sites => {
            loader.style.display = 'none';

            if (!sites || sites.length === 0) {
                selectSite.innerHTML = '<option value="">— Aucun site pour ce Grand Site —</option>';
                selectSite.disabled = true;
                return;
            }

            let html = '<option value="">— Choisir un Site —</option>';
            sites.forEach(s => {
                html += `<option value="${s.id}">📍 ${s.name}</option>`;
            });
            selectSite.innerHTML = html;
            selectSite.disabled = false;
        })
        .catch(err => {
            loader.style.display = 'none';
            console.error(err);
            selectSite.innerHTML = '<option value="">— Erreur de chargement —</option>';
        });
    });
});
</script>

@endsection