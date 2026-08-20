@extends('admin.layout')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('bons.index', $dossier->id) }}" class="btn btn-outline-secondary btn-sm mb-2">← Bons</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">🧾 Nouveau paiement</h2>
        <div style="font-size:13px;color:#64748b;">
            {{ $dossier->client?->name }} — {{ $dossier->grandSite?->nom ?? $dossier->nom_dossier }}
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <form method="POST" action="{{ route('bons.store', $dossier->id) }}" id="formBon" onsubmit="return validerFormulaire()">
            @csrf

            {{-- INFOS DATE --}}
            <div style="background:white;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:16px;">
                <h6 style="font-weight:700;color:#1e3a5f;margin-bottom:14px;">📅 Date du bon</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date_bon" class="form-control"
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            {{-- VERSEMENTS DU JOUR --}}
            <div style="background:white;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:16px;">
                <h6 style="font-weight:700;color:#1e3a5f;margin-bottom:6px;">💰 Versements du jour</h6>
                <p style="font-size:12px;color:#64748b;margin-bottom:14px;">Saisissez 0 ou laissez vide si pas de versement pour ce type.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="color:#1d4ed8;">📁 Paiement De la Parcelle (FCFA)</label>
                        <input type="number" name="versement_dossier" id="v_dossier"
                               class="form-control" value="0" min="0"
                               oninput="calculerTotal()">
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">
                            Déjà versé : {{ number_format($totaux['dossier'],0,',',' ') }} FCFA
                            @if($dossier->prix_superficie > 0)
                                / Réf : {{ number_format($dossier->prix_superficie,0,',',' ') }} FCFA
                            @endif
                        </div>
                        <div id="erreur-dossier" style="display:none;color:#dc2626;font-size:11px;font-weight:600;margin-top:4px;">
                            ⚠️ Dépassement de la référence
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="color:#ea580c;">🛠️ Paiement Du Dossier Tech (FCFA)</label>
                        <input type="number" name="versement_technique" id="v_technique"
                               class="form-control" value="0" min="0"
                               oninput="calculerTotal()">
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">
                            Déjà versé : {{ number_format($totaux['technique'],0,',',' ') }} FCFA
                            @if($dossier->prix_technique > 0)
                                / Réf : {{ number_format($dossier->prix_technique,0,',',' ') }} FCFA
                            @endif
                        </div>
                        <div id="erreur-technique" style="display:none;color:#dc2626;font-size:11px;font-weight:600;margin-top:4px;">
                            ⚠️ Dépassement de la référence
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="color:#7c3aed;">🚗 Logistique D'implantation (FCFA)</label>
                        <input type="number" name="versement_logistique" id="v_logistique"
                               class="form-control" value="0" min="0"
                               oninput="calculerTotal()">
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">
                            Déjà versé : {{ number_format($totaux['logistique'],0,',',' ') }} FCFA
                            @if($dossier->prix_logistique > 0)
                                / Réf : {{ number_format($dossier->prix_logistique,0,',',' ') }} FCFA
                            @endif
                        </div>
                        <div id="erreur-logistique" style="display:none;color:#dc2626;font-size:11px;font-weight:600;margin-top:4px;">
                            ⚠️ Dépassement de la référence
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="color:#ca8a04;">✂️ Paiement Morcellement (FCFA)</label>
                        <input type="number" name="versement_morcellement" id="v_morcellement"
                               class="form-control" value="0" min="0"
                               oninput="calculerTotal()">
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">
                            Déjà versé : {{ number_format($totaux['morcellement'],0,',',' ') }} FCFA
                            @if($dossier->prix_morcellement > 0)
                                / Réf : {{ number_format($dossier->prix_morcellement,0,',',' ') }} FCFA
                            @endif
                        </div>
                        <div id="erreur-morcellement" style="display:none;color:#dc2626;font-size:11px;font-weight:600;margin-top:4px;">
                            ⚠️ Dépassement de la référence
                        </div>
                    </div>
                </div>

                {{-- TOTAL VERSEMENT --}}
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;margin-top:16px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-weight:700;color:#64748b;">Total versement du jour :</span>
                    <span style="font-size:20px;font-weight:900;color:#16a34a;" id="total-jour">0 FCFA</span>
                </div>

                {{-- ✅ MESSAGE D'ERREUR GLOBAL --}}
                <div id="erreur-depassement" style="display:none;background:#fee2e2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;margin-top:12px;color:#b91c1c;font-weight:600;font-size:13px;">
                    ⚠️ <span id="message-erreur"></span>
                </div>
            </div>

            {{-- OPTIONS --}}
            <div style="background:white;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:16px;">
                <h6 style="font-weight:700;color:#1e3a5f;margin-bottom:14px;">⚙️ Options</h6>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="afficher_reste" value="1"
                           id="afficher_reste" checked>
                    <label class="form-check-label" for="afficher_reste">
                        Afficher le montant restant à payer sur le bon
                    </label>
                </div>
                <div>
                    <label class="form-label fw-semibold">Notes internes</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Remarques sur ce paiement..."></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('bons.index', $dossier->id) }}" class="btn btn-light">Annuler</a>
                <button type="submit" class="btn btn-primary px-5" style="font-weight:700;" id="btnSubmit">
                    💾 Créer le bon
                </button>
            </div>
        </form>
    </div>

    {{-- RÉCAP DROITE --}}
    <div class="col-md-4">
        <div style="background:#0f172a;border-radius:14px;padding:20px;color:#cbd5e1;position:sticky;top:80px;">
            <h6 style="color:#60a5fa;font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;">
                📋 Récap du dossier
            </h6>
            <div style="font-weight:800;color:white;font-size:14px;margin-bottom:12px;">
                {{ $dossier->client?->name }}
            </div>
            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">
                📞 {{ $dossier->client?->phone ?? '-' }}
            </div>
            <div style="font-size:12px;color:#64748b;margin-bottom:16px;">
                🏢 {{ $dossier->grandSite?->nom ?? '-' }}
                @if($dossier->superficie_voulue)
                    · {{ number_format($dossier->superficie_voulue,0,',',' ') }} m²
                @endif
            </div>

            @if($dossier->prix_superficie > 0 && $dossier->superficie_voulue > 0)
            <div style="background:rgba(255,255,255,0.06);border-radius:8px;padding:10px;margin-bottom:8px;">
                <div style="font-size:10px;color:#475569;text-transform:uppercase;">Prix unitaire</div>
                <div style="font-weight:700;color:white;">
                    {{ number_format($dossier->prix_unitaire,0,',',' ') }} FCFA/m²
                </div>
            </div>
            @endif

            <div style="border-top:1px solid rgba(255,255,255,0.08);padding-top:12px;margin-top:8px;">
                <div style="font-size:10px;color:#475569;text-transform:uppercase;margin-bottom:8px;">Déjà versé</div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                    <span style="color:#64748b;">Parcelle</span>
                    <span style="color:#60a5fa;font-weight:700;">{{ number_format($totaux['dossier'],0,',',' ') }} FCFA</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                    <span style="color:#64748b;">Dossier Technique</span>
                    <span style="color:#fb923c;font-weight:700;">{{ number_format($totaux['technique'],0,',',' ') }} FCFA</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                    <span style="color:#64748b;">Logistique D'implantation</span>
                    <span style="color:#a78bfa;font-weight:700;">{{ number_format($totaux['logistique'],0,',',' ') }} FCFA</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px;">
                    <span style="color:#64748b;">Morcellement</span>
                    <span style="color:#fbbf24;font-weight:700;">{{ number_format($totaux['morcellement'],0,',',' ') }} FCFA</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:14px;border-top:1px solid rgba(255,255,255,0.08);padding-top:8px;">
                    <span style="color:white;font-weight:700;">Total versé</span>
                    <span style="color:#4ade80;font-weight:900;">
                        {{ number_format(array_sum($totaux),0,',',' ') }} FCFA
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
<script>
// Données PHP passées au JavaScript
const prixReferences = {
    dossier: {{ $dossier->prix_superficie ?? 0 }},
    technique: {{ $dossier->prix_technique ?? 0 }},
    logistique: {{ $dossier->prix_logistique ?? 0 }},
    morcellement: {{ $dossier->prix_morcellement ?? 0 }}
};

const totauxDejaVerses = {
    dossier: {{ $totaux['dossier'] ?? 0 }},
    technique: {{ $totaux['technique'] ?? 0 }},
    logistique: {{ $totaux['logistique'] ?? 0 }},
    morcellement: {{ $totaux['morcellement'] ?? 0 }}
};

function calculerTotal() {
    // Récupérer les valeurs saisies
    const vDossier = parseFloat(document.getElementById('v_dossier')?.value || 0) || 0;
    const vTechnique = parseFloat(document.getElementById('v_technique')?.value || 0) || 0;
    const vLogistique = parseFloat(document.getElementById('v_logistique')?.value || 0) || 0;
    const vMorcellement = parseFloat(document.getElementById('v_morcellement')?.value || 0) || 0;
    
    // Calculer le total du jour
    const totalJour = vDossier + vTechnique + vLogistique + vMorcellement;
    
    // Mettre à jour l'affichage du total
    const el = document.getElementById('total-jour');
    if (el) el.innerText = Math.round(totalJour).toLocaleString('fr-FR') + ' FCFA';
    
    // Vérifier les dépassements individuels
    let erreur = false;
    let messages = [];
    
    // Configuration des types
    const types = [
        { 
            id: 'v_dossier', 
            erreurId: 'erreur-dossier',
            nom: 'Parcelle', 
            prixRef: prixReferences.dossier, 
            dejaVersé: totauxDejaVerses.dossier, 
            valeur: vDossier,
            couleur: '#1d4ed8'
        },
        { 
            id: 'v_technique', 
            erreurId: 'erreur-technique',
            nom: 'Dossier Technique', 
            prixRef: prixReferences.technique, 
            dejaVersé: totauxDejaVerses.technique, 
            valeur: vTechnique,
            couleur: '#ea580c'
        },
        { 
            id: 'v_logistique', 
            erreurId: 'erreur-logistique',
            nom: 'Logistique', 
            prixRef: prixReferences.logistique, 
            dejaVersé: totauxDejaVerses.logistique, 
            valeur: vLogistique,
            couleur: '#7c3aed'
        },
        { 
            id: 'v_morcellement', 
            erreurId: 'erreur-morcellement',
            nom: 'Morcellement', 
            prixRef: prixReferences.morcellement, 
            dejaVersé: totauxDejaVerses.morcellement, 
            valeur: vMorcellement,
            couleur: '#ca8a04'
        }
    ];
    
    types.forEach(type => {
        const input = document.getElementById(type.id);
        const erreurEl = document.getElementById(type.erreurId);
        
        // Si la référence est 0, pas de validation
        if (type.prixRef === 0) {
            if (input) {
                input.style.borderColor = '#e2e8f0';
                input.style.borderWidth = '1px';
                input.style.backgroundColor = '';
            }
            if (erreurEl) erreurEl.style.display = 'none';
            return;
        }
        
        const totalApres = type.dejaVersé + type.valeur;
        
        if (totalApres > type.prixRef) {
            erreur = true;
            messages.push(
                `⚠️ ${type.nom} : ${totalApres.toLocaleString('fr-FR')} FCFA > ${type.prixRef.toLocaleString('fr-FR')} FCFA (référence)`
            );
            // Mettre en rouge le champ concerné
            if (input) {
                input.style.borderColor = '#dc2626';
                input.style.borderWidth = '2px';
                input.style.backgroundColor = '#fef2f2';
            }
            if (erreurEl) erreurEl.style.display = 'block';
        } else {
            // Remettre en vert si le total atteint exactement la référence
            if (input) {
                if (totalApres === type.prixRef && type.valeur > 0) {
                    input.style.borderColor = '#16a34a';
                    input.style.borderWidth = '2px';
                    input.style.backgroundColor = '#f0fdf4';
                } else if (type.valeur > 0) {
                    input.style.borderColor = '#fcd34d';
                    input.style.borderWidth = '2px';
                    input.style.backgroundColor = '#fffbeb';
                } else {
                    input.style.borderColor = '#e2e8f0';
                    input.style.borderWidth = '1px';
                    input.style.backgroundColor = '';
                }
            }
            if (erreurEl) erreurEl.style.display = 'none';
        }
    });
    
    // Afficher ou cacher le message d'erreur global
    const erreurDiv = document.getElementById('erreur-depassement');
    const messageErreur = document.getElementById('message-erreur');
    
    if (erreur) {
        erreurDiv.style.display = 'block';
        messageErreur.innerHTML = messages.join('<br>');
        document.getElementById('btnSubmit').disabled = true;
        document.getElementById('btnSubmit').style.opacity = '0.5';
        document.getElementById('btnSubmit').style.cursor = 'not-allowed';
    } else {
        erreurDiv.style.display = 'none';
        document.getElementById('btnSubmit').disabled = false;
        document.getElementById('btnSubmit').style.opacity = '1';
        document.getElementById('btnSubmit').style.cursor = 'pointer';
    }
}

function validerFormulaire() {
    // Vérifier une dernière fois avant l'envoi
    const btn = document.getElementById('btnSubmit');
    if (btn.disabled) {
        alert('❌ Un ou plusieurs montants dépassent la référence. Veuillez corriger les saisies.');
        return false;
    }
    
    // Vérifier que le total du jour n'est pas 0
    const vDossier = parseFloat(document.getElementById('v_dossier')?.value || 0) || 0;
    const vTechnique = parseFloat(document.getElementById('v_technique')?.value || 0) || 0;
    const vLogistique = parseFloat(document.getElementById('v_logistique')?.value || 0) || 0;
    const vMorcellement = parseFloat(document.getElementById('v_morcellement')?.value || 0) || 0;
    
    if (vDossier === 0 && vTechnique === 0 && vLogistique === 0 && vMorcellement === 0) {
        alert('⚠️ Veuillez saisir au moins un montant > 0 pour créer un bon.');
        return false;
    }
    
    return true;
}

// Initialiser la validation au chargement
document.addEventListener('DOMContentLoaded', function() {
    calculerTotal();
});
</script>
@endsection