@extends('layouts.comptable')
@section('content')

{{-- ✅ Affichage automatique de la référence de l'utilisateur connecté --}}
<div class="alert alert-info" style="font-size:12px;">
    <strong>🔑 Référence automatique :</strong> 
    <span style="background:#1e3a5f;color:white;padding:2px 10px;border-radius:6px;font-weight:700;">
        {{ $reference }}
    </span>
    <br>
    <strong>👤 Utilisateur connecté :</strong> 
    {{ $user->name ?? $user->email ?? 'Utilisateur' }}
    @if($user->role)
        <span style="background:#e0e7ff;color:#1e40af;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;margin-left:6px;">
            {{ strtoupper($user->role) }}
        </span>
    @endif
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('comptable.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← Retour</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">💳 Paiement Technique</h2>
        <div style="font-size:13px;color:#64748b;">
            {{ $dossier->client?->name }} — {{ $dossier->grandSite?->nom ?? $dossier->nom_dossier }}
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <form method="POST" action="{{ route('comptable.store', $dossier->id) }}" id="formPaiement">
            @csrf
            {{-- ✅ Plus besoin de champ "reference" caché, on utilise Auth::user() --}}

            {{-- RÉCAP (inchangé) --}}
            <div style="background:white;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:16px;">
                <h6 style="font-weight:700;color:#1e3a5f;margin-bottom:14px;">📋 Récapitulatif</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div style="background:#f1f5f9;border-radius:8px;padding:12px;">
                            <div style="font-size:11px;color:#64748b;">Prix technique</div>
                            <div style="font-weight:900;color:#1e3a5f;font-size:16px;">
                                {{ number_format($prixRef, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div style="background:#fef3c7;border-radius:8px;padding:12px;">
                            <div style="font-size:11px;color:#92400e;">Déjà payé</div>
                            <div style="font-weight:900;color:#b45309;font-size:16px;">
                                {{ number_format($dejaPaye, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div style="background:#fee2e2;border-radius:8px;padding:12px;">
                            <div style="font-size:11px;color:#991b1b;">Reste à payer</div>
                            <div style="font-weight:900;color:#b91c1c;font-size:16px;">
                                {{ number_format($reste, 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FORMULAIRE (inchangé) --}}
            <div style="background:white;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:16px;">
                <h6 style="font-weight:700;color:#1e3a5f;margin-bottom:14px;">💰 Nouveau paiement</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Montant à payer (FCFA) <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="montant" id="montant"
                               class="form-control form-control-lg"
                               min="1" max="{{ $reste }}"
                               value="{{ $reste }}" required
                               oninput="verifierMontant()">
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">
                            Maximum autorisé : {{ number_format($reste, 0, ',', ' ') }} FCFA
                        </div>
                        <div id="erreur-montant" style="display:none;color:#dc2626;font-size:12px;font-weight:700;margin-top:4px;">
                            ⚠️ Le montant dépasse le reste à payer
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Date du paiement <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date_paiement" class="form-control form-control-lg"
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Note (optionnel)</label>
                        <textarea name="note" class="form-control" rows="2"
                                  placeholder="Remarque sur ce paiement..."></textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('comptable.index') }}" class="btn btn-light">Annuler</a>
                <button type="submit" class="btn btn-primary px-5" style="font-weight:700;" id="btnSubmit">
                    💾 Enregistrer le paiement
                </button>
            </div>
        </form>
    </div>

    {{-- RÉCAP DROITE (inchangé) --}}
    <div class="col-md-4">
        <div style="background:#0f172a;border-radius:14px;padding:20px;color:#cbd5e1;position:sticky;top:80px;">
            <h6 style="color:#60a5fa;font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;">
                📋 Détails du dossier
            </h6>
            <div style="font-weight:800;color:white;font-size:14px;margin-bottom:12px;">
                {{ $dossier->client?->name }}
            </div>
            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">
                📞 {{ $dossier->client?->phone ?? '-' }}
            </div>
            <div style="font-size:12px;color:#64748b;margin-bottom:16px;">
                🏢 {{ $dossier->grandSite?->nom ?? '-' }}
            </div>

            <div style="border-top:1px solid rgba(255,255,255,0.08);padding-top:12px;">
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px;">
                    <span style="color:#64748b;">Réf. technique</span>
                    <span style="color:#60a5fa;font-weight:700;">{{ number_format($prixRef, 0, ',', ' ') }} FCFA</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px;">
                    <span style="color:#64748b;">Déjà payé</span>
                    <span style="color:#fb923c;font-weight:700;">{{ number_format($dejaPaye, 0, ',', ' ') }} FCFA</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:14px;border-top:1px solid rgba(255,255,255,0.08);padding-top:8px;margin-top:8px;">
                    <span style="color:white;font-weight:700;">Reste</span>
                    <span style="color:#f87171;font-weight:900;">{{ number_format($reste, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const maxMontant = {{ $reste }};

function verifierMontant() {
    const input = document.getElementById('montant');
    const erreur = document.getElementById('erreur-montant');
    const btn = document.getElementById('btnSubmit');
    const val = parseFloat(input.value) || 0;

    if (val > maxMontant || val <= 0) {
        erreur.style.display = val > maxMontant ? 'block' : 'none';
        input.style.borderColor = '#dc2626';
        btn.disabled = true;
        btn.style.opacity = '0.5';
    } else {
        erreur.style.display = 'none';
        input.style.borderColor = '#16a34a';
        btn.disabled = false;
        btn.style.opacity = '1';
    }
}

document.addEventListener('DOMContentLoaded', verifierMontant);
</script>
@endsection