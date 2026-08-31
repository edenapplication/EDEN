@extends('rh.layout')
@section('content')

<style>
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:520px; max-height:90vh; overflow-y:auto; }
.stat-card { background:white; border-radius:10px; padding:14px; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
.detail-rows { display:none; background:#f8fafc; }
.emp-total-row { cursor:pointer; }
.emp-total-row:hover td { background:#eff6ff !important; }
.badge-retard { background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:10px; font-weight:800; font-size:13px; }
.badge-sup    { background:#dcfce7; color:#16a34a; padding:2px 8px; border-radius:8px; font-weight:700; font-size:11px; }
.horaire-ref  { font-size:11px; color:#64748b; background:#f1f5f9; padding:4px 10px; border-radius:6px; display:inline-block; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">⏰ Retards</h2>
        <span class="horaire-ref">Horaire : 08h00 → 18h00</span>
    </div>
    <div class="d-flex gap-2">
    <a href="{{ route('rh.retards.import-excel') }}" class="btn btn-outline-success btn-sm">📊 Import Excel</a>
    <a href="{{ route('rh.retards.nettoyer-doublons') }}" class="btn btn-outline-warning btn-sm" 
       onclick="return confirm('Supprimer les doublons de retards ?')">
        🧹 Nettoyer doublons
    </a>
    <a href="{{ route('rh.retards.pdf-liste', ['mois' => $mois]) }}" class="btn btn-outline-danger btn-sm">🖨️ PDF</a>
    <button onclick="openModal('addModal')" class="btn btn-primary">+ Enregistrer</button>
</div>

</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show">
        <strong>Erreurs :</strong>
        <ul class="mb-0 mt-1" style="font-size:11px;">
            @foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- FILTRES --}}
<form method="GET" class="d-flex gap-2 mb-4 flex-wrap align-items-end"
      style="background:white;padding:12px 16px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;display:block;">Mois</label>
        <input type="month" name="mois" class="form-control form-control-sm"
               value="{{ $mois }}" onchange="this.form.submit()">
    </div>
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;display:block;">Direction</label>
        <select name="direction_id" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">Toutes</option>
            @foreach($directions as $d)
                <option value="{{ $d->id }}" {{ $dirId == $d->id ? 'selected':'' }}>{{ $d->nom }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;display:block;">Employé</label>
        <select name="employe_id" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">Tous</option>
            @foreach($employes as $e)
                <option value="{{ $e->id }}" {{ $empId == $e->id ? 'selected':'' }}>
                    {{ $e->nom }} {{ $e->prenom }}
                </option>
            @endforeach
        </select>
    </div>
    <a href="{{ route('rh.retards.index') }}" class="btn btn-outline-secondary btn-sm" style="align-self:flex-end;">Reset</a>
</form>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #dc2626;text-align:center;">
            <div style="font-size:28px;font-weight:900;color:#dc2626;">{{ $retards->count() }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Retards ce mois</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #f59e0b;text-align:center;">
            <div style="font-size:28px;font-weight:900;color:#f59e0b;">{{ $parEmploye->count() }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Employés concernés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #dc2626;text-align:center;">
            <div style="font-size:22px;font-weight:900;color:#dc2626;">{{ $retards->sum('minutes_retard') }} min</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Total minutes retard</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #16a34a;text-align:center;">
            <div style="font-size:22px;font-weight:900;color:#16a34a;">{{ $retards->sum('minutes_sup') }} min</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Total heures sup.</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- PAR DIRECTION --}}
    <div class="col-md-5">
        <div class="stat-card">
            <div style="font-weight:700;font-size:13px;color:#1e3a5f;margin-bottom:12px;">📊 Par direction</div>
            @forelse($parDirection as $dir => $data)
                <div style="margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-weight:700;font-size:12px;">{{ $dir ?? 'Non défini' }}</span>
                        <span style="background:#fee2e2;color:#dc2626;padding:2px 8px;border-radius:8px;font-size:11px;font-weight:700;">
                            {{ $data['nb'] }} retard(s)
                        </span>
                    </div>
                    @foreach($data['employes'] as $emp)
                        <div style="font-size:11px;color:#64748b;padding-left:10px;">
                            👤 {{ $emp['nom'] }}
                            @if($emp['service'] !== '-')
                                <span style="color:#94a3b8;">— {{ $emp['service'] }}</span>
                            @endif
                            : <strong style="color:#dc2626;">{{ $emp['nb'] }}</strong>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="text-muted text-center" style="font-size:12px;">Aucun retard ce mois</div>
            @endforelse
        </div>
    </div>

    {{-- PAR SERVICE --}}
    <div class="col-md-3">
        <div class="stat-card">
            <div style="font-weight:700;font-size:13px;color:#1e3a5f;margin-bottom:12px;">🗂️ Par service</div>
            @forelse($parService as $svc => $data)
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px;">
                    <span>{{ $svc ?? 'Non défini' }}</span>
                    <span style="background:#fee2e2;color:#dc2626;padding:1px 8px;border-radius:8px;font-size:11px;font-weight:700;">
                        {{ $data['nb'] }}
                    </span>
                </div>
            @empty
                <div class="text-muted text-center" style="font-size:12px;">—</div>
            @endforelse
        </div>
    </div>

    {{-- LÉGENDE CALCUL --}}
    <div class="col-md-4">
        <div class="stat-card" style="background:#f0fdf4;border:1px solid #bbf7d0;">
            <div style="font-weight:700;font-size:13px;color:#15803d;margin-bottom:10px;">⚙️ Logique de calcul</div>
            <div style="font-size:12px;color:#166534;line-height:1.9;">
                <div>🕗 <strong>Début :</strong> 08h00 | <strong>Fin :</strong> 18h00</div>
                <div>🔴 <strong>Retard</strong> = H.Arrivée − 08h00 <em>(si arrivée après 08h00)</em></div>
                <div>🟢 <strong>H.Sup</strong> = H.Départ − 18h00 <em>(si départ après 18h00)</em></div>
                <hr style="border-color:#86efac;margin:8px 0;">
                <div style="font-size:11px;color:#15803d;">
                    <strong>Ex :</strong> Arrivée 08h25, Départ 19h10<br>
                    → Retard = <strong>25 min</strong> | H.Sup = <strong>70 min</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABLEAU PAR EMPLOYÉ --}}
<div style="background:white;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);overflow:hidden;margin-bottom:20px;">
    <div style="padding:14px 18px;border-bottom:2px solid #e2e8f0;font-weight:700;color:#1e3a5f;font-size:14px;">
        👥 Récapitulatif par employé —
        {{ \Carbon\Carbon::createFromFormat('Y-m', $mois)->translatedFormat('F Y') }}
        <span style="font-size:11px;color:#94a3b8;font-weight:400;margin-left:8px;">
            (cliquer pour voir le détail)
        </span>
    </div>
    <table class="table table-hover table-bordered mb-0" style="font-size:12px;">
        <thead class="table-dark">
            <tr>
                <th>Matricule</th>
                <th>Nom & Prénom</th>
                <th>Direction</th>
                <th>Service</th>
                <th>Nb retards</th>
                <th>Total retard (min)</th>
                <th>Total H.Sup (min)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($parEmploye as $idx => $item)
            <tr class="emp-total-row" onclick="toggleDetail({{ $idx }})">
                <td><strong style="color:#1d4ed8;">{{ $item['employe']?->matricule }}</strong></td>
                <td><strong>{{ $item['employe']?->nom }} {{ $item['employe']?->prenom }}</strong></td>
                <td>{{ $item['employe']?->direction?->nom ?? '-' }}</td>
                <td>{{ $item['employe']?->service?->nom ?? '-' }}</td>
                <td><span class="badge-retard">{{ $item['nb'] }}</span></td>
                <td style="color:#dc2626;font-weight:700;">
                    {{ $item['duree_min'] > 0 ? $item['duree_min'] . ' min' : '-' }}
                </td>
                <td>
                    @if($item['heures_sup_min'] > 0)
                        <span class="badge-sup">{{ $item['heures_sup_min'] }} min</span>
                    @else
                        <span style="color:#94a3b8;">-</span>
                    @endif
                </td>
                <td>
                    <button onclick="event.stopPropagation(); openAddForEmploye({{ $item['employe']?->id }}, '{{ addslashes($item['employe']?->nom . ' ' . $item['employe']?->prenom) }}')"
                            class="btn btn-primary btn-sm" style="font-size:10px;">+ Retard</button>
                </td>
            </tr>
            {{-- Détail des lignes --}}
            <tr id="detail-{{ $idx }}" class="detail-rows">
                <td colspan="8" style="padding:0;">
                    <table style="width:100%;font-size:11px;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#e2e8f0;color:#475569;">
                                <th style="padding:5px 12px;width:40px;"></th>
                                <th style="padding:5px;">Date</th>
                                <th style="padding:5px;">H. Arrivée</th>
                                <th style="padding:5px;">H. Départ</th>
                                <th style="padding:5px;color:#dc2626;">Retard</th>
                                <th style="padding:5px;color:#16a34a;">H. Sup</th>
                                <th style="padding:5px;">Motif</th>
                                <th style="padding:5px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($item['lignes'] as $r)
                            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                <td style="padding:5px 12px;"></td>
                                <td style="padding:5px;">📅 {{ $r->date }}</td>
                                <td style="padding:5px;font-weight:600;">
                                    {{ $r->heure_arrivee ? '🕐 '.$r->heure_arrivee : '-' }}
                                </td>
                                <td style="padding:5px;font-weight:600;">
                                    {{ $r->heure_depart  ? '🕕 '.$r->heure_depart  : '-' }}
                                </td>
                                <td style="padding:5px;color:#dc2626;font-weight:700;">
                                    {{ $r->minutes_retard > 0 ? $r->minutes_retard.' min' : '-' }}
                                </td>
                                <td style="padding:5px;color:#16a34a;font-weight:700;">
                                    {{ $r->minutes_sup > 0 ? $r->minutes_sup.' min' : '-' }}
                                </td>
                                <td style="padding:5px;color:#64748b;">{{ $r->motif ?? '-' }}</td>
                                <td style="padding:5px;">
                                    <button onclick="openEditRetard(
                                        {{ $r->id }},
                                        {{ $r->employe_id }},
                                        '{{ $r->date }}',
                                        '{{ $r->heure_arrivee }}',
                                        '{{ $r->heure_depart }}',
                                        '{{ addslashes($r->motif) }}')"
                                        class="btn btn-warning btn-sm" style="font-size:9px;padding:2px 6px;">✏️</button>
                                    <form action="{{ route('rh.retards.destroy', $r->id) }}" method="POST"
                                          style="display:inline" onsubmit="return confirm('Supprimer ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm"
                                                style="font-size:9px;padding:2px 6px;">🗑</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Aucun retard ce mois</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeAll()"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;" id="addModalTitle">⏰ Enregistrer un retard</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form method="POST" action="{{ route('rh.retards.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Employé <span class="text-danger">*</span></label>
                <select name="employe_id" class="form-control" id="add_employe_id" required>
                    <option value="">-- Choisir --</option>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">H. Arrivée</label>
                <input type="time" name="heure_arrivee" class="form-control" placeholder="08:25">
                <div style="font-size:10px;color:#64748b;margin-top:3px;">Référence : 08h00</div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">H. Départ</label>
                <input type="time" name="heure_depart" class="form-control" placeholder="18:00">
                <div style="font-size:10px;color:#64748b;margin-top:3px;">H.sup après 18h00</div>
            </div>
            <div class="col-12">
                <div style="background:#fef9c3;border-radius:8px;padding:8px 12px;font-size:11px;color:#92400e;">
                    ⚙️ Le retard et les heures supplémentaires sont calculés automatiquement.
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" class="form-control" placeholder="Ex: Embouteillages">
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </div>
    </form>
</div>

{{-- MODAL MODIFICATION --}}
<div class="modal-overlay" id="overlayEdit" onclick="closeAll()"></div>
<div class="modal-box" id="editModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">✏️ Modifier le retard</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form id="editRetardForm" method="POST" action="">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Employé</label>
                <select name="employe_id" class="form-control" id="edit_r_employe" required>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Date</label>
                <input type="date" name="date" class="form-control" id="edit_r_date" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">H. Arrivée</label>
                <input type="time" name="heure_arrivee" class="form-control" id="edit_r_ha">
                <div style="font-size:10px;color:#64748b;margin-top:3px;">Référence : 08h00</div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">H. Départ</label>
                <input type="time" name="heure_depart" class="form-control" id="edit_r_hd">
                <div style="font-size:10px;color:#64748b;margin-top:3px;">H.sup après 18h00</div>
            </div>
            <div class="col-12">
                <div style="background:#fef9c3;border-radius:8px;padding:8px 12px;font-size:11px;color:#92400e;">
                    ⚙️ Le retard et les heures supplémentaires sont recalculés automatiquement à la sauvegarde.
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" class="form-control" id="edit_r_motif">
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-warning">💾 Mettre à jour</button>
        </div>
    </form>
</div>

{{-- MODAL IMPORT EXCEL --}}
<div class="modal-overlay" id="overlayImport" onclick="closeAll()"></div>
<div class="modal-box" id="importModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#15803d;font-weight:800;">📊 Import Excel des retards</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form method="POST" action="{{ route('rh.retards.import-excel') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Fichier Excel</label>
            <input type="file" name="fichier_excel" class="form-control" accept=".xlsx,.xls,.csv" required>
        </div>
        <div style="background:#f0fdf4;border-radius:8px;padding:12px;font-size:12px;color:#15803d;margin-bottom:16px;">
            <strong>Structure requise (ligne 1 = entête ignorée) :</strong>
            <table style="width:100%;border-collapse:collapse;margin-top:6px;font-size:11px;">
                <tr style="background:#dcfce7;font-weight:700;">
                    <td style="padding:3px 8px;border:1px solid #86efac;">A : Matricule</td>
                    <td style="padding:3px 8px;border:1px solid #86efac;">B : NOMS</td>
                    <td style="padding:3px 8px;border:1px solid #86efac;">C : Date</td>
                    <td style="padding:3px 8px;border:1px solid #86efac;">D : H.A</td>
                    <td style="padding:3px 8px;border:1px solid #86efac;">E : H.D</td>
                </tr>
                <tr style="color:#374151;font-size:10px;">
                    <td style="padding:3px 8px;border:1px solid #86efac;">EDG_08_24_0001</td>
                    <td style="padding:3px 8px;border:1px solid #86efac;">DUPONT Jean</td>
                    <td style="padding:3px 8px;border:1px solid #86efac;">2026-05-01</td>
                    <td style="padding:3px 8px;border:1px solid #86efac;">08:25</td>
                    <td style="padding:3px 8px;border:1px solid #86efac;">19:10</td>
                </tr>
            </table>
            <div style="margin-top:8px;font-size:11px;">
                🔴 <strong>H.A</strong> = Heure d'arrivée réelle &nbsp;|&nbsp;
                🟢 <strong>H.D</strong> = Heure de départ réelle<br>
                Retard calculé si H.A &gt; 08h00 &nbsp;|&nbsp; H.Sup si H.D &gt; 18h00
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-success">📊 Importer</button>
        </div>
    </form>
</div>

@endsection
@section('scripts')
<script>
function openModal(id) {
    ['overlayAdd','overlayEdit','overlayImport','addModal','editModal','importModal']
        .forEach(el => { const e = document.getElementById(el); if(e) e.style.display='none'; });
    const map = { addModal:'overlayAdd', editModal:'overlayEdit', importModal:'overlayImport' };
    const ov  = map[id];
    if (ov) document.getElementById(ov).style.display = 'block';
    document.getElementById(id).style.display = 'block';
}
function closeAll() {
    ['overlayAdd','overlayEdit','overlayImport','addModal','editModal','importModal']
        .forEach(id => { const el = document.getElementById(id); if(el) el.style.display='none'; });
}
function openAddForEmploye(id, nom) {
    document.getElementById('addModalTitle').textContent = '⏰ Retard — ' + nom;
    document.getElementById('add_employe_id').value = id;
    openModal('addModal');
}
function openEditRetard(id, employeId, date, ha, hd, motif) {
    document.getElementById('editRetardForm').action = '/rh/retards/' + id;
    document.getElementById('edit_r_employe').value  = employeId;
    document.getElementById('edit_r_date').value     = date;
    document.getElementById('edit_r_ha').value       = ha;
    document.getElementById('edit_r_hd').value       = hd;
    document.getElementById('edit_r_motif').value    = motif;
    openModal('editModal');
}
function toggleDetail(idx) {
    const row = document.getElementById('detail-' + idx);
    row.style.display = (row.style.display === 'table-row') ? 'none' : 'table-row';
}
</script>
@endsection