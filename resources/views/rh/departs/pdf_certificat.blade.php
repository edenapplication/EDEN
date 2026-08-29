<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1e293b; padding:30px; }
    .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; border-bottom:3px solid #1d4ed8; padding-bottom:12px; }
    .company { font-size:18px; font-weight:900; color:#1e3a5f; }
    .company-sub { font-size:9px; color:#64748b; margin-top:2px; }
    .title { font-size:14px; font-weight:700; color:#1e3a5f; text-align:right; }
    .title-sub { font-size:9px; color:#64748b; margin-top:2px; }
    .content { margin:20px 0; line-height:1.8; font-size:11px; }
    .sign { margin-top:40px; display:flex; justify-content:space-between; }
    .sign-box { text-align:center; border-top:1px solid #1e3a5f; padding-top:6px; width:200px; font-size:9px; color:#64748b; }
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
        <div class="title">CERTIFICAT DE CESSATION</div>
        <div class="title-sub">N° {{ $certificat->reference }}</div>
        <div class="title-sub">Émis le {{ $certificat->date_emission?->format('d/m/Y') }}</div>
    </div>
</div>

<div class="content">
    <p style="font-weight:700;font-size:12px;">OBJET : Certificat de cessation de travail</p>
    <br>

    <p>Je soussigné(e), <strong>{{ $certificat->employe?->nom }} {{ $certificat->employe?->prenom }}</strong>,</p>
    <p>De nationalité <strong>{{ $certificat->employe?->nationalite ?? 'Camerounaise' }}</strong>,</p>
    <p>Né(e) le <strong>{{ $certificat->employe?->date_naissance?->format('d/m/Y') }}</strong> à <strong>{{ $certificat->employe?->lieu_naissance ?? '-' }}</strong>,</p>
    <p>De profession <strong>{{ $certificat->employe?->intitule_poste ?? '-' }}</strong>,</p>
    <br>

    <p>Certifie que j'ai cessé définitivement mes fonctions au sein de la société <strong>EDEN GROUP</strong> le <strong>{{ $certificat->date_effet?->format('d/m/Y') }}</strong>.</p>
    <br>

    @if($certificat->motif)
        <p><strong>Motif :</strong> {{ $certificat->motif }}</p>
        <br>
    @endif

    @if($certificat->mention_speciale)
        <p><strong>Mention spéciale :</strong> {{ $certificat->mention_speciale }}</p>
        <br>
    @endif

    <p>Le présent certificat lui est délivré pour servir et valoir ce que de droit.</p>
    <br>

    <p>Fait à <strong>Yaoundé</strong>, le <strong>{{ now()->format('d/m/Y') }}</strong></p>
</div>

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