<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1e293b; padding:30px; }
    .header { text-align:center; margin-bottom:30px; border-bottom:2px solid #1e3a5f; padding-bottom:14px; }
    .header h1 { font-size:18px; color:#1e3a5f; }
    .header p { font-size:11px; color:#64748b; }
    .section { margin-bottom:20px; }
    .section h3 { font-size:12px; font-weight:700; color:#1e3a5f; background:#f1f5f9; padding:6px 10px; border-radius:4px; margin-bottom:10px; }
    .row { display:flex; margin-bottom:8px; }
    .label { width:180px; font-weight:600; color:#64748b; flex-shrink:0; }
    .value { flex:1; color:#1e293b; }
    .note-box { border:1px solid #e2e8f0; border-radius:6px; padding:12px; min-height:60px; background:#f8fafc; line-height:1.6; white-space:pre-wrap; word-break:break-word; }
    .footer { margin-top:40px; display:flex; justify-content:space-between; }
    .sign-box { text-align:center; width:200px; border-top:1px solid #1e3a5f; padding-top:6px; font-size:10px; color:#64748b; }
    .badge { display:inline-block; padding:2px 10px; border-radius:10px; font-size:10px; font-weight:700; }
    .badge-att { background:#fef3c7; color:#92400e; }
    .badge-ok  { background:#dcfce7; color:#15803d; }
    .badge-ko  { background:#fee2e2; color:#b91c1c; }
</style>
</head>
<body>

<div class="header">
    <h1>EDEN GROUP — Demande d'absence / Permission</h1>
    <p>Référence : {{ $absence->reference }} &nbsp;|&nbsp; Générée le {{ now()->format('d/m/Y à H:i') }}</p>
</div>

<div class="section">
    <h3>👤 Informations de l'employé</h3>
    <div class="row"><span class="label">Matricule :</span><span class="value">{{ $absence->employe?->matricule }}</span></div>
    <div class="row"><span class="label">Nom & Prénom :</span><span class="value">{{ $absence->employe?->nom }} {{ $absence->employe?->prenom }}</span></div>
    <div class="row"><span class="label">Direction :</span><span class="value">{{ $absence->employe?->direction?->nom ?? '-' }}</span></div>
    <div class="row"><span class="label">Poste :</span><span class="value">{{ $absence->employe?->intitule_poste ?? '-' }}</span></div>
</div>

<div class="section">
    <h3>📋 Détails de la demande</h3>
    <div class="row"><span class="label">Type :</span><span class="value">{{ $absence->type_absence }}</span></div>
    <div class="row"><span class="label">Date début :</span><span class="value">{{ $absence->date_debut?->format('d/m/Y') }}</span></div>
    <div class="row"><span class="label">Date fin :</span><span class="value">{{ $absence->date_fin?->format('d/m/Y') }}</span></div>
    <div class="row"><span class="label">Nombre de jours :</span><span class="value"><strong>{{ $absence->nombre_jours }} jour(s)</strong></span></div>
    <div class="row">
        <span class="label">Statut :</span>
        <span class="value">
            <span class="badge {{ $absence->statut === 'approuvé' ? 'badge-ok' : ($absence->statut === 'refusé' ? 'badge-ko' : 'badge-att') }}">
                {{ ucfirst($absence->statut) }}
            </span>
        </span>
    </div>
</div>

@if($absence->motif || $absence->note)
<div class="section">
    <h3>📝 Motif & Notes</h3>
    @if($absence->motif)
        <div class="row"><span class="label">Motif :</span><span class="value">{{ $absence->motif }}</span></div>
    @endif
    @if($absence->note)
        <div style="margin-top:8px;">
            <div class="label" style="margin-bottom:6px;">Note / Remarques :</div>
            <div class="note-box">{{ $absence->note }}</div>
        </div>
    @endif
</div>
@endif

<div class="footer">
    <div class="sign-box">Signature de l'employé</div>
    <div class="sign-box">Visa RH</div>
    <div class="sign-box">Approbation Direction</div>
</div>

</body>
</html>