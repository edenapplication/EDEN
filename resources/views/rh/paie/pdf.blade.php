<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:DejaVu Sans,sans-serif; font-size:9px; color:#1e293b; }
.header { background:#1e3a5f; color:white; padding:14px 18px; margin-bottom:14px; }
.header h1 { font-size:14px; font-weight:bold; }
.header p  { font-size:8px; opacity:0.8; }
.emp-info { display:table; width:100%; margin-bottom:12px; }
.emp-col  { display:table-cell; padding:8px; background:#f8fafc; border:1px solid #e2e8f0; }
.section { margin-bottom:10px; }
.section-title { background:#1d4ed8; color:white; padding:5px 8px; font-size:8px; font-weight:bold; margin-bottom:4px; }
table { width:100%; border-collapse:collapse; }
tr { border-bottom:1px solid #e2e8f0; }
td { padding:4px 6px; font-size:8px; }
td:last-child { text-align:right; font-weight:bold; }
.total-row { background:#1e3a5f; color:white; }
.total-row td { padding:6px 8px; font-size:10px; font-weight:bold; }
.footer { text-align:center; font-size:7px; color:#94a3b8; margin-top:12px; border-top:1px solid #e2e8f0; padding-top:6px; }
.signature { display:table; width:100%; margin-top:20px; }
.sig-col { display:table-cell; text-align:center; border-top:1px solid #1e3a5f; padding-top:6px; font-size:8px; width:33%; }
</style>
</head>
<body>

<div class="header">
    <h1>BULLETIN DE PAIE — {{ strtoupper($bulletin->mois_annee ?? $bulletin->periode) }}</h1>
    <p>{{ $bulletin->vague }} — Généré le {{ now()->format('d/m/Y à H:i') }}</p>
</div>

{{-- Infos employé --}}
<div class="emp-info">
    <div class="emp-col">
        <div style="font-size:8px;color:#64748b;font-weight:600;">EMPLOYÉ</div>
        <div style="font-size:11px;font-weight:bold;margin-top:2px;">{{ $bulletin->employe?->nom }} {{ $bulletin->employe?->prenom }}</div>
    </div>
    <div class="emp-col">
        <div style="font-size:8px;color:#64748b;font-weight:600;">MATRICULE</div>
        <div style="font-size:11px;font-weight:bold;color:#1d4ed8;margin-top:2px;">{{ $bulletin->employe?->matricule }}</div>
    </div>
    <div class="emp-col">
        <div style="font-size:8px;color:#64748b;font-weight:600;">POSTE</div>
        <div style="font-size:10px;font-weight:bold;margin-top:2px;">{{ $bulletin->employe?->intitule_poste ?? '-' }}</div>
    </div>
    <div class="emp-col">
        <div style="font-size:8px;color:#64748b;font-weight:600;">CATÉGORIE</div>
        <div style="font-size:11px;font-weight:bold;margin-top:2px;">{{ $bulletin->employe?->categorie ?? '-' }}</div>
    </div>
    <div class="emp-col">
        <div style="font-size:8px;color:#64748b;font-weight:600;">CONTRAT</div>
        <div style="font-size:11px;font-weight:bold;margin-top:2px;">{{ $bulletin->employe?->type_contrat }}</div>
    </div>
    <div class="emp-col">
        <div style="font-size:8px;color:#64748b;font-weight:600;">DATE PAIEMENT</div>
        <div style="font-size:11px;font-weight:bold;margin-top:2px;">{{ $bulletin->date_paiement?->format('d/m/Y') }}</div>
    </div>
</div>

{{-- ÉLÉMENTS POSITIFS --}}
<div class="section">
    <div class="section-title">✅ ÉLÉMENTS DU SALAIRE</div>
    <table>
        <tr><td>Salaire brut de base</td><td>{{ number_format($bulletin->salaire_brut, 0, ',', ' ') }} FCFA</td></tr>
        <tr><td>Salaire à l'heure (base 173,33h)</td><td>{{ number_format($bulletin->salaire_heure, 2, ',', ' ') }} FCFA/h</td></tr>
        @if($bulletin->nb_heures_sup > 0)
        <tr><td>Heures supplémentaires ({{ $bulletin->nb_heures_sup }}h)</td><td>{{ number_format($bulletin->montant_heures_sup, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        @if($bulletin->prime > 0)
        <tr><td>Prime</td><td>{{ number_format($bulletin->prime, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        @if($bulletin->indemnite > 0)
        <tr><td>Indemnité</td><td>{{ number_format($bulletin->indemnite, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        <tr><td>Montant fixe</td><td>{{ number_format($bulletin->montant_fixe, 0, ',', ' ') }} FCFA</td></tr>
    </table>
</div>

{{-- DÉDUCTIONS --}}
<div class="section">
    <div class="section-title" style="background:#dc2626;">❌ DÉDUCTIONS</div>
    <table>
        @if($bulletin->montant_retard > 0)
        <tr><td>Retards ({{ $bulletin->nb_retards }} fois)</td><td>- {{ number_format($bulletin->montant_retard, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        @if($bulletin->montant_absence > 0)
        <tr><td>Absences ({{ $bulletin->nb_absences }} jours)</td><td>- {{ number_format($bulletin->montant_absence, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        @if($bulletin->acompte > 0)
        <tr><td>Acompte</td><td>- {{ number_format($bulletin->acompte, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        @if($bulletin->pret > 0)
        <tr><td>Remboursement prêt @if($bulletin->duree_pret)({{ $bulletin->duree_pret }} mois)@endif</td><td>- {{ number_format($bulletin->pret, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        @if($bulletin->montant_sanction > 0)
        <tr><td>Sanction @if($bulletin->motif_sanction)— {{ $bulletin->motif_sanction }}@endif</td><td>- {{ number_format($bulletin->montant_sanction, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        @if($bulletin->cnps > 0)
        <tr><td>CNPS</td><td>- {{ number_format($bulletin->cnps, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        @if($bulletin->frais_bancaires > 0)
        <tr><td>Frais bancaires</td><td>- {{ number_format($bulletin->frais_bancaires, 0, ',', ' ') }} FCFA</td></tr>
        @endif
        @if($bulletin->imputation_salaire > 0)
        <tr><td>Imputation sur salaire</td><td>- {{ number_format($bulletin->imputation_salaire, 0, ',', ' ') }} FCFA</td></tr>
        @endif
    </table>
</div>

{{-- NET --}}
<table>
    <tr class="total-row">
        <td>NET À PAYER</td>
        <td style="font-size:14px;">{{ number_format($bulletin->net_a_payer, 0, ',', ' ') }} FCFA</td>
    </tr>
</table>

@if($bulletin->observation)
<div style="background:#fef9c3;padding:8px;border-radius:4px;margin-top:8px;font-size:8px;">
    <strong>Observation :</strong> {{ $bulletin->observation }}
</div>
@endif

{{-- SIGNATURES --}}
<div class="signature">
    <div class="sig-col">DRH</div>
    <div class="sig-col">Direction Générale</div>
    <div class="sig-col">Signature Employé</div>
</div>

<div class="footer">
    EDEN GROUP — Bulletin de paie confidentiel — {{ $bulletin->mois_annee }} — {{ $bulletin->vague }}
</div>

</body>
</html>