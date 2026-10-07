{{-- APRÈS --}}
@extends('admin.affectations.layout')
@section('content')

<style>
.ar-container { max-width: 1100px; margin: 0 auto; }

/* 📊 MINI TABLEAU DE BORD */
.ar-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}
.ar-dash-card {
    background: white;
    border-radius: 12px;
    padding: 14px 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    border-left: 4px solid #1d4ed8;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: transform 0.2s;
}
.ar-dash-card:hover { transform: translateY(-2px); }
.ar-dash-ico {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.ar-dash-lbl {
    font-size: 10px; color: #64748b;
    text-transform: uppercase; font-weight: 700;
    letter-spacing: 0.3px;
}
.ar-dash-val {
    font-size: 20px; font-weight: 800;
    color: #1e3a5f; line-height: 1.1;
    margin-top: 2px;
}

.ar-step {
    background: white; border-radius: 12px; padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    margin-bottom: 16px; border-left: 4px solid #e2e8f0;
    transition: all 0.3s;
}
.ar-step.active { border-left-color: #1d4ed8; box-shadow: 0 4px 20px rgba(29,78,216,0.12); }
.ar-step.done   { border-left-color: #16a34a; opacity: 0.9; }

.ar-step-header {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 14px; font-weight: 800; color: #1e3a5f;
    font-size: 15px;
}
.ar-step-num {
    width: 28px; height: 28px; border-radius: 50%;
    background: #e2e8f0; color: #64748b;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 800; flex-shrink: 0;
}
.ar-step.active .ar-step-num { background: #1d4ed8; color: white; }
.ar-step.done   .ar-step-num { background: #16a34a; color: white; }

/* Recherche */
.ar-search-box { position: relative; }
.ar-search-input {
    width: 100%; padding: 14px 18px 14px 48px;
    border: 2px solid #e2e8f0; border-radius: 10px;
    font-size: 15px; transition: all 0.2s;
}
.ar-search-input:focus { border-color: #1d4ed8; outline: none; box-shadow: 0 0 0 4px rgba(29,78,216,0.1); }
.ar-search-icon {
    position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
    font-size: 18px; color: #94a3b8; pointer-events: none;
}
.ar-search-clear {
    position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: #94a3b8; cursor: pointer;
    font-size: 18px; display: none;
}
.ar-search-clear:hover { color: #dc2626; }

.ar-resultats { margin-top: 12px; max-height: 320px; overflow-y: auto; display: none; }
.ar-resultat-item {
    padding: 12px 16px; border: 2px solid #e2e8f0;
    border-radius: 10px; margin-bottom: 8px;
    cursor: pointer; transition: all 0.2s;
    display: flex; justify-content: space-between; align-items: center; gap: 12px;
}
.ar-resultat-item:hover { border-color: #1d4ed8; background: #eff6ff; transform: translateX(4px); }
.ar-resultat-item.selected { border-color: #16a34a; background: #f0fdf4; }

.ar-badge { padding: 2px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; display:inline-block; }
.ar-badge.client { background: #dbeafe; color: #1d4ed8; }
.ar-badge.benef  { background: #faf5ff; color: #7c3aed; }
.ar-badge.info   { background: #f1f5f9; color: #64748b; }

.ar-dossier-item {
    padding: 12px 16px; border: 2px solid #e2e8f0;
    border-radius: 10px; margin-bottom: 8px;
    cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; gap: 12px;
}
.ar-dossier-item:hover { border-color: #1d4ed8; background: #eff6ff; }
.ar-dossier-item.selected { border-color: #16a34a; background: #f0fdf4; }
.ar-dossier-item input[type="radio"] { width: 18px; height: 18px; accent-color: #16a34a; }

.ar-alerte {
    background: #fef3c7; border: 2px solid #fcd34d;
    border-radius: 10px; padding: 12px 16px;
    color: #92400e; font-size: 13px; font-weight: 600;
    display: flex; align-items: center; gap: 10px;
}
.ar-alerte.success { background: #dcfce7; border-color: #86efac; color: #166534; }
.ar-alerte.info    { background: #dbeafe; border-color: #93c5fd; color: #1e40af; }

.ar-cascade { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }

.ar-lots-grid {
    display: flex; flex-wrap: wrap; gap: 8px;
    margin-top: 12px; padding: 12px;
    background: #f8fafc; border-radius: 8px;
    max-height: 280px; overflow-y: auto;
    border: 1px solid #e2e8f0;
}
.ar-lot-label {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; border: 2px solid #e2e8f0;
    border-radius: 8px; cursor: pointer;
    font-size: 12px; font-weight: 600; background: white;
    transition: all 0.2s;
}
.ar-lot-label:hover { border-color: #1d4ed8; }
.ar-lot-label.checked { background: #dcfce7; border-color: #16a34a; color: #166534; }
.ar-lot-label input[type="checkbox"] { width: 14px; height: 14px; accent-color: #16a34a; }

.ar-resume-lots {
    margin-top: 12px; padding: 10px 14px;
    background: #dcfce7; border: 2px solid #86efac;
    border-radius: 8px; font-size: 13px; color: #166534;
    font-weight: 700;
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;
}

.ar-btn-add {
    width: 100%; padding: 14px;
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    color: white; border: none; border-radius: 12px;
    font-size: 15px; font-weight: 800;
    cursor: pointer; transition: all 0.2s;
    box-shadow: 0 4px 14px rgba(29,78,216,0.3);
    margin-top: 16px;
}
.ar-btn-add:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(29,78,216,0.4);
}
.ar-btn-add:disabled { background: #cbd5e1; box-shadow: none; cursor: not-allowed; opacity: 0.7; }

.ar-empty {
    color: #94a3b8; font-size: 12px;
    text-align: center; padding: 20px;
}

/* ═══════════════════════════════════════════════ */
/* 🛒 PANIER                                        */
/* ═══════════════════════════════════════════════ */
.ar-panier {
    background: white; border-radius: 12px; padding: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px; border-left: 4px solid #f59e0b;
    display: none;
}
.ar-panier.visible { display: block; }
.ar-panier-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 14px;
}
.ar-panier-title {
    font-size: 16px; font-weight: 800; color: #1e3a5f;
    display: flex; align-items: center; gap: 10px;
}
.ar-panier-count {
    background: #f59e0b; color: white;
    padding: 2px 10px; border-radius: 20px;
    font-size: 12px; font-weight: 800;
}
.ar-panier-item {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 12px 16px; border: 2px solid #e2e8f0;
    border-radius: 10px; margin-bottom: 8px;
    background: #fefce8; transition: all 0.2s;
    gap: 10px;
}
.ar-panier-item:hover { border-color: #f59e0b; background: #fef9c3; }
.ar-panier-item-left { flex: 1; min-width: 0; }
.ar-panier-nom { font-weight: 800; color: #1e3a5f; font-size: 14px; margin-bottom: 4px; }
.ar-panier-detail { font-size: 11px; color: #64748b; margin-bottom: 4px; }
.ar-panier-lots { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px; }
.ar-panier-lot-chip {
    background: #dcfce7; color: #166534;
    padding: 2px 8px; border-radius: 6px;
    font-size: 10px; font-weight: 700;
    border: 1px solid #86efac;
}
.ar-panier-total {
    font-size: 12px; font-weight: 800; color: #7c3aed;
    margin-top: 6px; background: #ede9fe;
    padding: 3px 10px; border-radius: 6px;
    display: inline-block;
}
.ar-panier-remove {
    background: #fee2e2; color: #dc2626;
    border: none; border-radius: 8px;
    width: 30px; height: 30px; cursor: pointer;
    font-size: 14px; flex-shrink: 0;
    transition: all 0.2s;
}
.ar-panier-remove:hover { background: #dc2626; color: white; transform: scale(1.1); }

.ar-panier-footer {
    background: #f8fafc; border-radius: 10px;
    padding: 14px; margin-top: 14px;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 12px;
}
.ar-panier-totaux {
    display: flex; gap: 20px; flex-wrap: wrap;
}
.ar-panier-total-bloc { text-align: left; }
.ar-panier-total-lbl { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; }
.ar-panier-total-val { font-size: 16px; font-weight: 800; color: #1e3a5f; }

.ar-panier-actions { display: flex; gap: 8px; }

.ar-btn-clear {
    background: #fee2e2; color: #dc2626;
    border: none; border-radius: 8px;
    padding: 10px 16px; font-size: 13px;
    font-weight: 700; cursor: pointer;
    transition: all 0.2s;
}
.ar-btn-clear:hover { background: #dc2626; color: white; }

.ar-btn-submit {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white; border: none; border-radius: 8px;
    padding: 12px 24px; font-size: 14px;
    font-weight: 800; cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 14px rgba(22,163,74,0.3);
}
.ar-btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(22,163,74,0.4);
}
.ar-btn-submit:disabled { background: #cbd5e1; box-shadow: none; cursor: not-allowed; }

.toast-notification {
    position: fixed; bottom: 20px; right: 20px;
    background: #1f2937; color: #fff;
    padding: 12px 20px; border-radius: 8px;
    font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 9999; max-width: 400px;
    animation: slideInToast 0.3s ease;
}
.toast-notification.success { background: #16a34a; }
.toast-notification.error   { background: #dc2626; }
.toast-notification.warning { background: #f59e0b; }
@keyframes slideInToast {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* ═══════════════════════════════════════════════ */
/* 🪟 MODAL CRÉATION BÉNÉFICIAIRE                   */
/* ═══════════════════════════════════════════════ */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9998;
}
.modal-overlay.visible { display: block; }
.modal-box {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 24px;
    border-radius: 14px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.25);
    z-index: 9999;
    width: 600px;
    max-width: 95%;
    max-height: 90vh;
    overflow-y: auto;
}
.modal-box.visible { display: block; }

.benef-client-item,
.benef-dossier-item {
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.2s;
}
.benef-client-item:hover,
.benef-dossier-item:hover {
    border-color: #7c3aed;
    background: #faf5ff;
}
.benef-client-item.selected,
.benef-dossier-item.selected {
    border-color: #16a34a;
    background: #f0fdf4;
}

@media (max-width: 768px) {
    .ar-cascade { grid-template-columns: 1fr 1fr; }
}
</style>

<div class="ar-container">

    {{-- EN-TÊTE --}}
    <div style="background:linear-gradient(135deg,#1e3a5f,#1d4ed8);border-radius:14px;
                padding:24px;color:white;margin-bottom:20px;">
        <h2 style="margin:0 0 6px 0;font-weight:800;">🎯 Attribution des parcelles</h2>
        <div style="font-size:13px;opacity:0.9;">
            Ajoutez plusieurs affectations dans le panier, puis validez tout en une seule fois.
        </div>
    </div>

    {{-- 📊 MINI TABLEAU DE BORD --}}
    <div class="ar-dashboard">
        <div class="ar-dash-card" style="border-left-color:#16a34a;">
            <div class="ar-dash-ico" style="background:#dcfce7;color:#16a34a;">📦</div>
            <div>
                <div class="ar-dash-lbl">Lots disponibles</div>
                <div class="ar-dash-val">{{ number_format($stats['lots_disponibles'], 0, ',', ' ') }}</div>
            </div>
        </div>

        <div class="ar-dash-card" style="border-left-color:#1d4ed8;">
            <div class="ar-dash-ico" style="background:#dbeafe;color:#1d4ed8;">🏢</div>
            <div>
                <div class="ar-dash-lbl">Grands sites</div>
                <div class="ar-dash-val">{{ number_format($stats['grand_sites'], 0, ',', ' ') }}</div>
            </div>
        </div>

        <div class="ar-dash-card" style="border-left-color:#7c3aed;">
            <div class="ar-dash-ico" style="background:#ede9fe;color:#7c3aed;">🎯</div>
            <div>
                <div class="ar-dash-lbl">Affectations aujourd'hui</div>
                <div class="ar-dash-val">{{ number_format($stats['affectations_du_jour'], 0, ',', ' ') }}</div>
            </div>
        </div>

        <div class="ar-dash-card" style="border-left-color:#f59e0b;">
            <div class="ar-dash-ico" style="background:#fef3c7;color:#f59e0b;">👥</div>
            <div>
                <div class="ar-dash-lbl">Bénéficiaires</div>
                <div class="ar-dash-val">{{ number_format($stats['beneficiaires'], 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>

    {{-- 🛒 PANIER EN HAUT --}}
    <div class="ar-panier" id="ar-panier">
        <div class="ar-panier-header">
            <div class="ar-panier-title">
                🛒 Panier
                <span class="ar-panier-count" id="panier-count">0</span>
            </div>
        </div>
        <div id="panier-liste"></div>
        <div class="ar-panier-footer" id="panier-footer" style="display:none;">
            <div class="ar-panier-totaux">
                <div class="ar-panier-total-bloc">
                    <div class="ar-panier-total-lbl">Affectations</div>
                    <div class="ar-panier-total-val" id="panier-total-count">0</div>
                </div>
                <div class="ar-panier-total-bloc">
                    <div class="ar-panier-total-lbl">Lots</div>
                    <div class="ar-panier-total-val" id="panier-total-lots">0</div>
                </div>
                <div class="ar-panier-total-bloc">
                    <div class="ar-panier-total-lbl">Superficie</div>
                    <div class="ar-panier-total-val" id="panier-total-sup">0 m²</div>
                </div>
            </div>
            <div class="ar-panier-actions">
                <button class="ar-btn-clear" onclick="viderPanier()">🗑 Vider</button>
                <button class="ar-btn-submit" id="ar-btn-submit" onclick="validerPanier()">
                    ✅ Tout valider
                </button>
            </div>
        </div>
    </div>

    {{-- ÉTAPE 1 : RECHERCHE --}}
    <div class="ar-step active" id="step-1">
        <div class="ar-step-header">
            <span class="ar-step-num">1</span>
            🔍 Rechercher client/Porteur du dossier
        </div>

        <div class="ar-search-box">
            <span class="ar-search-icon">🔎</span>
            <input type="text" id="ar-search" class="ar-search-input"
                   placeholder="Tapez un nom ou un téléphone (min. 2 caractères)..."
                   autocomplete="off">
            <button class="ar-search-clear" id="ar-search-clear" onclick="clearSearch()">✕</button>
        </div>

        <div class="ar-resultats" id="ar-resultats"></div>

        {{-- 🎯 Bouton création bénéficiaire --}}
        <div id="ar-create-benef" style="display:none;margin-top:14px;">
            <div style="background:#faf5ff;border:2px dashed #c4b5fd;border-radius:10px;
                        padding:14px 18px;display:flex;justify-content:space-between;
                        align-items:center;gap:12px;flex-wrap:wrap;">
                <div style="font-size:12px;color:#6b21a8;">
                    <strong>💡 Pas trouvé dans les résultats ?</strong><br>
                    Créez un nouveau bénéficiaire et associez-le à un dossier existant.
                </div>
                <button type="button" onclick="ouvrirModalCreationBenef()"
                        style="background:linear-gradient(135deg,#7c3aed,#6d28d9);color:white;
                               border:none;border-radius:8px;padding:10px 18px;
                               font-size:13px;font-weight:800;cursor:pointer;
                               box-shadow:0 4px 12px rgba(124,58,237,0.3);
                               display:inline-flex;align-items:center;gap:6px;
                               transition:all 0.2s;"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'">
                    ➕ Créer un bénéficiaire
                </button>
            </div>
        </div>
    </div>

    {{-- ÉTAPE 2 : DOSSIER --}}
    <div class="ar-step" id="step-2" style="display:none;">
        <div class="ar-step-header">
            <span class="ar-step-num">2</span>
            📂 Choisir le dossier
        </div>

        <div id="ar-personne-selected" style="background:#eff6ff;border-radius:10px;
             padding:12px 16px;margin-bottom:14px;font-size:13px;color:#1e3a5f;"></div>

        <div id="ar-alerte-conversion" style="display:none;margin-bottom:12px;"></div>

        <div id="ar-dossiers"></div>
    </div>

    {{-- ÉTAPE 3 : LOTS --}}
    <div class="ar-step" id="step-3" style="display:none;">
        <div class="ar-step-header">
            <span class="ar-step-num">3</span>
            📦 Sélectionner les lots
        </div>

        <div class="ar-cascade">
            <div>
                <label style="font-size:11px;font-weight:700;color:#64748b;">🏢 Grand Site</label>
                <select id="ar-gs" class="form-control form-control-sm" onchange="chargerSites(this.value)">
                    <option value="">-- Choisir --</option>
                    @foreach($grandSites as $gs)
                        <option value="{{ $gs->id }}">{{ $gs->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#64748b;">📍 Site</label>
                <select id="ar-site" class="form-control form-control-sm" onchange="chargerTfs(this.value)">
                    <option value="">-- Choisir --</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#64748b;">📄 TF</label>
                <select id="ar-tf" class="form-control form-control-sm" onchange="chargerBlocs(this.value)">
                    <option value="">-- Choisir --</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#64748b;">🏗️ Bloc</label>
                <select id="ar-bloc" class="form-control form-control-sm" onchange="chargerLots(this.value)">
                    <option value="">-- Choisir --</option>
                </select>
            </div>
        </div>

        <div class="ar-lots-grid" id="ar-lots-grid">
            <div class="ar-empty" style="width:100%;">
                ℹ️ Sélectionnez Grand Site → Site → TF → Bloc pour voir les lots disponibles
            </div>
        </div>

        <div class="ar-resume-lots" id="ar-resume-lots" style="display:none;"></div>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:12px;margin-top:16px;">
            <div>
                <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Date d'affectation</label>
                <input type="date" id="ar-date" class="form-control form-control-sm"
                       value="{{ now()->format('Y-m-d') }}">
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#64748b;">📝 Notes (optionnel)</label>
                <input type="text" id="ar-notes" class="form-control form-control-sm"
                       placeholder="Ex : affectation terrain du 01/10">
            </div>
        </div>

        <button class="ar-btn-add" id="ar-btn-add" onclick="ajouterAuPanier()" disabled>
            ➕ Ajouter au panier
        </button>
    </div>

    {{-- 🪟 MODAL CRÉATION BÉNÉFICIAIRE --}}
    <div class="modal-overlay" id="benefModalOverlay" onclick="fermerModalCreationBenef()"></div>
    <div class="modal-box" id="benefModal">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h5 style="color:#7c3aed;font-weight:800;margin:0;font-size:16px;">
                ➕ Créer un nouveau bénéficiaire
            </h5>
            <button onclick="fermerModalCreationBenef()"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#94a3b8;">✕</button>
        </div>

        {{-- Étape A : choisir le dossier --}}
        <div id="benef-etape-dossier">
            <div style="background:#eff6ff;border-radius:10px;padding:12px 16px;
                        margin-bottom:14px;font-size:12.5px;color:#1e40af;">
                🔍 Sélectionnez le <strong>dossier</strong> auquel associer ce bénéficiaire
            </div>

            <div style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#64748b;">
                    🔎 Rechercher un client (nom ou téléphone)
                </label>
                <input type="text" id="benef-recherche-client" class="form-control form-control-sm"
                       style="margin-top:6px;" placeholder="Min. 2 caractères..."
                       oninput="rechercherClientPourBenef(this.value)">
            </div>

            <div id="benef-liste-clients" style="max-height:280px;overflow-y:auto;"></div>

            <div id="benef-dossiers-client" style="display:none;margin-top:14px;">
                <div style="background:#f0fdf4;border-radius:10px;padding:10px 14px;
                            margin-bottom:10px;font-size:12px;color:#166534;"
                     id="benef-client-selectionne"></div>
                <div style="font-size:12px;font-weight:700;color:#64748b;margin-bottom:8px;">
                    📂 Choisissez le dossier :
                </div>
                <div id="benef-liste-dossiers"></div>
            </div>
        </div>

        {{-- Étape B : infos bénéficiaire --}}
        <div id="benef-etape-infos" style="display:none;">
            <div style="background:#f0fdf4;border-radius:10px;padding:10px 14px;
                        margin-bottom:14px;font-size:12px;color:#166534;"
                 id="benef-recap-dossier"></div>

            <div style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#64748b;">
                    👤 Nom complet <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" id="benef-nom" class="form-control form-control-sm"
                       style="margin-top:6px;" maxlength="255">
            </div>

            <div style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#64748b;">📞 Téléphone</label>
                <input type="text" id="benef-telephone" class="form-control form-control-sm"
                       style="margin-top:6px;" maxlength="50">
            </div>

            <div style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#64748b;">
                    🪪 CNI (image ou PDF) <span style="color:#dc2626;">*</span>
                </label>
                <input type="file" id="benef-cni" class="form-control form-control-sm"
                       style="margin-top:6px;" accept=".jpg,.jpeg,.png,.pdf">
                <div style="font-size:10px;color:#94a3b8;margin-top:4px;">
                    JPG, PNG ou PDF — max 10 Mo
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label style="font-size:12px;font-weight:700;color:#64748b;">📝 Notes (optionnel)</label>
                <textarea id="benef-notes" class="form-control form-control-sm"
                          rows="3" style="margin-top:6px;font-size:13px;"
                          maxlength="1000"></textarea>
            </div>

            <div style="background:#eff6ff;border-radius:8px;padding:10px 14px;
                        font-size:11px;color:#1e40af;">
                ℹ️ Le bénéficiaire sera créé <strong>sans lot</strong>.
                Vous pourrez lui affecter des lots juste après.
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;gap:8px;margin-top:20px;">
            <button onclick="retourEtapeDossier()" id="benef-btn-retour"
                    class="btn btn-light btn-sm" style="font-weight:600;display:none;">
                ⬅️ Retour
            </button>
            <div style="display:flex;gap:8px;margin-left:auto;">
                <button onclick="fermerModalCreationBenef()"
                        class="btn btn-light btn-sm" style="font-weight:600;">
                    Annuler
                </button>
                <button onclick="validerCreationBenef()" id="benef-btn-valider"
                        class="btn btn-sm"
                        style="background:linear-gradient(135deg,#7c3aed,#6d28d9);
                               color:white;font-weight:800;border:none;padding:8px 20px;display:none;">
                    ✅ Créer le bénéficiaire
                </button>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const URL_RECHERCHE = '{{ route("affectation-rapide.rechercher") }}';
const URL_AFFECTER  = '{{ route("affectation-rapide.affecter") }}';

let personneSelectionnee = null;
let dossierSelectionne   = null;
let lotsSelectionnes     = new Set();
let lotsDisponibles      = [];

// 🛒 PANIER
let panier = [];

// ════════════════════════════════════════════════════════════════
// ÉTAPE 1 — RECHERCHE LIVE
// ════════════════════════════════════════════════════════════════
let rechercheTimeout = null;

document.getElementById('ar-search').addEventListener('input', function () {
    const q = this.value.trim();
    document.getElementById('ar-search-clear').style.display = q.length > 0 ? 'block' : 'none';

    clearTimeout(rechercheTimeout);

    if (q.length < 2) {
        document.getElementById('ar-resultats').style.display = 'none';
        document.getElementById('ar-create-benef').style.display = 'none';
        return;
    }

    rechercheTimeout = setTimeout(() => rechercher(q), 300);
});

function clearSearch() {
    document.getElementById('ar-search').value = '';
    document.getElementById('ar-search-clear').style.display = 'none';
    document.getElementById('ar-resultats').style.display = 'none';
    document.getElementById('ar-create-benef').style.display = 'none';
    resetEtapes();
}

function rechercher(q) {
    fetch(`${URL_RECHERCHE}?q=${encodeURIComponent(q)}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        afficherResultats(data.resultats);
    })
    .catch(err => console.error('Erreur recherche :', err));
}

function afficherResultats(resultats) {
    const container = document.getElementById('ar-resultats');
    const createBox = document.getElementById('ar-create-benef');

    // ✅ Afficher le bloc "Créer un bénéficiaire"
    createBox.style.display = 'block';

    if (!resultats.length) {
        container.innerHTML = `<div class="ar-empty">Aucun résultat trouvé</div>`;
        container.style.display = 'block';
        return;
    }

    container.innerHTML = resultats.map((r, i) => {
        const badgeType = r.type === 'client'
            ? `<span class="ar-badge client">👤 Client</span>`
            : `<span class="ar-badge benef">👥 Bénéficiaire</span>`;

        const tel = r.telephone ? `📞 ${r.telephone}` : '';

        return `
            <div class="ar-resultat-item" data-index="${i}">
                <div>
                    <div style="font-weight:700;color:#1e3a5f;font-size:14px;">
                        ${r.nom} ${badgeType}
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:4px;">
                        ${tel} · 📂 ${r.nb_dossiers} dossier(s)
                    </div>
                </div>
                <div style="color:#94a3b8;font-size:18px;">›</div>
            </div>
        `;
    }).join('');

    container.querySelectorAll('.ar-resultat-item').forEach((el, i) => {
        el.addEventListener('click', () => choisirPersonne(resultats[i], el));
    });

    container.style.display = 'block';
}

// ════════════════════════════════════════════════════════════════
// ÉTAPE 2 — CHOIX DE LA PERSONNE + DU DOSSIER
// ════════════════════════════════════════════════════════════════
function choisirPersonne(personne, el) {
    personneSelectionnee = personne;
    dossierSelectionne   = null;
    lotsSelectionnes     = new Set();

    document.querySelectorAll('.ar-resultat-item').forEach(item => item.classList.remove('selected'));
    el.classList.add('selected');

    document.getElementById('step-1').classList.add('done');
    document.getElementById('step-1').classList.remove('active');
    document.getElementById('step-2').style.display = 'block';
    document.getElementById('step-2').classList.add('active');

    document.getElementById('step-3').style.display = 'none';
    document.getElementById('step-3').classList.remove('active', 'done');

    const badgeType = personne.type === 'client'
        ? `<span class="ar-badge client">👤 Client</span>`
        : `<span class="ar-badge benef">👥 Bénéficiaire</span>`;

    document.getElementById('ar-personne-selected').innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <strong style="font-size:14px;">${personne.nom}</strong> ${badgeType}
                ${personne.telephone ? `<span style="color:#64748b;font-size:12px;"> · 📞 ${personne.telephone}</span>` : ''}
            </div>
            <button onclick="resetEtapes()" style="background:none;border:1.5px solid #cbd5e1;
                    border-radius:6px;padding:4px 10px;font-size:11px;color:#64748b;cursor:pointer;">
                ✕ Changer
            </button>
        </div>
    `;

    const alerte = document.getElementById('ar-alerte-conversion');
    if (personne.type === 'client' && !personne.is_benef) {
        alerte.className = 'ar-alerte';
        alerte.innerHTML = `⚠️ <strong>Ce client n'est pas encore bénéficiaire.</strong> Il sera automatiquement converti lors de l'affectation.`;
        alerte.style.display = 'flex';
    } else if (personne.type === 'client' && personne.is_benef) {
        alerte.className = 'ar-alerte info';
        alerte.innerHTML = `ℹ️ Ce client est déjà bénéficiaire d'un dossier.`;
        alerte.style.display = 'flex';
    } else {
        alerte.style.display = 'none';
    }

    afficherDossiers(personne.dossiers);
}

function afficherDossiers(dossiers) {
    const container = document.getElementById('ar-dossiers');

    if (!dossiers || dossiers.length === 0) {
        container.innerHTML = `<div class="ar-empty">Aucun dossier trouvé pour cette personne</div>`;
        return;
    }

    container.innerHTML = dossiers.map((d, i) => `
        <div class="ar-dossier-item" data-dossier-index="${i}">
            <input type="radio" name="ar-dossier" value="${d.id}">
            <div style="flex:1;">
                <div style="font-weight:700;color:#1e3a5f;font-size:14px;">
                    📂 ${d.nom}
                </div>
                ${d.grand_site ? `<div style="font-size:11px;color:#64748b;margin-top:2px;">🏢 ${d.grand_site}</div>` : ''}
            </div>
        </div>
    `).join('');

    container.querySelectorAll('.ar-dossier-item').forEach((el, i) => {
        el.addEventListener('click', () => choisirDossier(dossiers[i], el));
    });
}

function choisirDossier(dossier, el) {
    dossierSelectionne = dossier;

    document.querySelectorAll('.ar-dossier-item').forEach(d => d.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;

    document.getElementById('step-2').classList.add('done');
    document.getElementById('step-2').classList.remove('active');
    document.getElementById('step-3').style.display = 'block';
    document.getElementById('step-3').classList.add('active');

    resetCascade();
    lotsSelectionnes = new Set();
    lotsDisponibles  = [];
    mettreAJourBouton();
}

// ════════════════════════════════════════════════════════════════
// ÉTAPE 3 — CASCADE + SÉLECTION LOTS
// ════════════════════════════════════════════════════════════════
function resetCascade() {
    document.getElementById('ar-gs').value = '';
    document.getElementById('ar-site').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('ar-tf').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('ar-bloc').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('ar-lots-grid').innerHTML = `
        <div class="ar-empty" style="width:100%;">
            ℹ️ Sélectionnez Grand Site → Site → TF → Bloc pour voir les lots disponibles
        </div>`;
    document.getElementById('ar-resume-lots').style.display = 'none';
}

function chargerSites(gsId) {
    const sel = document.getElementById('ar-site');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('ar-tf').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('ar-bloc').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('ar-lots-grid').innerHTML = `<div class="ar-empty" style="width:100%;">ℹ️ Sélectionnez Site → TF → Bloc</div>`;

    if (!gsId) return;

    fetch(`/admin/affectations/api/sites/${gsId}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(sites => {
        sel.innerHTML = '<option value="">-- Choisir --</option>' +
            sites.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
    });
}

function chargerTfs(siteId) {
    const sel = document.getElementById('ar-tf');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('ar-bloc').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('ar-lots-grid').innerHTML = `<div class="ar-empty" style="width:100%;">ℹ️ Sélectionnez TF → Bloc</div>`;

    if (!siteId) return;

    fetch(`/admin/affectations/api/tfs/${siteId}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(tfs => {
        sel.innerHTML = '<option value="">-- Choisir --</option>' +
            tfs.map(t => `<option value="${t.id}">${t.title}</option>`).join('');
    });
}

function chargerBlocs(tfId) {
    const sel = document.getElementById('ar-bloc');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('ar-lots-grid').innerHTML = `<div class="ar-empty" style="width:100%;">ℹ️ Sélectionnez un Bloc</div>`;

    if (!tfId) return;

    fetch(`/admin/affectations/api/blocs/${tfId}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(blocs => {
        sel.innerHTML = '<option value="">-- Choisir --</option>' +
            blocs.map(b => `<option value="${b.id}">Bloc ${b.code}</option>`).join('');
    });
}

function chargerLots(blocId) {
    const grid = document.getElementById('ar-lots-grid');

    if (!blocId) {
        grid.innerHTML = `<div class="ar-empty" style="width:100%;">ℹ️ Sélectionnez un Bloc</div>`;
        return;
    }

    grid.innerHTML = `<div class="ar-empty" style="width:100%;">⏳ Chargement des lots...</div>`;

    fetch(`/admin/affectations/api/lots/${blocId}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(lots => {
        // ✅ Exclure les lots déjà dans le panier
        const lotsDansPanier = new Set();
        panier.forEach(p => p.lots.forEach(l => lotsDansPanier.add(l.id)));
        lots = lots.filter(l => !lotsDansPanier.has(l.id));

        lotsDisponibles = lots;

        if (!lots.length) {
            grid.innerHTML = `<div class="ar-empty" style="width:100%;">Aucun lot disponible dans ce bloc</div>`;
            return;
        }

        grid.innerHTML = lots.map(l => `
            <label class="ar-lot-label" data-lot-id="${l.id}">
                <input type="checkbox" value="${l.id}"
                       data-superficie="${l.superficie ?? 0}"
                       data-numero="${l.numero}">
                📦 Lot ${l.numero}
                ${l.superficie ? `<span style="color:#64748b;font-size:10px;">(${parseInt(l.superficie).toLocaleString('fr-FR')} m²)</span>` : ''}
            </label>
        `).join('');

        grid.querySelectorAll('.ar-lot-label').forEach(label => {
            const cb = label.querySelector('input[type="checkbox"]');
            cb.addEventListener('change', () => toggleLot(label, cb));
        });
    });
}

function toggleLot(label, cb) {
    const lotId = parseInt(cb.value);

    if (cb.checked) {
        lotsSelectionnes.add(lotId);
        label.classList.add('checked');
    } else {
        lotsSelectionnes.delete(lotId);
        label.classList.remove('checked');
    }

    mettreAJourResumeLots();
    mettreAJourBouton();
}

function mettreAJourResumeLots() {
    const resume = document.getElementById('ar-resume-lots');

    if (lotsSelectionnes.size === 0) {
        resume.style.display = 'none';
        return;
    }

    let superficieTotale = 0;
    const numeros = [];

    document.querySelectorAll('.ar-lot-label input:checked').forEach(cb => {
        superficieTotale += parseFloat(cb.dataset.superficie) || 0;
        numeros.push(cb.dataset.numero);
    });

    resume.style.display = 'flex';
    resume.innerHTML = `
        <span>✅ <strong>${lotsSelectionnes.size} lot(s)</strong> sélectionné(s) : ${numeros.join(', ')}</span>
        <span style="background:#166534;color:white;padding:3px 12px;border-radius:8px;">
            📐 ${superficieTotale.toLocaleString('fr-FR')} m²
        </span>
    `;
}

function mettreAJourBouton() {
    const btn = document.getElementById('ar-btn-add');
    btn.disabled = !(personneSelectionnee && dossierSelectionne && lotsSelectionnes.size > 0);
}

// ════════════════════════════════════════════════════════════════
// 🛒 AJOUTER AU PANIER
// ════════════════════════════════════════════════════════════════
function ajouterAuPanier() {
    if (!personneSelectionnee || !dossierSelectionne || lotsSelectionnes.size === 0) {
        showToast('⚠️ Sélection incomplète', 'warning');
        return;
    }

    const lotsDetails = [];
    document.querySelectorAll('.ar-lot-label input:checked').forEach(cb => {
        lotsDetails.push({
            id: parseInt(cb.value),
            numero: cb.dataset.numero,
            superficie: parseFloat(cb.dataset.superficie) || 0,
        });
    });

    const entree = {
        id: Date.now(),
        type_personne: personneSelectionnee.type,
        personne_id: personneSelectionnee.id,
        personne_nom: personneSelectionnee.nom,
        dossier_id: dossierSelectionne.id,
        dossier_nom: dossierSelectionne.nom,
        grand_site: dossierSelectionne.grand_site || '',
        lots: lotsDetails,
        date_affectation: document.getElementById('ar-date').value,
        notes: document.getElementById('ar-notes').value,
    };

    const doublon = panier.find(p =>
        p.personne_id === entree.personne_id &&
        p.dossier_id === entree.dossier_id &&
        p.type_personne === entree.type_personne
    );

    if (doublon) {
        const lotsExistants = new Set(doublon.lots.map(l => l.id));
        entree.lots.forEach(l => {
            if (!lotsExistants.has(l.id)) {
                doublon.lots.push(l);
            }
        });
        doublon.date_affectation = entree.date_affectation;
        doublon.notes = entree.notes || doublon.notes;
    } else {
        panier.push(entree);
    }

    afficherPanier();

    resetEtapes();
    document.getElementById('ar-search').value = '';
    document.getElementById('ar-search-clear').style.display = 'none';
    document.getElementById('ar-create-benef').style.display = 'none';

    showToast('✅ Ajouté au panier', 'success');
}

// ════════════════════════════════════════════════════════════════
// 🛒 AFFICHER LE PANIER
// ════════════════════════════════════════════════════════════════
function afficherPanier() {
    const conteneur = document.getElementById('ar-panier');
    const liste = document.getElementById('panier-liste');
    const footer = document.getElementById('panier-footer');
    const count = document.getElementById('panier-count');

    if (panier.length === 0) {
        conteneur.classList.remove('visible');
        return;
    }

    conteneur.classList.add('visible');
    count.textContent = panier.length;

    liste.innerHTML = panier.map(p => {
        const lotsChips = p.lots.map(l =>
            `<span class="ar-panier-lot-chip">📦 ${l.numero}</span>`
        ).join('');

        const supTotal = p.lots.reduce((sum, l) => sum + (l.superficie || 0), 0);

        return `
            <div class="ar-panier-item" data-panier-id="${p.id}">
                <div class="ar-panier-item-left">
                    <div class="ar-panier-nom">
                        ${p.type_personne === 'client' ? '👤' : '👥'} ${p.personne_nom}
                    </div>
                    <div class="ar-panier-detail">
                        📂 ${p.dossier_nom}
                        ${p.grand_site ? ` · 🏢 ${p.grand_site}` : ''}
                        · 📅 ${formatDate(p.date_affectation)}
                    </div>
                    <div class="ar-panier-lots">${lotsChips}</div>
                    <div class="ar-panier-total">
                        📐 ${supTotal.toLocaleString('fr-FR')} m² · ${p.lots.length} lot(s)
                    </div>
                </div>
                <button class="ar-panier-remove" onclick="retirerDuPanier(${p.id})" title="Retirer">✕</button>
            </div>
        `;
    }).join('');

    const totalAffectations = panier.length;
    const totalLots = panier.reduce((sum, p) => sum + p.lots.length, 0);
    const totalSuperficie = panier.reduce((sum, p) =>
        sum + p.lots.reduce((s, l) => s + (l.superficie || 0), 0), 0);

    document.getElementById('panier-total-count').textContent = totalAffectations;
    document.getElementById('panier-total-lots').textContent  = totalLots;
    document.getElementById('panier-total-sup').textContent   = totalSuperficie.toLocaleString('fr-FR') + ' m²';

    footer.style.display = 'flex';
}

function retirerDuPanier(panierId) {
    panier = panier.filter(p => p.id !== panierId);
    afficherPanier();
    showToast('🗑 Retiré du panier', 'info');
}

function viderPanier() {
    if (panier.length === 0) return;
    if (!confirm(`Vider le panier (${panier.length} affectation(s)) ?`)) return;
    panier = [];
    afficherPanier();
    showToast('🗑 Panier vidé', 'info');
}

// ════════════════════════════════════════════════════════════════
// ✅ VALIDER TOUT LE PANIER
// ════════════════════════════════════════════════════════════════
async function validerPanier() {
    if (panier.length === 0) {
        showToast('⚠️ Panier vide', 'warning');
        return;
    }

    const btn = document.getElementById('ar-btn-submit');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Enregistrement en cours...';

    if (window.EdenLoader) window.EdenLoader.show();

    let succes = 0;
    let erreurs = [];

    for (const entree of panier) {
        try {
            const response = await fetch(URL_AFFECTER, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    type_personne:    entree.type_personne,
                    personne_id:      entree.personne_id,
                    dossier_id:       entree.dossier_id,
                    lot_ids:          entree.lots.map(l => l.id),
                    date_affectation: entree.date_affectation,
                    notes:            entree.notes,
                }),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                succes++;
            } else {
                let msg = data.message || `Erreur ${response.status}`;
                if (data.errors) msg = Object.values(data.errors).flat().join(', ');
                erreurs.push(`❌ ${entree.personne_nom} — ${msg}`);
            }
        } catch (err) {
            erreurs.push(`❌ ${entree.personne_nom} — Erreur réseau`);
        }
    }

    if (window.EdenLoader) window.EdenLoader.hide();

    btn.disabled = false;
    btn.innerHTML = originalText;

    if (succes > 0) {
        showToast(`✅ ${succes} affectation(s) enregistrée(s)` +
                  (erreurs.length > 0 ? ` — ${erreurs.length} erreur(s)` : ''), 'success');

        if (erreurs.length === 0) {
            panier = [];
            afficherPanier();
            setTimeout(() => location.reload(), 1500);
        } else {
            alert("⚠️ Certaines affectations ont échoué :\n\n" + erreurs.join('\n'));
        }
    } else {
        showToast('❌ Aucune affectation enregistrée', 'error');
        alert("Erreurs :\n\n" + erreurs.join('\n'));
    }
}

// ════════════════════════════════════════════════════════════════
// RESET
// ════════════════════════════════════════════════════════════════
function resetEtapes() {
    personneSelectionnee = null;
    dossierSelectionne   = null;
    lotsSelectionnes     = new Set();
    lotsDisponibles      = [];

    const step1 = document.getElementById('step-1');
    step1.classList.add('active');
    step1.classList.remove('done');

    const step2 = document.getElementById('step-2');
    step2.style.display = 'none';
    step2.classList.remove('active', 'done');
    document.getElementById('ar-personne-selected').innerHTML = '';
    document.getElementById('ar-alerte-conversion').style.display = 'none';
    document.getElementById('ar-dossiers').innerHTML = '';

    const step3 = document.getElementById('step-3');
    step3.style.display = 'none';
    step3.classList.remove('active', 'done');

    document.querySelectorAll('.ar-resultat-item').forEach(el => el.classList.remove('selected'));

    const btn = document.getElementById('ar-btn-add');
    btn.disabled = true;
}

// ════════════════════════════════════════════════════════════════
// 🎯 CRÉATION DE BÉNÉFICIAIRE
// ════════════════════════════════════════════════════════════════
let benefClientSelectionne = null;
let benefDossierSelectionne = null;
let benefRechercheTimeout = null;

function ouvrirModalCreationBenef() {
    benefClientSelectionne = null;
    benefDossierSelectionne = null;

    document.getElementById('benef-recherche-client').value = '';
    document.getElementById('benef-liste-clients').innerHTML = '';
    document.getElementById('benef-dossiers-client').style.display = 'none';
    document.getElementById('benef-etape-dossier').style.display = 'block';
    document.getElementById('benef-etape-infos').style.display = 'none';
    document.getElementById('benef-btn-retour').style.display = 'none';
    document.getElementById('benef-btn-valider').style.display = 'none';
    document.getElementById('benef-nom').value = '';
    document.getElementById('benef-telephone').value = '';
    document.getElementById('benef-cni').value = '';
    document.getElementById('benef-notes').value = '';

    document.getElementById('benefModalOverlay').classList.add('visible');
    document.getElementById('benefModal').classList.add('visible');

    setTimeout(() => document.getElementById('benef-recherche-client').focus(), 100);
}

function fermerModalCreationBenef() {
    document.getElementById('benefModalOverlay').classList.remove('visible');
    document.getElementById('benefModal').classList.remove('visible');
}

function rechercherClientPourBenef(q) {
    q = q.trim();

    clearTimeout(benefRechercheTimeout);

    if (q.length < 2) {
        document.getElementById('benef-liste-clients').innerHTML = '';
        document.getElementById('benef-dossiers-client').style.display = 'none';
        return;
    }

    benefRechercheTimeout = setTimeout(() => {
        fetch(`${URL_RECHERCHE}?q=${encodeURIComponent(q)}`, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            afficherClientsPourBenef(data.resultats);
        });
    }, 300);
}

function afficherClientsPourBenef(resultats) {
    const container = document.getElementById('benef-liste-clients');

    const clients = resultats.filter(r => r.type === 'client' && r.nb_dossiers > 0);

    if (!clients.length) {
        container.innerHTML = `<div style="text-align:center;color:#94a3b8;padding:20px;font-size:12px;">
            Aucun client avec dossier trouvé. Vérifiez le nom ou créez un client d'abord.
        </div>`;
        document.getElementById('benef-dossiers-client').style.display = 'none';
        return;
    }

    container.innerHTML = clients.map((c, i) => `
        <div class="benef-client-item" data-client-index="${i}">
            <div style="font-weight:700;color:#1e3a5f;font-size:13px;">
                👤 ${c.nom}
                ${c.telephone ? `<span style="color:#64748b;font-weight:400;font-size:11px;"> · 📞 ${c.telephone}</span>` : ''}
            </div>
            <div style="font-size:11px;color:#64748b;margin-top:2px;">
                📂 ${c.nb_dossiers} dossier(s)
            </div>
        </div>
    `).join('');

    container.querySelectorAll('.benef-client-item').forEach((el, i) => {
        el.addEventListener('click', () => choisirClientPourBenef(clients[i], el));
    });
}

function choisirClientPourBenef(client, el) {
    benefClientSelectionne = client;
    benefDossierSelectionne = null;

    document.querySelectorAll('.benef-client-item').forEach(e => e.classList.remove('selected'));
    el.classList.add('selected');

    const blocDossiers = document.getElementById('benef-dossiers-client');
    document.getElementById('benef-client-selectionne').innerHTML = `
        👤 <strong>${client.nom}</strong>
        ${client.telephone ? ` · 📞 ${client.telephone}` : ''}
    `;

    const containerDossiers = document.getElementById('benef-liste-dossiers');
    containerDossiers.innerHTML = client.dossiers.map((d, i) => `
        <div class="benef-dossier-item" data-dossier-index="${i}">
            <div style="font-weight:700;color:#1e3a5f;font-size:13px;">
                📂 ${d.nom}
            </div>
            ${d.grand_site ? `<div style="font-size:11px;color:#64748b;margin-top:2px;">🏢 ${d.grand_site}</div>` : ''}
        </div>
    `).join('');

    containerDossiers.querySelectorAll('.benef-dossier-item').forEach((el2, i) => {
        el2.addEventListener('click', () => choisirDossierPourBenef(client.dossiers[i], el2));
    });

    blocDossiers.style.display = 'block';
}

function choisirDossierPourBenef(dossier, el) {
    benefDossierSelectionne = dossier;

    document.querySelectorAll('.benef-dossier-item').forEach(e => e.classList.remove('selected'));
    el.classList.add('selected');

    document.getElementById('benef-recap-dossier').innerHTML = `
        👤 <strong>${benefClientSelectionne.nom}</strong>
        · 📂 <strong>${dossier.nom}</strong>
        ${dossier.grand_site ? ` · 🏢 ${dossier.grand_site}` : ''}
    `;

    document.getElementById('benef-nom').value = benefClientSelectionne.nom || '';
    document.getElementById('benef-telephone').value = benefClientSelectionne.telephone || '';

    document.getElementById('benef-etape-dossier').style.display = 'none';
    document.getElementById('benef-etape-infos').style.display = 'block';
    document.getElementById('benef-btn-retour').style.display = 'inline-block';
    document.getElementById('benef-btn-valider').style.display = 'inline-block';

    setTimeout(() => document.getElementById('benef-nom').focus(), 100);
}

function retourEtapeDossier() {
    document.getElementById('benef-etape-dossier').style.display = 'block';
    document.getElementById('benef-etape-infos').style.display = 'none';
    document.getElementById('benef-btn-retour').style.display = 'none';
    document.getElementById('benef-btn-valider').style.display = 'none';
    benefDossierSelectionne = null;
    document.querySelectorAll('.benef-dossier-item').forEach(e => e.classList.remove('selected'));
}

async function validerCreationBenef() {
    if (!benefClientSelectionne || !benefDossierSelectionne) {
        showToast('⚠️ Sélectionnez un client et un dossier', 'warning');
        return;
    }

    const nom       = document.getElementById('benef-nom').value.trim();
    const telephone = document.getElementById('benef-telephone').value.trim();
    const cniFile   = document.getElementById('benef-cni').files[0];
    const notes     = document.getElementById('benef-notes').value.trim();

    if (!nom) { showToast('⚠️ Le nom est obligatoire', 'warning'); return; }
    if (!cniFile) { showToast('⚠️ La CNI est obligatoire', 'warning'); return; }
    if (cniFile.size > 10 * 1024 * 1024) { showToast('⚠️ CNI trop lourde (max 10 Mo)', 'warning'); return; }

    const btn = document.getElementById('benef-btn-valider');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Création...';

    if (window.EdenLoader) window.EdenLoader.show();

    const formData = new FormData();
    formData.append('nom', nom);
    if (telephone) formData.append('telephone', telephone);
    formData.append('cni', cniFile);
    if (notes) formData.append('notes', notes);
    formData.append('client_id', benefClientSelectionne.id);
    formData.append('date_affectation', new Date().toISOString().split('T')[0]);

    try {
        const url = `/admin/dossiers/${benefDossierSelectionne.id}/beneficiaires`;

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();

        if (window.EdenLoader) window.EdenLoader.hide();
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (response.ok && data.success) {
            showToast('✅ Bénéficiaire créé avec succès !', 'success');
            fermerModalCreationBenef();

            setTimeout(() => {
                const q = benefClientSelectionne.nom;
                document.getElementById('ar-search').value = q;
                document.getElementById('ar-search-clear').style.display = 'block';
                rechercher(q);
            }, 500);
        } else {
            let msg = data.message || 'Erreur lors de la création';
            if (data.errors) msg = Object.values(data.errors).flat().join('\n');
            alert('❌ ' + msg);
            showToast('❌ ' + msg, 'error');
        }
    } catch (err) {
        if (window.EdenLoader) window.EdenLoader.hide();
        btn.disabled = false;
        btn.innerHTML = originalText;
        console.error('Erreur création bénéficiaire :', err);
        showToast('❌ Erreur réseau', 'error');
    }
}

// ════════════════════════════════════════════════════════════════
// UTILITAIRES
// ════════════════════════════════════════════════════════════════
function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.split('-');
    return `${d}/${m}/${y}`;
}

function showToast(message, type = 'info') {
    document.querySelectorAll('.toast-notification').forEach(el => el.remove());

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
</script>
@endsection