<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#1e293b; padding:24px; }
    .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; border-bottom:3px solid #1e3a5f; padding-bottom:14px; }
    .company { font-size:16px; font-weight:900; color:#1e3a5f; }
    .company-sub { font-size:9px; color:#64748b; margin-top:2px; }
    .bulletin-title { text-align:right; font-size:13px; font-weight:700; color:#1e3a5f; }
    .bulletin-ref   { font-size:9px; color:#64748b; margin-top:2px; }
    .section { margin-bottom:14px; }
    .section-title { font-size:10px; font-weight:700; color:white; background:#1e3a5f; padding:4px 8px; border-radius:3px; margin-bottom:8px; }
    .grid-2 { display:flex; gap:14px; }
    .grid-2 > div { flex:1; }
    .row { display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid #f1f5f9; font-size:9.5px; }
    .row .lbl { color:#64748b; }
    .row .val { font-weight:600; color:#1e293b; }
    .row .val.green  { color:#16a34a; }
    .row .val.red    { color:#dc2626; }
    .row .val.purple { color:#7c3aed; }
    .row .val.blue   { color:#1d4ed8; }
    .total-box { background:#1e3a5f; color:white; border-radius:8px; padding:12px 16px; margin-top:14px; display:flex; justify-content:space-between; align-items:center; }
    .total-box .lbl { font-size:11px; font-weight:700; }
    .total-box .val { font-size:18px; font-weight:900; }
    .prog { height:6px; background:#e2e8f0; border-radius:3px; margin-top:4px; }
    .prog-fill { height:100%; border-radius:3px; background:#16a34a; }
    .sign { margin-top:30px; display:flex; justify-content:space-between; }
    .sign-box { text-align:center; border-top:1px solid #1e3a5f; padding-top:6px; width:180px; font-size:9px; color:#64748b; }
    .badge { display:inline-block; padding:2px 8px; border-radius:8px; font-size:9px; font-weight:700; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="company">EDEN GROUP</div>
        <div class="company-sub">Bulletin de paie — {{ $bulletin->mois_annee ?? $bulletin->periode }}</div>
    </div>
    <div class="bulletin-title">
        BULLETIN DE PAIE
        <div class="bulletin-ref">
            {{ $bulletin->employe?->matricule }} &nbsp;|&nbsp;
            {{ $bulletin->vague }} &nbsp;|&nbsp;
            Payé le {{ $bulletin->date_paiement }}
        </div>
        <div style="margin-top:4px;">
            <span class="badge" style="background:{{ $bulletin->statut==='payé'?'#dcfce7':($bulletin->statut==='validé'?'#fef3c7':'#f1f5f9')}};color:{{ $bulletin->statut==='payé'?'#15803d':($bulletin->statut==='validé'?'#92400e':'#475569')}};">
                {{ strtoupper($bulletin->statut) }}
            </span>
        </div>
    </div>
</div>

{{-- INFOS EMPLOYÉ --}}
<div class="section">
    <div class="section-title">👤 Informations de l'employé</div>
    <div class="grid-2">
        <div>
            <div class="row"><span class="lbl">Matricule</span><span class="val blue">{{ $bulletin->employe?->matricule }}</span></div>
            <div class="row"><span class="lbl">Nom & Prénom</span><span class="val">{{ $bulletin->employe?->nom }} {{ $bulletin->employe?->prenom }}</span></div>
            <div class="row"><span class="lbl">Direction</span><span class="val">{{ $bulletin->employe?->direction?->nom ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Poste</span><span class="val">{{ $bulletin->employe?->intitule_poste ?? '-' }}</span></div>
        </div>
        <div>
            <div class="row"><span class="lbl">Type contrat</span><span class="val">{{ $bulletin->employe?->type_contrat ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Catégorie</span><span class="val">{{ $bulletin->employe?->categorie ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Date intégration</span><span class="val">{{ $bulletin->employe?->date_integration?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Vague</span><span class="val">{{ $bulletin->vague }}</span></div>
        </div>
    </div>
</div>

{{-- CALCUL SALAIRE --}}
<div class="grid-2">

    {{-- ÉLÉMENTS POSITIFS --}}
    <div class="section">
        <div class="section-title">✅ Éléments de rémunération</div>
        <div class="row"><span class="lbl">Salaire de base</span><span class="val green">{{ number_format($bulletin->salaire_brut, 0, ',', ' ') }} FCFA</span></div>
        <div class="row"><span class="lbl">Salaire horaire</span><span class="val">{{ number_format($bulletin->salaire_heure ?? 0, 4, ',', ' ') }} FCFA/h</span></div>
        <div class="row"><span class="lbl">Nb heures sup.</span><span class="val">{{ $bulletin->nb_heures_sup ?? 0 }} h</span></div>
        <div class="row"><span class="lbl">Montant heures sup.</span><span class="val green">{{ number_format($bulletin->montant_heures_sup ?? 0, 0, ',', ' ') }} FCFA</span></div>
        <div class="row"><span class="lbl">Prime</span><span class="val green">{{ number_format($bulletin->prime ?? 0, 0, ',', ' ') }} FCFA</span></div>
        <div class="row"><span class="lbl">Indemnité</span><span class="val green">{{ number_format($bulletin->indemnite ?? 0, 0, ',', ' ') }} FCFA</span></div>
        <div class="row"><span class="lbl">Montant fixe</span><span class="val green">{{ number_format($bulletin->montant_fixe ?? 0, 0, ',', ' ') }} FCFA</span></div>
        <div class="row" style="background:#f0fff4;font-weight:700;padding:5px 0;">
            <span class="lbl" style="font-weight:700;">TOTAL BRUT</span>
            <span class="val green" style="font-size:11px;">{{ number_format($bulletin->salaire_brut + ($bulletin->montant_heures_sup??0) + ($bulletin->prime??0) + ($bulletin->indemnite??0) + ($bulletin->montant_fixe??0), 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    {{-- DÉDUCTIONS --}}
    <div class="section">
        <div class="section-title">❌ Déductions</div>
        <div class="row"><span class="lbl">Retards</span><span class="val red">{{ number_format($bulletin->montant_retard ?? 0, 0, ',', ' ') }} FCFA</span></div>
        <div class="row"><span class="lbl">Absences</span><span class="val red">{{ number_format($bulletin->montant_absence ?? 0, 0, ',', ' ') }} FCFA</span></div>
        <div class="row"><span class="lbl">Acompte</span><span class="val purple">{{ number_format($bulletin->acompte ?? 0, 0, ',', ' ') }} FCFA</span></div>
        <div class="row"><span class="lbl">Remboursement prêt</span><span class="val purple">{{ number_format($bulletin->pret ?? 0, 0, ',', ' ') }} FCFA</span></div>
        <div class="row"><span class="lbl">Sanction</span><span class="val red">{{ number_format($bulletin->montant_sanction ?? 0, 0, ',', ' ') }} FCFA</span></div>
        <div class="row"><span class="lbl">Imputation salaire</span><span class="val red">{{ number_format($bulletin->imputation_salaire ?? 0, 0, ',', ' ') }} FCFA</span></div>
        <div class="row"><span class="lbl">Frais bancaires</span><span class="val red">{{ number_format($bulletin->frais_bancaires ?? 0, 0, ',', ' ') }} FCFA</span></div>
        <div class="row"><span class="lbl">CNPS (part salariale)</span><span class="val red">{{ number_format($bulletin->cnps ?? 0, 0, ',', ' ') }} FCFA</span></div>
        @php
            $totalDeductions = ($bulletin->montant_retard??0) + ($bulletin->montant_absence??0) + ($bulletin->acompte??0) + ($bulletin->pret??0) + ($bulletin->montant_sanction??0) + ($bulletin->imputation_salaire??0) + ($bulletin->frais_bancaires??0) + ($bulletin->cnps??0);
        @endphp
        <div class="row" style="background:#fff1f2;font-weight:700;padding:5px 0;">
            <span class="lbl" style="font-weight:700;">TOTAL DÉDUCTIONS</span>
            <span class="val red" style="font-size:11px;">{{ number_format($totalDeductions, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>
</div>

{{-- NET À PAYER --}}
<div class="total-box">
    <span class="lbl">💰 NET À PAYER</span>
    <span class="val">{{ number_format($bulletin->net_a_payer, 0, ',', ' ') }} FCFA</span>
</div>

@if($bulletin->note_bulletin ?? false)
<div class="section" style="margin-top:12px;">
    <div class="section-title">📝 Note</div>
    <div style="padding:8px;background:#f8fafc;border-radius:4px;font-size:9.5px;line-height:1.6;">{{ $bulletin->note_bulletin }}</div>
</div>
@endif

<div class="sign">
    <div class="sign-box">Signature Employé<br><br><br></div>
    <div class="sign-box">Visa RH<br><br><br></div>
    <div class="sign-box">Direction<br><br><br></div>
</div>

<div style="text-align:center;margin-top:20px;font-size:8px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:8px;">
    Document généré le {{ now()->format('d/m/Y à H:i') }} — EDEN GROUP — Confidentiel
</div>

</body>
</html>