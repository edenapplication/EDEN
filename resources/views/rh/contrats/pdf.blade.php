<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#1e293b; padding:20px; }
    .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; border-bottom:3px solid #1d4ed8; padding-bottom:12px; }
    .company { font-size:16px; font-weight:900; color:#1e3a5f; }
    .company-sub { font-size:9px; color:#64748b; margin-top:2px; }
    .title { font-size:14px; font-weight:700; color:#1e3a5f; text-align:right; }
    .title-sub { font-size:9px; color:#64748b; margin-top:2px; }
    .section { margin-bottom:12px; }
    .section-title { font-size:10px; font-weight:700; color:white; background:#1e3a5f; padding:4px 8px; border-radius:3px; margin-bottom:6px; }
    .grid-2 { display:flex; gap:12px; }
    .grid-2 > div { flex:1; }
    .row { display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px solid #f1f5f9; font-size:9px; }
    .row .lbl { color:#64748b; }
    .row .val { font-weight:600; }
    .badge { display:inline-block; padding:2px 8px; border-radius:8px; font-size:8px; font-weight:700; }
    .footer { margin-top:20px; text-align:center; font-size:8px; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:8px; }
    .sign { margin-top:30px; display:flex; justify-content:space-between; }
    .sign-box { text-align:center; border-top:1px solid #1e3a5f; padding-top:6px; width:180px; font-size:9px; color:#64748b; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="company">EDEN GROUP</div>
        <div class="company-sub">Direction des Ressources Humaines</div>
        <div class="company-sub">Yaoundé, Cameroun</div>
    </div>
    <div>
        <div class="title">CONTRAT DE TRAVAIL</div>
        <div class="title-sub">N° {{ $contrat->numero_contrat }}</div>
        <div style="margin-top:4px;">
            <span class="badge" style="background:{{ $contrat->statut_color }};color:{{ $contrat->statut_text_color }};">
                {{ $contrat->statut_label }}
            </span>
        </div>
    </div>
</div>

{{-- INFOS CONTRAT --}}
<div class="section">
    <div class="section-title">📋 Informations générales</div>
    <div class="grid-2">
        <div>
            <div class="row"><span class="lbl">Numéro de contrat</span><span class="val">{{ $contrat->numero_contrat }}</span></div>
            <div class="row"><span class="lbl">Type de contrat</span><span class="val">{{ $contrat->typeContrat?->nom }}</span></div>
            <div class="row"><span class="lbl">Date de début</span><span class="val">{{ $contrat->date_debut?->format('d/m/Y') }}</span></div>
            <div class="row"><span class="lbl">Date de fin</span><span class="val">{{ $contrat->date_fin?->format('d/m/Y') ?? 'CDI (Indéterminé)' }}</span></div>
        </div>
        <div>
            <div class="row"><span class="lbl">Période d'essai</span><span class="val">{{ $contrat->periode_essai_jours ? $contrat->periode_essai_jours.' jours' : 'Aucune' }}</span></div>
            <div class="row"><span class="lbl">Renouvelable</span><span class="val">{{ $contrat->est_renouvelable ? 'Oui' : 'Non' }}</span></div>
            <div class="row"><span class="lbl">Date de signature</span><span class="val">{{ $contrat->date_signature?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Date de validation</span><span class="val">{{ $contrat->date_validation?->format('d/m/Y') ?? '-' }}</span></div>
        </div>
    </div>
</div>

{{-- EMPLOYÉ --}}
<div class="section">
    <div class="section-title">👤 Informations de l'employé</div>
    <div class="grid-2">
        <div>
            <div class="row"><span class="lbl">Matricule</span><span class="val">{{ $contrat->employe?->matricule }}</span></div>
            <div class="row"><span class="lbl">Nom & Prénom</span><span class="val">{{ $contrat->employe?->nom }} {{ $contrat->employe?->prenom }}</span></div>
            <div class="row"><span class="lbl">Date de naissance</span><span class="val">{{ $contrat->employe?->date_naissance?->format('d/m/Y') ?? '-' }}</span></div>
        </div>
        <div>
            <div class="row"><span class="lbl">Téléphone</span><span class="val">{{ $contrat->employe?->telephone ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Email</span><span class="val">{{ $contrat->employe?->email ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Adresse</span><span class="val">{{ $contrat->employe?->adresse ?? '-' }}</span></div>
        </div>
    </div>
</div>

{{-- POSTE --}}
<div class="section">
    <div class="section-title">🏢 Poste & Affectation</div>
    <div class="grid-2">
        <div>
            <div class="row"><span class="lbl">Direction</span><span class="val">{{ $contrat->direction?->nom ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Service</span><span class="val">{{ $contrat->service?->nom ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Poste</span><span class="val">{{ $contrat->poste?->intitule ?? '-' }}</span></div>
        </div>
        <div>
            <div class="row"><span class="lbl">Agence / Site</span><span class="val">{{ $contrat->agenceSite?->nom ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Responsable</span><span class="val">{{ $contrat->responsable_hierarchique ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Lieu de travail</span><span class="val">{{ $contrat->lieu_travail ?? '-' }}</span></div>
        </div>
    </div>
</div>

{{-- SALAIRE --}}
<div class="section">
    <div class="section-title">💰 Rémunération</div>
    <div class="grid-2">
        <div>
            <div class="row"><span class="lbl">Salaire de base</span><span class="val" style="color:#1d4ed8;font-size:11px;font-weight:700;">{{ number_format($contrat->salaire_base, 0, ',', ' ') }} FCFA</span></div>
            <div class="row"><span class="lbl">Mode de paiement</span><span class="val">{{ $contrat->mode_paiement ?? '-' }}</span></div>
        </div>
        <div>
            <div class="row"><span class="lbl">Devise</span><span class="val">{{ $contrat->devise ?? 'FCFA' }}</span></div>
            <div class="row"><span class="lbl">Horaires</span><span class="val">{{ $contrat->horaires ?? '-' }}</span></div>
        </div>
    </div>
</div>

{{-- CONDITIONS --}}
@if($contrat->conditions_particulieres)
<div class="section">
    <div class="section-title">📝 Conditions particulières</div>
    <div style="font-size:9px;line-height:1.8;padding:6px 8px;background:#f8fafc;border-radius:4px;">{{ $contrat->conditions_particulieres }}</div>
</div>
@endif

{{-- SIGNATURES --}}
<div class="sign">
    <div class="sign-box">L'Employé</div>
    <div class="sign-box">Le Responsable RH</div>
    <div class="sign-box">La Direction</div>
</div>

<div class="footer">
    Document généré le {{ now()->format('d/m/Y à H:i') }} — EDEN GROUP · Direction des Ressources Humaines
</div>

</body>
</html>