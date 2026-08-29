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
    .title { font-size:13px; font-weight:700; color:#1e3a5f; text-align:right; }
    .title-sub { font-size:9px; color:#64748b; margin-top:2px; }
    .section { margin-bottom:12px; }
    .section-title { font-size:10px; font-weight:700; color:white; background:#1e3a5f; padding:4px 8px; border-radius:3px; margin-bottom:6px; }
    .row { display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid #f1f5f9; font-size:9px; }
    .row .lbl { color:#64748b; }
    .row .val { font-weight:600; }
    .total { background:#f0fdf4; padding:8px; border-radius:4px; font-size:11px; }
    .sign { margin-top:30px; display:flex; justify-content:space-between; }
    .sign-box { text-align:center; border-top:1px solid #1e3a5f; padding-top:6px; width:180px; font-size:9px; color:#64748b; }
    .footer { margin-top:16px; text-align:center; font-size:7px; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:6px; }
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
        <div class="title">SOLDE DE TOUT COMPTE</div>
        <div class="title-sub">N° {{ $solde->depart?->reference ?? 'STC-'.str_pad($solde->id, 6, '0', STR_PAD_LEFT) }}</div>
        <div class="title-sub">Émis le {{ now()->format('d/m/Y') }}</div>
    </div>
</div>

<div class="section">
    <div class="section-title">👤 Informations de l'employé</div>
    <div class="row"><span class="lbl">Matricule</span><span class="val">{{ $solde->employe?->matricule }}</span></div>
    <div class="row"><span class="lbl">Nom & Prénom</span><span class="val">{{ $solde->employe?->nom }} {{ $solde->employe?->prenom }}</span></div>
    <div class="row"><span class="lbl">Date d'entrée</span><span class="val">{{ $solde->employe?->date_integration?->format('d/m/Y') }}</span></div>
    <div class="row"><span class="lbl">Date de sortie</span><span class="val">{{ $solde->depart?->date_depart?->format('d/m/Y') }}</span></div>
    <div class="row"><span class="lbl">Motif de départ</span><span class="val">{{ $solde->depart?->motifDepart?->nom ?? $solde->depart?->motif_libre ?? '-' }}</span></div>
</div>

<div class="section">
    <div class="section-title">💰 Détail du calcul</div>
    <div class="row"><span class="lbl">Salaire de base</span><span class="val">{{ number_format($solde->salaire_base, 0, ',', ' ') }} FCFA</span></div>
    <div class="row"><span class="lbl">Indemnité de congés</span><span class="val">{{ number_format($solde->indemnite_conges, 0, ',', ' ') }} FCFA</span></div>
    <div class="row"><span class="lbl">Indemnité de préavis</span><span class="val">{{ number_format($solde->indemnite_preavis, 0, ',', ' ') }} FCFA</span></div>
    <div class="row"><span class="lbl">Prime d'ancienneté</span><span class="val">{{ number_format($solde->prime_anciennete, 0, ',', ' ') }} FCFA</span></div>
    <div class="row" style="border-bottom:2px solid #e2e8f0;padding-bottom:8px;font-weight:700;">
        <span class="lbl" style="font-weight:700;">Total brut</span>
        <span class="val" style="color:#1d4ed8;font-size:11px;">{{ number_format($solde->total_brut, 0, ',', ' ') }} FCFA</span>
    </div>
    <div class="row"><span class="lbl">CNPS (part salariale)</span><span class="val" style="color:#dc2626;">- {{ number_format($solde->cnps, 0, ',', ' ') }} FCFA</span></div>
    <div class="row"><span class="lbl">Impôts / retenues</span><span class="val" style="color:#dc2626;">- {{ number_format($solde->impots, 0, ',', ' ') }} FCFA</span></div>
    <div class="row total" style="border-bottom:2px solid #16a34a;padding-bottom:8px;">
        <span style="font-weight:700;font-size:11px;">NET À PAYER</span>
        <span style="font-size:15px;font-weight:900;color:#16a34a;">{{ number_format($solde->net_a_payer, 0, ',', ' ') }} FCFA</span>
    </div>
</div>

<div class="section">
    <div class="section-title">💳 Modalités de paiement</div>
    <div class="row"><span class="lbl">Date de paiement</span><span class="val">{{ $solde->date_paiement?->format('d/m/Y') ?? '-' }}</span></div>
    <div class="row"><span class="lbl">Mode de paiement</span><span class="val">{{ $solde->mode_paiement ?? '-' }}</span></div>
    <div class="row"><span class="lbl">Référence paiement</span><span class="val">{{ $solde->reference_paiement ?? '-' }}</span></div>
</div>

<div class="sign">
    <div class="sign-box">L'Employé</div>
    <div class="sign-box">Le Responsable RH</div>
    <div class="sign-box">La Direction</div>
</div>

<div class="footer">
    Document généré le {{ now()->format('d/m/Y à H:i') }} — EDEN GROUP · Direction des Ressources Humaines
    <br>
    Ce document atteste du solde de tout compte de l'employé.
</div>

</body>
</html>