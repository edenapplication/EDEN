@extends('admin.layout')
@section('content')

<input type="hidden" name="reference" value="{{ session('reference_utilisateur') ?? request()->query('reference') }}">

<style>
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(15,23,42,0.5); z-index: 9998;
    }
    .modal-overlay.active { display: block; }

    .modal-box {
        display: none; position: fixed; top: 50%; left: 50%;
        transform: translate(-50%, -50%); background: white;
        padding: 24px; border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        z-index: 9999; max-height: 90vh; overflow-y: auto;
    }
    .modal-box.active { display: block; }
</style>

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
        <form method="POST" action="{{ route('bons.store', $dossier->id) }}" id="formBon">
            @csrf
            <div id="versementsHidden"></div>

            {{-- DATE --}}
            <div style="background:white;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:16px;">
                <h6 style="font-weight:700;color:#1e3a5f;margin-bottom:14px;">📅 Date du bon</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date_bon" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            {{-- PARCELLE & MORCELLEMENT --}}
            <div style="background:white;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:16px;">
                <h6 style="font-weight:700;color:#1e3a5f;margin-bottom:6px;">💰 Versements directs</h6>
                <p style="font-size:12px;color:#64748b;margin-bottom:14px;">
                    Pas besoin de bénéficiaire pour ces types.
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="color:#1d4ed8;">📁 Ajouter un paiement pour la Parcelle (FCFA)</label>
                        <input type="number" name="versement_dossier" id="v_dossier"
                               class="form-control" value="0" min="0" oninput="calculerTotal()">
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">
                            Déjà versé : {{ number_format($totaux['dossier'],0,',',' ') }} FCFA
                            @if($dossier->prix_superficie > 0)
                                / Réf : {{ number_format($dossier->prix_superficie,0,',',' ') }} FCFA
                            @endif
                        </div>
                        <div id="warn-dossier" style="display:none;color:#f59e0b;font-size:11px;font-weight:600;margin-top:4px;">
                            ⚠️ Dépassement de la référence
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold" style="color:#ca8a04;">✂️ Ajouter un paiement pour le Morcellement (FCFA)</label>
                        <input type="number" name="versement_morcellement" id="v_morcellement"
                               class="form-control" value="0" min="0" oninput="calculerTotal()">
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">
                            Déjà versé : {{ number_format($totaux['morcellement'],0,',',' ') }} FCFA
                            @if($dossier->prix_morcellement > 0)
                                / Réf : {{ number_format($dossier->prix_morcellement,0,',',' ') }} FCFA
                            @endif
                        </div>
                        <div id="warn-morcellement" style="display:none;color:#f59e0b;font-size:11px;font-weight:600;margin-top:4px;">
                            ⚠️ Dépassement de la référence
                        </div>
                    </div>
                </div>
            </div>

            {{-- PAIEMENTS PAR BÉNÉFICIAIRE --}}
            <div style="background:white;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <h6 style="font-weight:700;color:#7c3aed;margin:0;">👥 Ajouter un paiement de Dossier Technique ET/OU de Logistique d'Implantation</h6>
                    <button type="button" onclick="ajouterVersementBenef()"
                            style="background:#faf5ff;color:#7c3aed;border:2px dashed #c4b5fd;
                                   padding:6px 14px;border-radius:8px;font-weight:700;
                                   font-size:12px;cursor:pointer;">
                        ➕ Ajouter un bénéficiaire
                    </button>
                </div>
                <p style="font-size:12px;color:#64748b;margin-bottom:14px;">
                    Chaque bénéficiaire peut payer <strong>un dossier technique ET/OU une logistique d'implantation</strong>.
                </p>

                <div id="versementsBenefList"></div>

                {{-- ✅ SOUS-TOTAUX (cachés par défaut) --}}
                <div id="sousTotauxBenef"
                     style="display:none;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px;">
                    <div style="background:#fff7ed;border-radius:8px;padding:10px;border-left:3px solid #ea580c;">
                        <div style="font-size:10px;color:#9a3412;font-weight:700;text-transform:uppercase;">🛠️ Total du versement pour le dossier Technique</div>
                        <div style="font-size:16px;font-weight:900;color:#ea580c;" id="total-tech-benef">0 FCFA</div>
                    </div>
                    <div style="background:#faf5ff;border-radius:8px;padding:10px;border-left:3px solid #7c3aed;">
                        <div style="font-size:10px;color:#6b21a8;font-weight:700;text-transform:uppercase;">🚗 Total du versement pour la Logistique d'implantation</div>
                        <div style="font-size:16px;font-weight:900;color:#7c3aed;" id="total-logi-benef">0 FCFA</div>
                    </div>
                </div>
            </div>

            {{-- TOTAL GÉNÉRAL --}}
            <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:2px solid #86efac;
                        border-radius:14px;padding:20px;margin-bottom:16px;
                        display:flex;justify-content:space-between;align-items:center;">
                <span style="font-weight:800;color:#15803d;font-size:14px;">💰 TOTAL DU BON :</span>
                <span style="font-size:26px;font-weight:900;color:#16a34a;" id="total-jour">0 FCFA</span>
            </div>

            {{-- AVERTISSEMENTS --}}
            <div id="warn-global"
                 style="display:none;background:#fef3c7;border:1px solid #fcd34d;
                        border-radius:10px;padding:12px 16px;margin-bottom:16px;
                        color:#92400e;font-weight:600;font-size:13px;">
                ⚠️ <span id="warn-message"></span>
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
        <div style="background:#0f172a;border-radius:14px;padding:20px;color:#cbd5e1;
                    position:sticky;top:80px;margin-bottom:16px;">
            <h6 style="color:#60a5fa;font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;">
                📋 Récap du dossier
            </h6>
            <div style="font-weight:800;color:white;font-size:14px;margin-bottom:12px;">
                {{ $dossier->client?->name }}
            </div>
            <div style="font-size:12px;color:#64748b;margin-bottom:4px;">📞 {{ $dossier->client?->phone ?? '-' }}</div>
            <div style="font-size:12px;color:#64748b;margin-bottom:16px;">🏢 {{ $dossier->grandSite?->nom ?? '-' }}</div>

            <div style="border-top:1px solid rgba(255,255,255,0.08);padding-top:12px;">
                <div style="font-size:10px;color:#475569;text-transform:uppercase;margin-bottom:8px;">Déjà versé</div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                    <span style="color:#64748b;">Parcelle</span>
                    <span style="color:#60a5fa;font-weight:700;">{{ number_format($totaux['dossier'],0,',',' ') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                    <span style="color:#64748b;">Technique</span>
                    <span style="color:#fb923c;font-weight:700;">{{ number_format($totaux['technique'],0,',',' ') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                    <span style="color:#64748b;">Logistique</span>
                    <span style="color:#a78bfa;font-weight:700;">{{ number_format($totaux['logistique'],0,',',' ') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;">
                    <span style="color:#64748b;">Morcellement</span>
                    <span style="color:#fbbf24;font-weight:700;">{{ number_format($totaux['morcellement'],0,',',' ') }}</span>
                </div>
            </div>
        </div>

        {{-- HISTORIQUE BÉNÉFICIAIRES --}}
        <div style="background:white;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
            <h6 style="color:#7c3aed;font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;font-weight:700;">
                👥 Historique par bénéficiaire
            </h6>

            @forelse($dossier->beneficiaires as $benef)
                @php
                    $payeTechB = \App\Models\BeneficiairePaiement::where('beneficiaire_id', $benef->id)
                        ->where('paiement_type', 'technique')->sum('montant');
                    $payeLogiB = \App\Models\BeneficiairePaiement::where('beneficiaire_id', $benef->id)
                        ->where('paiement_type', 'logistique')->sum('montant');
                    $totalB = $payeTechB + $payeLogiB;
                @endphp

                <div style="background:#faf5ff;border-radius:8px;padding:12px;margin-bottom:8px;border-left:3px solid #a855f7;">
                    <div style="font-weight:800;color:#6b21a8;font-size:13px;">👤 {{ $benef->nom }}</div>
                    @if($benef->telephone)
                        <div style="font-size:11px;color:#94a3b8;margin-top:2px;">📞 {{ $benef->telephone }}</div>
                    @endif

                    @if($totalB > 0)
                        <div style="margin-top:8px;padding-top:8px;border-top:1px solid #e9d5ff;">
                            @if($payeTechB > 0)
                                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:3px;">
                                    <span style="color:#64748b;">🛠️ Technique</span>
                                    <span style="color:#16a34a;font-weight:800;">{{ number_format($payeTechB,0,',',' ') }}</span>
                                </div>
                            @endif
                            @if($payeLogiB > 0)
                                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:3px;">
                                    <span style="color:#64748b;">🚗 Logistique</span>
                                    <span style="color:#16a34a;font-weight:800;">{{ number_format($payeLogiB,0,',',' ') }}</span>
                                </div>
                            @endif
                            <div style="display:flex;justify-content:space-between;font-size:13px;margin-top:6px;padding-top:6px;border-top:1px dashed #e9d5ff;">
                                <span style="color:#6b21a8;font-weight:700;">Total</span>
                                <span style="color:#7c3aed;font-weight:900;">{{ number_format($totalB,0,',',' ') }}</span>
                            </div>
                        </div>
                    @else
                        <div style="font-size:11px;color:#94a3b8;margin-top:6px;font-style:italic;">Aucun paiement</div>
                    @endif
                </div>
            @empty
                <div style="text-align:center;padding:20px;color:#94a3b8;">
                    <div style="font-size:32px;">👥</div>
                    <div style="font-weight:700;margin-top:8px;font-size:13px;">Aucun bénéficiaire</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODAL BÉNÉFICIAIRE --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="benefOverlay" onclick="fermerModalBenef()"></div>
<div class="modal-box" id="benefModal" style="width:550px; max-width:95%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;" id="benefTitre">👥 Ajouter un bénéficiaire</h5>
        <button onclick="fermerModalBenef()"
                style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>

    <input type="hidden" id="benefId">
    <input type="hidden" id="benefDossierId">

    <div style="display:grid;gap:10px;">
        <div id="benefClientWrapper">
            <label style="font-size:11px;font-weight:700;color:#64748b;">
                🔗 Utiliser le client du dossier comme bénéficiaire
            </label>
            <select id="benefClientId" class="form-control form-control-sm"
                    onchange="prefillBenefFromClient(this)">
                <option value="">-- Nouveau bénéficiaire --</option>
                @if($dossier->client)
                    <option value="{{ $dossier->client->id }}"
                            data-nom="{{ $dossier->client->name }}"
                            data-phone="{{ $dossier->client->phone }}"
                            data-sexe="{{ $dossier->client->sexe }}">
                        👤 {{ $dossier->client->name }} ({{ $dossier->client->phone }})
                    </option>
                @endif
            </select>
            <div id="benefClientInfo" style="font-size:10px;color:#7c3aed;margin-top:4px;"></div>
        </div>

        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">Nom et prénom(s) *</label>
            <input type="text" id="benefNom" class="form-control form-control-sm">
        </div>

        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">Téléphone</label>
            <input type="text" id="benefTelephone" class="form-control form-control-sm">
        </div>

        {{-- ✅ SEXE — id corrigé en benefSexe --}}
        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">Sexe</label>
            <select name="sexe" id="benefSexe" class="form-control form-control-sm">
                <option value="">— Non renseigné —</option>
                <option value="masculin">👨 Masculin</option>
                <option value="feminin">👩 Féminin</option>
            </select>
        </div>

        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">
                CNI (obligatoire) <span style="color:#dc2626;">*</span>
            </label>
            <input type="file" id="benefCni" class="form-control form-control-sm"
                   accept="image/*,application/pdf">
            <div id="benefCniActuelle" style="font-size:10px;margin-top:4px;"></div>
            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                📎 Formats acceptés : JPG, PNG, PDF (max 10 Mo)
            </div>
        </div>
    </div>

    <div id="benefMessage"
         style="display:none;padding:10px 14px;border-radius:8px;
                font-size:13px;margin-top:12px;font-weight:600;"></div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button onclick="fermerModalBenef()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="sauvegarderBenef()" class="btn btn-sm" id="btnSaveBenef"
                style="background:#7c3aed;color:white;font-weight:600;">
            💾 Enregistrer
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ═══════════════════════════════════════════════════════════
// DONNÉES
// ═══════════════════════════════════════════════════════════
const CSRF_TOKEN  = '{{ csrf_token() }}';
const DOSSIER_ID  = {{ $dossier->id }};
const CLIENT_ID   = {{ $dossier->client?->id ?? 0 }};
const CLIENT_NOM  = '{{ addslashes($dossier->client?->name ?? '') }}';
const CLIENT_TEL  = '{{ addslashes($dossier->client?->phone ?? '') }}';
const CLIENT_SEXE = '{{ $dossier->client?->sexe ?? '' }}';   // ✅ AJOUT

const prixReferences = {
    dossier:      {{ $dossier->prix_superficie ?? 0 }},
    technique:    {{ $dossier->prix_technique ?? 0 }},
    logistique:   {{ $dossier->prix_logistique ?? 0 }},
    morcellement: {{ $dossier->prix_morcellement ?? 0 }}
};

const totauxDejaVerses = {
    dossier:      {{ $totaux['dossier'] ?? 0 }},
    technique:    {{ $totaux['technique'] ?? 0 }},
    logistique:   {{ $totaux['logistique'] ?? 0 }},
    morcellement: {{ $totaux['morcellement'] ?? 0 }}
};

// ✅ Récupérer les bénéficiaires existants AVEC leur sexe
let beneficiaires = @json($beneficiairesJson);
// ✅ Ajouter le client/détenteur du dossier en PREMIER dans la liste
const clientDetenteur = {
    id: CLIENT_ID,
    nom: CLIENT_NOM,
    telephone: CLIENT_TEL,
    sexe: CLIENT_SEXE,                                    // ✅ AJOUT
    isClient: true,
    isBeneficiaire: beneficiaires.some(b => b.id === CLIENT_ID)
};

// Insérer le client en premier s'il existe et n'est pas déjà dans la liste
if (clientDetenteur.id && !beneficiaires.some(b => b.id === clientDetenteur.id)) {
    beneficiaires.unshift(clientDetenteur);
}

let versementCounter = 0;
let currentVersementIdForNewBenef = null;

// ═══════════════════════════════════════════════════════════
// ✅ CRÉER AUTOMATIQUEMENT LE BÉNÉFICIAIRE SI C'EST LE CLIENT
// ═══════════════════════════════════════════════════════════
function autoCreerBeneficiaireSiClient(benefId, callback) {
    // Si l'ID correspond au client détenteur ET qu'il n'est pas encore bénéficiaire
    if (benefId == CLIENT_ID && !clientDetenteur.isBeneficiaire) {
        console.log('🔄 Création automatique du bénéficiaire pour le client:', CLIENT_NOM);

        const formData = new FormData();
        formData.append('nom', CLIENT_NOM);
        formData.append('telephone', CLIENT_TEL);
        if (CLIENT_SEXE) formData.append('sexe', CLIENT_SEXE);   // ✅ AJOUT
        formData.append('client_id', CLIENT_ID);

        fetch(`/admin/dossiers/${DOSSIER_ID}/beneficiaires`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Bénéficiaire créé automatiquement:', data.beneficiaire);

                // Mettre à jour la liste locale
                clientDetenteur.isBeneficiaire = true;
                clientDetenteur.beneficiaire_id = data.beneficiaire.id;

                // Mettre à jour l'ID du client avec le vrai ID bénéficiaire
                beneficiaires = beneficiaires.map(b => {
                    if (b.id == CLIENT_ID) {
                        return {
                            ...b,
                            beneficiaire_id: data.beneficiaire.id,
                        };
                    }
                    return b;
                });

                if (callback) callback(data.beneficiaire.id, true);
            } else {
                console.error('❌ Erreur création auto:', data.message);
                if (callback) callback(null, false, data.message);
            }
        })
        .catch(err => {
            console.error('❌ Erreur réseau:', err);
            if (callback) callback(null, false, err.message);
        });
    } else {
        if (callback) callback(benefId, true);
    }
}

// ═══════════════════════════════════════════════════════════
// AJOUTER UN VERSEMENT BÉNÉFICIAIRE
// ═══════════════════════════════════════════════════════════
function ajouterVersementBenef() {
    versementCounter++;
    const id = versementCounter;

    const sousTotaux = document.getElementById('sousTotauxBenef');
    if (sousTotaux) sousTotaux.style.display = 'grid';

    const optionsBenef = beneficiaires.map(b =>
        `<option value="${b.id}">${b.isClient ? '⭐ ' : ''}${b.nom}${b.telephone ? ' — ' + b.telephone : ''}${b.isClient ? ' (Détenteur)' : ''}</option>`
    ).join('');

    const html = `
        <div class="versement-benef" data-id="${id}"
             style="background:#faf5ff;border:2px solid #e9d5ff;border-radius:10px;
                    padding:14px;margin-bottom:10px;">

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <span style="font-weight:800;color:#7c3aed;font-size:12px;">VERSEMENT #${id}</span>
                <button type="button" onclick="supprimerVersementBenef(${id})"
                        style="background:#fee2e2;color:#dc2626;border:none;
                               padding:4px 10px;border-radius:6px;font-weight:700;
                               font-size:11px;cursor:pointer;">✖</button>
            </div>

            {{-- Bénéficiaire --}}
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:700;color:#64748b;">
                    👤 Bénéficiaire <span style="color:#dc2626;">*</span>
                </label>
                <select class="form-control form-control-sm benef-select"
                        data-id="${id}"
                        style="margin-top:4px;"
                        onchange="onBenefSelectChange(${id})">
                    <option value="">— Choisir un bénéficiaire —</option>
                    ${optionsBenef}
                    <option value="__NEW__"
                            style="font-weight:800;color:#7c3aed;background:#faf5ff;">
                        ➕ Créer un nouveau bénéficiaire...
                    </option>
                </select>

                <div class="new-benef-indicator" data-id="${id}"
                     style="display:none;margin-top:8px;padding:10px 12px;
                            background:linear-gradient(135deg,#faf5ff,#f3e8ff);
                            border:2px dashed #c4b5fd;border-radius:8px;
                            font-size:12px;color:#7c3aed;font-weight:700;
                            align-items:center;gap:8px;">
                    <span style="font-size:18px;">📝</span>
                    <div style="flex:1;">
                        <div>Nouveau bénéficiaire à créer</div>
                        <div class="new-benef-name-preview"
                             data-id="${id}"
                             style="font-size:11px;color:#6b21a8;font-weight:600;margin-top:2px;">
                            (Aucun nom saisi)
                        </div>
                    </div>
                    <button type="button"
                            onclick="ouvrirModalBenefPourVersement(${id})"
                            style="background:#7c3aed;color:white;border:none;
                                   padding:6px 12px;border-radius:6px;font-weight:700;
                                   font-size:11px;cursor:pointer;">
                        ✏️ Saisir les infos
                    </button>
                </div>
            </div>

            {{-- Type de paiement --}}
            <div style="background:white;border-radius:8px;padding:10px;margin-bottom:10px;">
                <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:8px;">
                    💰 Sélectionner le type de paiement effectué
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div style="background:#fff7ed;border:2px solid #fed7aa;border-radius:8px;padding:8px;">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin-bottom:8px;">
                            <input type="checkbox" class="tech-checkbox" data-id="${id}"
                                   onchange="onTypeChange(${id})">
                            <span style="font-weight:700;color:#ea580c;font-size:12px;">🛠️ Paiement pour le dossier Technique</span>
                        </label>

                        <div class="tech-montant-block" data-id="${id}" style="display:none;">
                            <input type="number" class="form-control form-control-sm tech-montant"
                                   data-id="${id}" min="0" value="0"
                                   placeholder="Montant FCFA"
                                   oninput="calculerTotal()">
                        </div>
                    </div>

                    <div style="background:#faf5ff;border:2px solid #e9d5ff;border-radius:8px;padding:8px;">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin-bottom:8px;">
                            <input type="checkbox" class="logi-checkbox" data-id="${id}"
                                   onchange="onTypeChange(${id})">
                            <span style="font-weight:700;color:#7c3aed;font-size:12px;">🚗 Paiement pour la Logistique d'implantation</span>
                        </label>

                        <div class="logi-montant-block" data-id="${id}" style="display:none;">
                            <input type="number" class="form-control form-control-sm logi-montant"
                                   data-id="${id}" min="0" value="0"
                                   placeholder="Montant FCFA"
                                   oninput="calculerTotal()">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('versementsBenefList').insertAdjacentHTML('beforeend', html);
    calculerTotal();
}

function supprimerVersementBenef(id) {
    const el = document.querySelector(`.versement-benef[data-id="${id}"]`);
    if (el) {
        el.remove();
        calculerTotal();

        const restants = document.querySelectorAll('.versement-benef').length;
        const sousTotaux = document.getElementById('sousTotauxBenef');
        if (sousTotaux && restants === 0) {
            sousTotaux.style.display = 'none';
        }
    }
}

function onTypeChange(id) {
    const techCb = document.querySelector(`.tech-checkbox[data-id="${id}"]`);
    const logiCb = document.querySelector(`.logi-checkbox[data-id="${id}"]`);

    const techBlock = document.querySelector(`.tech-montant-block[data-id="${id}"]`);
    const logiBlock = document.querySelector(`.logi-montant-block[data-id="${id}"]`);

    if (techBlock) techBlock.style.display = techCb?.checked ? 'block' : 'none';
    if (logiBlock) logiBlock.style.display = logiCb?.checked ? 'block' : 'none';

    calculerTotal();
}

// ═══════════════════════════════════════════════════════════
// DÉTECTION SELECT "NOUVEAU BÉNÉFICIAIRE"
// ═══════════════════════════════════════════════════════════
function onBenefSelectChange(id) {
    const select = document.querySelector(`.benef-select[data-id="${id}"]`);
    const indicator = document.querySelector(`.new-benef-indicator[data-id="${id}"]`);

    if (!select || !indicator) return;

    if (select.value === '__NEW__') {
        indicator.style.display = 'flex';
        ouvrirModalBenefPourVersement(id);
    } else {
        indicator.style.display = 'none';
    }
}

// ═══════════════════════════════════════════════════════════
// MODAL CRÉATION BÉNÉFICIAIRE
// ═══════════════════════════════════════════════════════════
function ouvrirModalBenefPourVersement(versementId) {
    currentVersementIdForNewBenef = versementId;

    document.getElementById('benefId').value = '';
    document.getElementById('benefDossierId').value = DOSSIER_ID;
    document.getElementById('benefNom').value = '';
    document.getElementById('benefTelephone').value = '';

    // ✅ SEXE — Réinitialiser
    const sexeSelect = document.getElementById('benefSexe');
    if (sexeSelect) {
        sexeSelect.value = '';
    }

    document.getElementById('benefCni').value = '';
    document.getElementById('benefCniActuelle').innerHTML = '';
    document.getElementById('benefMessage').style.display = 'none';
    document.getElementById('benefClientId').value = '';
    document.getElementById('benefClientInfo').textContent = '';
    document.getElementById('benefTitre').textContent = '👥 Ajouter un bénéficiaire';
    document.getElementById('btnSaveBenef').disabled = false;
    document.getElementById('btnSaveBenef').innerHTML = '💾 Enregistrer';

    const inputNom = document.getElementById('benefNom');
    inputNom.oninput = function() {
        const preview = document.querySelector(`.new-benef-name-preview[data-id="${versementId}"]`);
        if (preview) {
            preview.textContent = this.value.trim() || '(Aucun nom saisi)';
        }
    };

    document.getElementById('benefOverlay').classList.add('active');
    document.getElementById('benefModal').classList.add('active');
    setTimeout(() => inputNom.focus(), 100);
}

function fermerModalBenef() {
    document.getElementById('benefOverlay').classList.remove('active');
    document.getElementById('benefModal').classList.remove('active');

    if (currentVersementIdForNewBenef) {
        const select = document.querySelector(`.benef-select[data-id="${currentVersementIdForNewBenef}"]`);
        const indicator = document.querySelector(`.new-benef-indicator[data-id="${currentVersementIdForNewBenef}"]`);

        if (select && select.value === '__NEW__') {
            select.value = '';
            if (indicator) indicator.style.display = 'none';
        }
    }

    currentVersementIdForNewBenef = null;
}

function prefillBenefFromClient(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt.value) {
        document.getElementById('benefClientInfo').textContent = '';
        document.getElementById('benefSexe').value = '';
        return;
    }
    document.getElementById('benefNom').value = opt.dataset.nom || '';
    document.getElementById('benefTelephone').value = opt.dataset.phone || '';
    const sexe = opt.dataset.sexe || '';

    // ✅ Pré-remplir le select avec le sexe du client
    const sexeSelect = document.getElementById('benefSexe');
    if (sexeSelect) sexeSelect.value = sexe;

    document.getElementById('benefClientInfo').textContent =
        sexe ? '👤 Sexe : ' + (sexe === 'masculin' ? 'Masculin' : 'Féminin') : '⚠️ Sexe non renseigné';

    if (currentVersementIdForNewBenef) {
        const preview = document.querySelector(`.new-benef-name-preview[data-id="${currentVersementIdForNewBenef}"]`);
        if (preview) preview.textContent = opt.dataset.nom || '(Aucun nom saisi)';
    }
}

function sauvegarderBenef() {
    const nom = document.getElementById('benefNom').value.trim();
    const cniFile = document.getElementById('benefCni').files[0];
    const clientId = document.getElementById('benefClientId')?.value || '';

    if (!nom) {
        afficherMessageBenef('⚠️ Le nom est obligatoire', 'error');
        return;
    }

    if (!clientId && !cniFile) {
        afficherMessageBenef('⚠️ La CNI est obligatoire', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('nom', nom);
    formData.append('telephone', document.getElementById('benefTelephone').value);
    if (cniFile) formData.append('cni', cniFile);
    if (clientId) formData.append('client_id', clientId);
    formData.append('sexe', document.getElementById('benefSexe')?.value || '');

    const btn = document.getElementById('btnSaveBenef');
    btn.disabled = true;
    btn.innerHTML = '⏳ Enregistrement...';

    fetch(`/admin/dossiers/${DOSSIER_ID}/beneficiaires`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '💾 Enregistrer';

        if (data.success) {
            beneficiaires.push({
                id: data.beneficiaire.id,
                nom: data.beneficiaire.nom,
                telephone: data.beneficiaire.telephone,
                sexe: data.beneficiaire.sexe,   // ✅ AJOUT
            });

            if (clientId == CLIENT_ID) {
                clientDetenteur.isBeneficiaire = true;
                clientDetenteur.beneficiaire_id = data.beneficiaire.id;
            }

            document.querySelectorAll('.benef-select').forEach(select => {
                const opt = document.createElement('option');
                opt.value = data.beneficiaire.id;
                opt.textContent = data.beneficiaire.nom +
                    (data.beneficiaire.telephone ? ' — ' + data.beneficiaire.telephone : '');

                const newOption = select.querySelector('option[value="__NEW__"]');
                if (newOption) {
                    select.insertBefore(opt, newOption);
                } else {
                    select.appendChild(opt);
                }
            });

            if (currentVersementIdForNewBenef) {
                const sel = document.querySelector(`.benef-select[data-id="${currentVersementIdForNewBenef}"]`);
                if (sel) {
                    sel.value = data.beneficiaire.id;

                    const indicator = document.querySelector(`.new-benef-indicator[data-id="${currentVersementIdForNewBenef}"]`);
                    if (indicator) indicator.style.display = 'none';

                    const preview = document.querySelector(`.new-benef-name-preview[data-id="${currentVersementIdForNewBenef}"]`);
                    if (preview) preview.textContent = data.beneficiaire.nom;
                }
            }

            afficherMessageBenef('✅ Bénéficiaire créé : ' + data.beneficiaire.nom, 'success');
            setTimeout(() => {
                document.getElementById('benefOverlay').classList.remove('active');
                document.getElementById('benefModal').classList.remove('active');
                currentVersementIdForNewBenef = null;
            }, 1000);
        } else {
            let msg = data.message || 'Erreur';
            if (data.errors) msg = Object.values(data.errors).flat().join('\n');
            afficherMessageBenef('❌ ' + msg, 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '💾 Enregistrer';
        afficherMessageBenef('❌ Erreur : ' + err.message, 'error');
    });
}

function afficherMessageBenef(msg, type) {
    const box = document.getElementById('benefMessage');
    box.style.display = 'block';
    box.textContent = msg;
    box.style.background = type === 'success' ? '#dcfce7' : '#fee2e2';
    box.style.color      = type === 'success' ? '#15803d' : '#b91c1c';
    box.style.border     = type === 'success' ? '1px solid #86efac' : '1px solid #fecaca';
}

// ═══════════════════════════════════════════════════════════
// CALCUL DU TOTAL
// ═══════════════════════════════════════════════════════════
function calculerTotal() {
    const vDossier      = parseFloat(document.getElementById('v_dossier')?.value || 0) || 0;
    const vMorcellement = parseFloat(document.getElementById('v_morcellement')?.value || 0) || 0;

    let totalTechBenef = 0;
    let totalLogiBenef = 0;

    document.querySelectorAll('.versement-benef').forEach(item => {
        const id = item.dataset.id;
        const techCb = item.querySelector(`.tech-checkbox[data-id="${id}"]`);
        const logiCb = item.querySelector(`.logi-checkbox[data-id="${id}"]`);

        if (techCb?.checked) {
            totalTechBenef += parseFloat(item.querySelector('.tech-montant')?.value || 0) || 0;
        }
        if (logiCb?.checked) {
            totalLogiBenef += parseFloat(item.querySelector('.logi-montant')?.value || 0) || 0;
        }
    });

    const totalJour = vDossier + vMorcellement + totalTechBenef + totalLogiBenef;

    document.getElementById('total-jour').innerText = Math.round(totalJour).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('total-tech-benef').innerText = Math.round(totalTechBenef).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('total-logi-benef').innerText = Math.round(totalLogiBenef).toLocaleString('fr-FR') + ' FCFA';

    let warnings = [];

    if (prixReferences.dossier > 0) {
        const total = totauxDejaVerses.dossier + vDossier;
        document.getElementById('warn-dossier').style.display =
            (total > prixReferences.dossier) ? 'block' : 'none';
        if (total > prixReferences.dossier) {
            warnings.push(`📁 Parcelle dépasse de ${(total - prixReferences.dossier).toLocaleString('fr-FR')} FCFA`);
        }
    }

    if (prixReferences.morcellement > 0) {
        const total = totauxDejaVerses.morcellement + vMorcellement;
        document.getElementById('warn-morcellement').style.display =
            (total > prixReferences.morcellement) ? 'block' : 'none';
        if (total > prixReferences.morcellement) {
            warnings.push(`✂️ Morcellement dépasse de ${(total - prixReferences.morcellement).toLocaleString('fr-FR')} FCFA`);
        }
    }

    if (prixReferences.technique > 0 && totalTechBenef > 0) {
        const total = totauxDejaVerses.technique + totalTechBenef;
        if (total > prixReferences.technique) {
            warnings.push(`🛠️ Technique dépasse de ${(total - prixReferences.technique).toLocaleString('fr-FR')} FCFA`);
        }
    }

    if (prixReferences.logistique > 0 && totalLogiBenef > 0) {
        const total = totauxDejaVerses.logistique + totalLogiBenef;
        if (total > prixReferences.logistique) {
            warnings.push(`🚗 Logistique dépasse de ${(total - prixReferences.logistique).toLocaleString('fr-FR')} FCFA`);
        }
    }

    const warnDiv = document.getElementById('warn-global');
    if (warnings.length > 0) {
        warnDiv.style.display = 'block';
        document.getElementById('warn-message').innerHTML =
            'Dépassement de référence (autorisé) :<br>' + warnings.join('<br>');
    } else {
        warnDiv.style.display = 'none';
    }
}

// ═══════════════════════════════════════════════════════════
// ✅ SOUMISSION AVEC CRÉATION AUTOMATIQUE DU BÉNÉFICIAIRE
// ═══════════════════════════════════════════════════════════
document.getElementById('formBon').addEventListener('submit', function(e) {
    e.preventDefault();

    const versements = [];
    let hasErreur = false;
    let total = 0;
    let promesses = [];

    document.querySelectorAll('.versement-benef').forEach((item) => {
        const id = item.dataset.id;
        const benefId = item.querySelector('.benef-select')?.value;
        const techCb = item.querySelector(`.tech-checkbox[data-id="${id}"]`);
        const logiCb = item.querySelector(`.logi-checkbox[data-id="${id}"]`);

        const techMontant = parseFloat(item.querySelector('.tech-montant')?.value || 0) || 0;
        const logiMontant = parseFloat(item.querySelector('.logi-montant')?.value || 0) || 0;

        const hasTech = techCb?.checked && techMontant > 0;
        const hasLogi = logiCb?.checked && logiMontant > 0;

        if (!hasTech && !hasLogi) return;

        if (!benefId || benefId === '__NEW__') {
            alert(`⚠️ Versement #${id} : choisissez ou créez un bénéficiaire.`);
            hasErreur = true;
            return;
        }

        if (benefId == CLIENT_ID && !clientDetenteur.isBeneficiaire) {
            promesses.push(
                new Promise((resolve) => {
                    autoCreerBeneficiaireSiClient(benefId, (nouveauBenefId, ok, msg) => {
                        if (ok) {
                            if (hasTech) {
                                versements.push({
                                    beneficiaire_id: nouveauBenefId,
                                    type: 'technique',
                                    montant: techMontant,
                                });
                                total += techMontant;
                            }
                            if (hasLogi) {
                                versements.push({
                                    beneficiaire_id: nouveauBenefId,
                                    type: 'logistique',
                                    montant: logiMontant,
                                });
                                total += logiMontant;
                            }
                        } else {
                            alert('❌ Impossible de créer le bénéficiaire : ' + (msg || 'Erreur inconnue'));
                            hasErreur = true;
                        }
                        resolve();
                    });
                })
            );
        } else {
            if (hasTech) {
                versements.push({
                    beneficiaire_id: benefId,
                    type: 'technique',
                    montant: techMontant,
                });
                total += techMontant;
            }
            if (hasLogi) {
                versements.push({
                    beneficiaire_id: benefId,
                    type: 'logistique',
                    montant: logiMontant,
                });
                total += logiMontant;
            }
        }
    });

    Promise.all(promesses).then(() => {
        if (hasErreur) return;

        const vDossier = parseFloat(document.getElementById('v_dossier')?.value || 0) || 0;
        const vMorc = parseFloat(document.getElementById('v_morcellement')?.value || 0) || 0;
        total += vDossier + vMorc;

        if (total <= 0) {
            alert('⚠️ Veuillez saisir au moins un montant > 0.');
            return;
        }

        const container = document.getElementById('versementsHidden');
        container.innerHTML = '';

        versements.forEach((v, i) => {
            for (const key in v) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `versements[${i}][${key}]`;
                input.value = v[key];
                container.appendChild(input);
            }
        });

        document.getElementById('formBon').submit();
    });
});

// ═══════════════════════════════════════════════════════════
// INITIALISATION
// ═══════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
    const sousTotaux = document.getElementById('sousTotauxBenef');
    if (sousTotaux) sousTotaux.style.display = 'none';

    calculerTotal();
});
</script>
@endsection