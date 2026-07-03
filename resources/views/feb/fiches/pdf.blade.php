<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:DejaVu Sans, sans-serif; font-size:9pt; color:#1e293b; padding:20px; }
    .entete { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid #1d4ed8; padding-bottom:14px; margin-bottom:20px; }
    .soc { font-size:15pt; font-weight:bold; color:#1e3a5f; }
    .info-user { font-size:9pt; color:#64748b; line-height:1.8; }
    .titre-doc { text-align:center; margin:0 0 20px; }
    .titre-doc h1 { font-size:16pt; font-weight:bold; color:#1e3a5f; text-transform:uppercase; }
    .titre-doc .desc { font-size:9.5pt; color:#64748b; margin-top:4px; }
    .section-bloc { margin-bottom:24px; }
    .section-titre { background:#1e3a5f; color:white; padding:7px 12px; font-size:10.5pt; font-weight:bold; border-radius:4px 4px 0 0; }
    table { width:100%; border-collapse:collapse; }
    th { background:#dbeafe; color:#1d4ed8; padding:7px 8px; font-size:8.5pt; text-align:left; border:1px solid #bfdbfe; }
    td { padding:6px 8px; font-size:8.5pt; border:1px solid #e2e8f0; }
    tr:nth-child(even) td { background:#f8fafc; }
    .num-col { color:#94a3b8; text-align:center; width:28px; }
    .vide { color:#94a3b8; font-style:italic; font-size:8pt; padding:12px; text-align:center; }
    .pied { margin-top:24px; text-align:center; font-size:8pt; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:8px; }
    .user-bloc { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px 14px; margin-bottom:18px; }
    .user-bloc .u-row { display:flex; gap:24px; font-size:9pt; }
    .u-row span { color:#64748b; margin-right:4px; }
    .u-row strong { color:#1e3a5f; }
</style>
</head>
<body>

{{-- En-tête --}}
<div class="entete">
    <div>
        <div class="soc">EDEN GROUP</div>
        <div style="font-size:9pt;color:#64748b;">Fiche d'Expression des Besoins</div>
    </div>
    <div style="text-align:right;font-size:8.5pt;color:#64748b;">
        <div>N° {{ str_pad($fiche->id, 5, '0', STR_PAD_LEFT) }}</div>
        <div>Créée le : {{ $fiche->created_at->format('d/m/Y') }}</div>
        @if($fiche->soumise_at)
        <div>Soumise le : {{ $fiche->soumise_at->format('d/m/Y à H:i') }}</div>
        @endif
    </div>
</div>

{{-- Informations utilisateur --}}
<div class="user-bloc">
    <div class="u-row">
        <div><span>Nom :</span><strong>{{ $fiche->utilisateur?->nom_complet }}</strong></div>
        <div><span>Poste :</span><strong>{{ $fiche->utilisateur?->poste ?? '-' }}</strong></div>
        <div><span>Agence :</span><strong>{{ $fiche->utilisateur?->agence?->nom ?? '-' }}</strong></div>
    </div>
</div>

{{-- Titre fiche --}}
<div class="titre-doc">
    <h1>{{ $fiche->titre }}</h1>
    @if($fiche->description)
    <div class="desc">{{ $fiche->description }}</div>
    @endif
</div>

{{-- Sections --}}
@foreach($fiche->sections as $section)
<div class="section-bloc">
    <div class="section-titre">{{ $loop->iteration }}. {{ $section->titre }}</div>

    @if($section->colonnes->count() > 0 && $section->lignes->count() > 0)
    <table>
        <thead>
            <tr>
                <th class="num-col">#</th>
                @foreach($section->colonnes as $col)
                <th>{{ $col->libelle }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($section->lignes as $ligne)
            <tr>
                <td class="num-col">{{ $ligne->numero_ligne }}</td>
                @foreach($section->colonnes as $col)
                <td>{{ $ligne->valeurs[$col->id] ?? '' }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="vide">Section vide</div>
    @endif
</div>
@endforeach

<div class="pied">
    EDEN GROUP · Fiche d'Expression des Besoins N° {{ str_pad($fiche->id, 5, '0', STR_PAD_LEFT) }} · {{ now()->format('d/m/Y') }}
</div>
</body>
</html>