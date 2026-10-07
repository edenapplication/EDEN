{{-- APRÈS --}}
@extends('admin.affectations.layout')
@section('content')

<style>
.ouv-container { max-width: 720px; margin: 40px auto; }
.ouv-card {
    background: white; border-radius: 18px; padding: 36px 40px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.08);
    border-top: 6px solid #1d4ed8;
}
.ouv-header { text-align: center; margin-bottom: 30px; }
.ouv-header .icon {
    width: 80px; height: 80px; border-radius: 20px;
    background: linear-gradient(135deg, #1d4ed8, #7c3aed);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 40px; color: white; margin-bottom: 16px;
    box-shadow: 0 8px 24px rgba(29,78,216,0.3);
}
.ouv-header h2 { margin: 0 0 8px 0; font-weight: 900; color: #1e3a5f; font-size: 24px; }
.ouv-header p { margin: 0; color: #64748b; font-size: 14px; }

.ouv-alert {
    border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;
    display: flex; align-items: flex-start; gap: 12px;
    font-size: 13px;
}
.ouv-alert.warning { background: #fef3c7; border: 2px solid #fcd34d; color: #92400e; }
.ouv-alert .icon-alert { font-size: 24px; flex-shrink: 0; }

.ouv-form-label {
    font-size: 12px; font-weight: 700; color: #64748b;
    display: block; margin-bottom: 8px; text-transform: uppercase;
    letter-spacing: 0.5px;
}
.ouv-input-date {
    width: 100%; padding: 16px 20px; font-size: 16px; font-weight: 700;
    border: 3px solid #e2e8f0; border-radius: 12px;
    transition: all 0.2s; color: #1e3a5f; text-align: center;
}
.ouv-input-date:focus {
    border-color: #1d4ed8; outline: none;
    box-shadow: 0 0 0 4px rgba(29,78,216,0.1);
}
.ouv-info {
    background: #eff6ff; border-left: 4px solid #1d4ed8;
    padding: 14px 18px; border-radius: 10px;
    margin: 20px 0; font-size: 12px; color: #1e40af;
}
.ouv-actions { display: flex; gap: 12px; margin-top: 24px; }
.ouv-btn-primary {
    flex: 1;
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    color: white; border: none; border-radius: 12px;
    padding: 16px 24px; font-size: 15px; font-weight: 800;
    cursor: pointer; transition: all 0.2s;
    box-shadow: 0 4px 14px rgba(29,78,216,0.3);
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.ouv-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(29,78,216,0.4);
}
.ouv-btn-secondary {
    background: white; color: #64748b;
    border: 2px solid #e2e8f0; border-radius: 12px;
    padding: 16px 24px; font-size: 14px; font-weight: 700;
    cursor: pointer; transition: all 0.2s;
    text-decoration: none;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.ouv-btn-secondary:hover { border-color: #1d4ed8; color: #1d4ed8; }

@media (max-width: 768px) {
    .ouv-container { margin: 20px auto; }
    .ouv-card { padding: 24px; }
    .ouv-actions { flex-direction: column; }
}
</style>

<div class="ouv-container">
    <div class="ouv-card">

        @if(session('warning'))
            <div class="ouv-alert warning">
                <div class="icon-alert">⚠️</div>
                <div>
                    <strong>Redirection automatique</strong><br>
                    {{ session('warning') }}
                </div>
            </div>
        @endif

        <div class="ouv-header">
            <div class="icon">📅</div>
            <h2>Ouverture de la programmation</h2>
            <p>Saisissez la date du jour d'implantation pour ouvrir la semaine correspondante</p>
        </div>

        <div class="ouv-info">
            ℹ️ La programmation affichera uniquement les lignes dont la
            <strong>date d'implantation</strong> appartient à la
            <strong>semaine de la date saisie</strong>.
            <br><br>
            ⚠️ Si des lignes de la <strong>semaine précédente</strong> sont encore en attente,
            vous serez automatiquement redirigé vers cette semaine pour les traiter d'abord.
        </div>

        <form method="GET" action="{{ route('affectations.programmation') }}" onsubmit="return validerForm()">
            <label class="ouv-form-label">📆 Date du jour d'implantation *</label>
            <input type="date"
                   name="date"
                   id="dateReference"
                   class="ouv-input-date"
                   value="{{ now()->format('Y-m-d') }}"
                   required
                   onchange="mettreAJourSemaine()">

            <div id="apercuSemaine" style="text-align:center; margin-top:14px; font-size:13px; color:#64748b;">
                📅 Semaine du <strong id="debutSemaine">—</strong> au <strong id="finSemaine">—</strong>
            </div>

            <div class="ouv-actions">
                <a href="{{ route('affectations.liste') }}" class="ouv-btn-secondary">
                    ← Retour
                </a>
                <button type="submit" class="ouv-btn-primary">
                    🚀 Ouvrir la programmation
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function mettreAJourSemaine() {
    const input = document.getElementById('dateReference');
    if (!input || !input.value) return;

    const date  = new Date(input.value + 'T00:00:00');
    const debut = new Date(date);
    const fin   = new Date(date);

    const jour = date.getDay();
    const diffLundi = (jour === 0 ? -6 : 1 - jour);
    debut.setDate(date.getDate() + diffLundi);
    fin.setDate(debut.getDate() + 6);

    document.getElementById('debutSemaine').textContent = formatDate(debut);
    document.getElementById('finSemaine').textContent   = formatDate(fin);
}

function formatDate(d) {
    const jour = String(d.getDate()).padStart(2, '0');
    const mois = String(d.getMonth() + 1).padStart(2, '0');
    return jour + '/' + mois + '/' + d.getFullYear();
}

function validerForm() {
    const input = document.getElementById('dateReference');
    if (!input || !input.value) {
        alert('⚠️ Veuillez saisir la date du jour.');
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', mettreAJourSemaine);
</script>

@endsection