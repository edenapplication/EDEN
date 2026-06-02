@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
.calc-box { background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px; padding:16px; margin-top:16px; }
.calc-row { display:flex; justify-content:space-between; padding:5px 0; font-size:13px; border-bottom:1px solid #e2e8f0; }
.calc-row:last-child { border:none; }
.calc-total { font-size:16px; font-weight:800; color:#1d4ed8; padding-top:8px; border-top:2px solid #1d4ed8; }
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

.emp-item:hover {
    background: #f0f9ff;
    transform: translateX(2px);
}

.emp-name {
    font-weight: 600;
    color: #1e3a5f;
    font-size: 14px;
}

.emp-meta {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

.emp-item:last-child {
    border-bottom: none;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">💰 Nouveau bulletin de paie</h2>
    <a href="{{ route('rh.paie.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
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

<input type="text" id="search_employe" class="form-control"
       placeholder="🔍 Rechercher un employé...">

<input type="hidden" name="employe_id" id="employe_id">

<div id="employe_list" class="list-group position-absolute w-100" style="z-index:999; display:none;"></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Période <span class="text-danger">*</span></label>
                    <input type="month" name="periode" class="form-control" required
                           value="{{ now()->format('Y-m') }}" onchange="calculer()">
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
                    <input type="date" name="date_paiement" class="form-control" required
                           value="{{ now()->format('Y-m-d') }}">
                </div>
            </div>
        </div>

        {{-- ÉLÉMENTS POSITIFS --}}
        <div class="form-section">
            <h5>✅ Éléments du salaire</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Salaire brut (FCFA)</label>
                    <input type="number" name="salaire_brut" id="salaire_brut" class="form-control"
                           value="0" min="0" oninput="calculer()">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Salaire/heure (auto)</label>
                    <input type="number" name="salaire_heure" id="salaire_heure" class="form-control"
                           value="0" readonly style="background:#f8fafc;">
                    <small class="text-muted">Base 173,33h/mois</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Montant fixe (FCFA)</label>
                    <input type="number" name="montant_fixe" id="montant_fixe" class="form-control"
                           value="1000" min="0" oninput="calculer()">
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
                    <input type="number" name="nb_heures_sup" id="nb_heures_sup" class="form-control"
                           value="0" min="0" step="0.5" oninput="calculer()">
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
                <div class="col-md-3">
                    <label class="form-label fw-semibold">CNPS (FCFA)</label>
                    <input type="number" name="cnps" class="form-control" value="0" min="0" oninput="calculer()">
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
                <div class="calc-row"><span>Salaire brut</span><span id="c-brut">0</span></div>
                <div class="calc-row"><span>Heures sup.</span><span id="c-hs">0</span></div>
                <div class="calc-row"><span>Prime</span><span id="c-prime">0</span></div>
                <div class="calc-row"><span>Indemnité</span><span id="c-ind">0</span></div>
                <div class="calc-row"><span>Fixe</span><span id="c-fixe">0</span></div>
                <div class="calc-row" style="font-weight:700;">
                    <span>= Total brut</span>
                    <span id="c-total-brut" style="color:#16a34a;">0</span>
                </div>

                <div style="font-size:11px; font-weight:700; color:#dc2626; text-transform:uppercase; margin:10px 0 6px;">Déductions</div>
                <div class="calc-row"><span>Retards</span><span id="c-retard">0</span></div>
                <div class="calc-row"><span>Absences</span><span id="c-absence">0</span></div>
                <div class="calc-row"><span>Acompte</span><span id="c-acompte">0</span></div>
                <div class="calc-row"><span>Prêt</span><span id="c-pret">0</span></div>
                <div class="calc-row"><span>Sanction</span><span id="c-sanction">0</span></div>
                <div class="calc-row"><span>Imputation</span><span id="c-imputation">0</span></div>
                <div class="calc-row"><span>Frais bancaires</span><span id="c-frais">0</span></div>
                <div class="calc-row"><span>CNPS</span><span id="c-cnps">0</span></div>
                <div class="calc-row" style="font-weight:700;">
                    <span>= Total déductions</span>
                    <span id="c-total-ded" style="color:#dc2626;">0</span>
                </div>

                <div class="calc-row calc-total">
                    <span>NET À PAYER</span>
                    <span id="c-net">0 FCFA</span>
                </div>

                <input type="hidden" name="net_a_payer" id="net_a_payer" value="0">
                <input type="hidden" name="montant_heures_sup" id="montant_heures_sup" value="0">
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('rh.paie.index') }}" class="btn btn-light flex-fill">Annuler</a>
                <button type="submit" class="btn btn-primary flex-fill">💾 Enregistrer</button>
            </div>
        </div>
    </div>
</div>

</form>

@endsection
@section('scripts')
<script>
function chargerSalaire() {
    const sel = document.getElementById('employe_id');
    const opt = sel.options[sel.selectedIndex];
    const salaire = parseFloat(opt.dataset.salaire) || 0;
    document.getElementById('salaire_brut').value = salaire;
    calculer();
}

function val(id) {
    return parseFloat(document.getElementById(id)?.value || document.querySelector(`[name="${id}"]`)?.value || 0) || 0;
}

function fmt(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';
}

function calculer() {
    const brut       = val('salaire_brut');
    const sh         = brut > 0 ? brut / 173.33 : 0;
    const nbHS       = val('nb_heures_sup');
    const hs         = Math.round(nbHS * sh);
    const prime      = parseFloat(document.querySelector('[name="prime"]')?.value) || 0;
    const ind        = parseFloat(document.querySelector('[name="indemnite"]')?.value) || 0;
    const fixe       = val('montant_fixe');
    const totalBrut  = brut + hs + prime + ind + fixe;

    const retard     = parseFloat(document.querySelector('[name="montant_retard"]')?.value) || 0;
    const absence    = parseFloat(document.querySelector('[name="montant_absence"]')?.value) || 0;
    const acompte    = parseFloat(document.querySelector('[name="acompte"]')?.value) || 0;
    const pret       = parseFloat(document.querySelector('[name="pret"]')?.value) || 0;
    const sanction   = parseFloat(document.querySelector('[name="montant_sanction"]')?.value) || 0;
    const imputation = parseFloat(document.querySelector('[name="imputation_salaire"]')?.value) || 0;
    const frais      = parseFloat(document.querySelector('[name="frais_bancaires"]')?.value) || 0;
    const cnps       = parseFloat(document.querySelector('[name="cnps"]')?.value) || 0;
    const totalDed   = retard + absence + acompte + pret + sanction + imputation + frais + cnps;

    const net = Math.max(0, totalBrut - totalDed);

    // Mise à jour affichage
    document.getElementById('salaire_heure').value     = sh.toFixed(4);
    document.getElementById('montant_heures_sup').value= hs;

    document.getElementById('c-brut').innerText        = fmt(brut);
    document.getElementById('c-hs').innerText          = fmt(hs);
    document.getElementById('c-prime').innerText       = fmt(prime);
    document.getElementById('c-ind').innerText         = fmt(ind);
    document.getElementById('c-fixe').innerText        = fmt(fixe);
    document.getElementById('c-total-brut').innerText  = fmt(totalBrut);
    document.getElementById('c-retard').innerText      = fmt(retard);
    document.getElementById('c-absence').innerText     = fmt(absence);
    document.getElementById('c-acompte').innerText     = fmt(acompte);
    document.getElementById('c-pret').innerText        = fmt(pret);
    document.getElementById('c-sanction').innerText    = fmt(sanction);
    document.getElementById('c-imputation').innerText  = fmt(imputation);
    document.getElementById('c-frais').innerText       = fmt(frais);
    document.getElementById('c-cnps').innerText        = fmt(cnps);
    document.getElementById('c-total-ded').innerText   = fmt(totalDed);
    document.getElementById('c-net').innerText         = fmt(net);
    document.getElementById('net_a_payer').value       = net;
}

document.addEventListener('DOMContentLoaded', calculer);
</script>
<script>
const employes = [
    @foreach($employes as $e)
        {
            id: {{ $e->id }},
            nom: "{{ $e->nom }} {{ $e->prenom }}",
            matricule: "{{ $e->matricule }}",
            salaire: {{ $e->salaire_base }}
        },
    @endforeach
];

const input = document.getElementById('search_employe');
const list = document.getElementById('employe_list');
const hidden = document.getElementById('employe_id');

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
            <div class="emp-meta">📌 ${e.matricule}</div>
        `;

        item.onclick = function () {
            input.value = e.nom;
            hidden.value = e.id;

            document.getElementById('salaire_brut').value = e.salaire;
            calculer();

            list.style.display = 'none';
        };

        list.appendChild(item);
    });

    list.style.display = 'block';
});

// fermer si clic extérieur
document.addEventListener('click', function (e) {
    if (!list.contains(e.target) && e.target !== input) {
        list.style.display = 'none';
    }
});
</script>
@endsection