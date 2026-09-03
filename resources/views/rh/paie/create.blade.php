@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
.calc-box { background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px; padding:16px; margin-top:16px; }
.calc-row { display:flex; justify-content:space-between; padding:5px 0; font-size:13px; border-bottom:1px solid #e2e8f0; }
.calc-row:last-child { border:none; }
.calc-total { font-size:16px; font-weight:800; color:#1d4ed8; padding-top:8px; border-top:2px solid #1d4ed8; }
.calc-subtotal { font-size:12px; font-weight:700; color:#16a34a; padding-top:6px; border-top:1px solid #e2e8f0; }
.calc-cnps { background:#fef9c3; border-radius:4px; padding:6px 8px; margin:4px 0; }

#employe_list {
    position: absolute;
    width: 100%;
    z-index: 9999;
    background: white;
    border-radius: 10px;
    margin-top: 4px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
    max-height: 260px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
}

.emp-item {
    padding: 10px 12px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.15s ease;
}
.emp-item:hover { background:#f0f9ff; transform:translateX(2px); }
.emp-name { font-weight:600; color:#1e3a5f; font-size:14px; }
.emp-meta { font-size:12px; color:#64748b; margin-top:2px; }
.emp-item:last-child { border-bottom:none; }

.badge-cnps { padding:2px 8px; border-radius:4px; font-size:10px; font-weight:600; }
.badge-cnps.salariale { background:#fee2e2; color:#dc2626; }
.badge-cnps.patronale { background:#ede9fe; color:#7c3aed; }
.badge-cnps.total { background:#dbeafe; color:#1d4ed8; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;">💰 Nouveau bulletin de paie</h2>
        <p style="color:#94a3b8;font-size:13px;margin:0;">Remplissez les champs ci-dessous pour générer le bulletin</p>
    </div>
    <a href="{{ route('rh.paie.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<form method="POST" action="{{ route('rh.paie.store') }}" id="paieForm">
@csrf

<div class="row g-3">
    <div class="col-md-8">

        {{-- EMPLOYÉ & PÉRIODE --}}
        <div class="form-section">
            <h5>👤 Employé & Période</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Employé <span class="text-danger">*</span></label>
                    <input type="text" id="search_employe" class="form-control" placeholder="🔍 Rechercher un employé...">
                    <input type="hidden" name="employe_id" id="employe_id">
                    <div id="employe_list" style="display:none;"></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Période <span class="text-danger">*</span></label>
                    <input type="month" name="periode" class="form-control" required value="{{ now()->format('Y-m') }}" onchange="calculer()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Vague <span class="text-danger">*</span></label>
                    <select name="vague" class="form-control" required>
                        <option value="VAGUE 1">VAGUE 1</option>
                        <option value="VAGUE 2">VAGUE 2</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Date de paiement <span class="text-danger">*</span></label>
                    <input type="date" name="date_paiement" class="form-control" required value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Contrat associé</label>
                    <input type="text" id="contrat_info" class="form-control" readonly style="background:#f8fafc;font-weight:600;color:#1d4ed8;" placeholder="Sélectionner un employé">
                </div>
            </div>
        </div>

        {{-- ÉLÉMENTS POSITIFS --}}
        <div class="form-section">
            <h5>✅ Éléments du salaire</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Salaire brut (FCFA)</label>
                    <input type="number" name="salaire_brut" id="salaire_brut" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Salaire/heure (auto)</label>
                    <input type="number" name="salaire_heure" id="salaire_heure" class="form-control" value="0" readonly style="background:#f8fafc;">
                    <small class="text-muted">Base 173,33h/mois</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Montant fixe (FCFA)</label>
                    <input type="number" name="montant_fixe" id="montant_fixe" class="form-control" value="1000" min="0" oninput="calculer()">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Prime (FCFA)</label>
                    <input type="number" name="prime" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Indemnité (FCFA)</label>
                    <input type="number" name="indemnite" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Heures supplémentaires</label>
                    <input type="number" name="nb_heures_sup" id="nb_heures_sup" class="form-control" value="0" min="0" step="0.5" oninput="calculer()">
                    <small class="text-muted">Montant calculé auto</small>
                </div>
            </div>
        </div>

        {{-- DÉDUCTIONS --}}
        <div class="form-section">
            <h5>❌ Déductions</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Nb retards</label>
                    <input type="number" name="nb_retards" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Montant retards (FCFA)</label>
                    <input type="number" name="montant_retard" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Nb absences (jours)</label>
                    <input type="number" name="nb_absences" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Montant absences (FCFA)</label>
                    <input type="number" name="montant_absence" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Acompte (FCFA)</label>
                    <input type="number" name="acompte" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Prêt (FCFA)</label>
                    <input type="number" name="pret" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Durée prêt (mois)</label>
                    <input type="number" name="duree_pret" class="form-control" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Sanction (FCFA)</label>
                    <input type="number" name="montant_sanction" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Motif sanction</label>
                    <input type="text" name="motif_sanction" class="form-control" placeholder="Ex: Absence non justifiée">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Imputation salaire (FCFA)</label>
                    <input type="number" name="imputation_salaire" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Frais bancaires (FCFA)</label>
                    <input type="number" name="frais_bancaires" class="form-control" value="0" min="0" oninput="calculer()">
                </div>
            </div>
        </div>

        {{-- OBSERVATION --}}
        <div class="form-section">
            <h5>📝 Observation</h5>
            <textarea name="observation" class="form-control" rows="2" placeholder="Remarque optionnelle..."></textarea>
        </div>

    </div>

    {{-- PANNEAU DE CALCUL EN TEMPS RÉEL --}}
    <div class="col-md-4">
        <div style="position:sticky; top:20px;">
            <div class="calc-box">
                <h5 style="font-weight:700; color:#1e3a5f; margin-bottom:12px;">🧮 Calcul en temps réel</h5>

                <div style="font-size:11px; font-weight:700; color:#16a34a; text-transform:uppercase; margin-bottom:6px;">Éléments positifs</div>
                <div class="calc-row"><span>Salaire brut</span><span id="c-brut">0 FCFA</span></div>
                <div class="calc-row"><span>Heures sup.</span><span id="c-hs">0 FCFA</span></div>
                <div class="calc-row"><span>Prime</span><span id="c-prime">0 FCFA</span></div>
                <div class="calc-row"><span>Indemnité</span><span id="c-ind">0 FCFA</span></div>
                <div class="calc-row"><span>Fixe</span><span id="c-fixe">0 FCFA</span></div>
                <div class="calc-row" style="font-weight:700;border-bottom:2px solid #16a34a;padding-bottom:6px;">
                    <span>= Total brut</span>
                    <span id="c-total-brut" style="color:#16a34a;font-size:15px;">0 FCFA</span>
                </div>

                {{-- ============================================================ --}}
                {{-- SECTION CNPS COMMENTÉE --}}
                {{-- ============================================================ --}}
                {{-- 
                <div style="font-size:11px; font-weight:700; color:#7c3aed; text-transform:uppercase; margin:10px 0 6px;">🏛️ Cotisations CNPS</div>
                <div class="calc-row calc-cnps">
                    <span>Base CNPS</span>
                    <span id="c-base-cnps" style="font-weight:600;color:#1e3a5f;">0 FCFA</span>
                </div>
                <div class="calc-row calc-cnps">
                    <span><span class="badge-cnps salariale">Salariale 2.52%</span></span>
                    <span id="c-cnps-salariale" style="color:#dc2626;font-weight:600;">0 FCFA</span>
                </div>
                <div class="calc-row calc-cnps">
                    <span><span class="badge-cnps patronale">Patronale 4.20%</span></span>
                    <span id="c-cnps-patronale" style="color:#7c3aed;font-weight:600;">0 FCFA</span>
                </div>
                <div class="calc-row calc-cnps" style="border-bottom:2px solid #7c3aed;padding-bottom:6px;">
                    <span><span class="badge-cnps total">Total CNPS 6.72%</span></span>
                    <span id="c-total-cnps" style="color:#1d4ed8;font-weight:700;font-size:14px;">0 FCFA</span>
                </div>
                --}}
                {{-- ============================================================ --}}
                {{-- FIN SECTION CNPS COMMENTÉE --}}
                {{-- ============================================================ --}}

                <div style="font-size:11px; font-weight:700; color:#dc2626; text-transform:uppercase; margin:10px 0 6px;">Déductions</div>
                <div class="calc-row"><span>Retards</span><span id="c-retard">0 FCFA</span></div>
                <div class="calc-row"><span>Absences</span><span id="c-absence">0 FCFA</span></div>
                <div class="calc-row"><span>Acompte</span><span id="c-acompte">0 FCFA</span></div>
                <div class="calc-row"><span>Prêt</span><span id="c-pret">0 FCFA</span></div>
                <div class="calc-row"><span>Sanction</span><span id="c-sanction">0 FCFA</span></div>
                <div class="calc-row"><span>Imputation</span><span id="c-imputation">0 FCFA</span></div>
                <div class="calc-row"><span>Frais bancaires</span><span id="c-frais">0 FCFA</span></div>
                <div class="calc-row" style="font-weight:700;border-bottom:2px solid #dc2626;padding-bottom:6px;">
                    <span>= Total déductions</span>
                    <span id="c-total-ded" style="color:#dc2626;font-size:15px;">0 FCFA</span>
                </div>

                <div class="calc-row calc-total" style="margin-top:8px;">
                    <span style="font-size:16px;">NET À PAYER</span>
                    <span id="c-net" style="font-size:20px;color:#16a34a;">0 FCFA</span>
                </div>

                <input type="hidden" name="net_a_payer" id="net_a_payer" value="0">
                <input type="hidden" name="montant_heures_sup" id="montant_heures_sup" value="0">
                {{-- CHAMPS CNPS COMMENTÉS --}}
                {{-- <input type="hidden" name="cnps_salariale" id="cnps_salariale" value="0"> --}}
                {{-- <input type="hidden" name="cnps_patronale" id="cnps_patronale" value="0"> --}}
                {{-- <input type="hidden" name="base_cnps" id="base_cnps" value="0"> --}}
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('rh.paie.index') }}" class="btn btn-light flex-fill"><i class="bi bi-x-lg"></i> Annuler</a>
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-save"></i> Enregistrer</button>
            </div>
        </div>
    </div>
</div>

</form>

@endsection

@section('scripts')
<script>
// ============================================================
// RECHERCHE EMPLOYÉ
// ============================================================
const employes = [
    @foreach($employes as $e)
        {
            id: {{ $e->id }},
            nom: "{{ $e->nom }} {{ $e->prenom }}",
            matricule: "{{ $e->matricule }}",
            salaire: {{ $e->salaire_base }},
            contrat: "{{ $e->contratActif ? $e->contratActif->numero_contrat : 'Aucun contrat' }}"
        },
    @endforeach
];

const input = document.getElementById('search_employe');
const list = document.getElementById('employe_list');
const hidden = document.getElementById('employe_id');
const contratInfo = document.getElementById('contrat_info');

input.addEventListener('input', function () {
    const value = this.value.toLowerCase();
    list.innerHTML = '';

    if (!value) {
        list.style.display = 'none';
        return;
    }

    const filtered = employes.filter(e =>
        e.nom.toLowerCase().includes(value) ||
        e.matricule.toLowerCase().includes(value)
    );

    if (filtered.length === 0) {
        list.style.display = 'none';
        return;
    }

    filtered.forEach(e => {
        const item = document.createElement('div');
        item.className = 'emp-item';
        item.innerHTML = `
            <div class="emp-name">${e.nom}</div>
            <div class="emp-meta">📌 ${e.matricule} · Contrat: ${e.contrat}</div>
        `;
        item.onclick = function () {
            input.value = e.nom;
            hidden.value = e.id;
            contratInfo.value = e.contrat;
            document.getElementById('salaire_brut').value = e.salaire;
            calculer();
            list.style.display = 'none';
        };
        list.appendChild(item);
    });

    list.style.display = 'block';
});

document.addEventListener('click', function (e) {
    if (!list.contains(e.target) && e.target !== input) {
        list.style.display = 'none';
    }
});

// ============================================================
// CALCUL EN TEMPS RÉEL
// ============================================================
function val(id) {
    return parseFloat(document.getElementById(id)?.value || document.querySelector(`[name="${id}"]`)?.value || 0) || 0;
}

function fmt(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';
}

function fmtNumber(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n));
}

function calculer() {
    // ===== ÉLÉMENTS POSITIFS =====
    const brut = val('salaire_brut');
    const sh = brut > 0 ? brut / 173.33 : 0;
    const nbHS = val('nb_heures_sup');
    const hs = Math.round(nbHS * sh);
    const prime = parseFloat(document.querySelector('[name="prime"]')?.value) || 0;
    const ind = parseFloat(document.querySelector('[name="indemnite"]')?.value) || 0;
    const fixe = val('montant_fixe');
    const totalBrut = brut + hs + prime + ind + fixe;

    // ============================================================
    // CALCUL CNPS COMMENTÉ
    // ============================================================
    // const baseCnps = brut;
    // const cnpsSalariale = Math.round(baseCnps * 0.0252);
    // const cnpsPatronale = Math.round(baseCnps * 0.0420);
    // const totalCnps = cnpsSalariale + cnpsPatronale;

    // ===== DÉDUCTIONS =====
    const retard = parseFloat(document.querySelector('[name="montant_retard"]')?.value) || 0;
    const absence = parseFloat(document.querySelector('[name="montant_absence"]')?.value) || 0;
    const acompte = parseFloat(document.querySelector('[name="acompte"]')?.value) || 0;
    const pret = parseFloat(document.querySelector('[name="pret"]')?.value) || 0;
    const sanction = parseFloat(document.querySelector('[name="montant_sanction"]')?.value) || 0;
    const imputation = parseFloat(document.querySelector('[name="imputation_salaire"]')?.value) || 0;
    const frais = parseFloat(document.querySelector('[name="frais_bancaires"]')?.value) || 0;

    // ============================================================
    // DÉDUCTIONS SANS CNPS
    // ============================================================
    // const totalDed = retard + absence + acompte + pret + sanction + imputation + frais + totalCnps;
    const totalDed = retard + absence + acompte + pret + sanction + imputation + frais;
    const net = Math.max(0, totalBrut - totalDed);

    // ===== MISE À JOUR AFFICHAGE =====
    document.getElementById('salaire_heure').value = sh.toFixed(4);
    document.getElementById('montant_heures_sup').value = hs;

    // Éléments positifs
    document.getElementById('c-brut').innerText = fmt(brut);
    document.getElementById('c-hs').innerText = fmt(hs);
    document.getElementById('c-prime').innerText = fmt(prime);
    document.getElementById('c-ind').innerText = fmt(ind);
    document.getElementById('c-fixe').innerText = fmt(fixe);
    document.getElementById('c-total-brut').innerText = fmt(totalBrut);

    // ============================================================
    // AFFICHAGE CNPS COMMENTÉ
    // ============================================================
    // document.getElementById('c-base-cnps').innerText = fmt(baseCnps);
    // document.getElementById('c-cnps-salariale').innerText = fmt(cnpsSalariale);
    // document.getElementById('c-cnps-patronale').innerText = fmt(cnpsPatronale);
    // document.getElementById('c-total-cnps').innerText = fmt(totalCnps);

    // Déductions
    document.getElementById('c-retard').innerText = fmt(retard);
    document.getElementById('c-absence').innerText = fmt(absence);
    document.getElementById('c-acompte').innerText = fmt(acompte);
    document.getElementById('c-pret').innerText = fmt(pret);
    document.getElementById('c-sanction').innerText = fmt(sanction);
    document.getElementById('c-imputation').innerText = fmt(imputation);
    document.getElementById('c-frais').innerText = fmt(frais);
    document.getElementById('c-total-ded').innerText = fmt(totalDed);

    // Net
    document.getElementById('c-net').innerText = fmt(net);

    // Hidden fields
    document.getElementById('net_a_payer').value = net;
    // CHAMPS CNPS COMMENTÉS
    // document.getElementById('cnps_salariale').value = cnpsSalariale;
    // document.getElementById('cnps_patronale').value = cnpsPatronale;
    // document.getElementById('base_cnps').value = baseCnps;
}

document.addEventListener('DOMContentLoaded', calculer);
</script>
@endsection