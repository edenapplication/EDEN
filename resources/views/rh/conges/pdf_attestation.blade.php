<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:11pt; color:#1e293b; background:white; padding:30px; }
    .entete { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:30px; border-bottom:3px solid #1d4ed8; padding-bottom:16px; }
    .soc-name { font-size:20pt; font-weight:bold; color:#1e3a5f; }
    .soc-sub  { font-size:9pt; color:#64748b; }
    .titre { text-align:center; margin:28px 0; }
    .titre h1 { font-size:16pt; font-weight:bold; color:#1e3a5f; text-transform:uppercase; letter-spacing:1px; }
    .titre .sous { font-size:10pt; color:#64748b; margin-top:4px; }
    .bloc { border:1px solid #e2e8f0; border-radius:6px; padding:16px; margin-bottom:16px; }
    .bloc-titre { font-size:10pt; font-weight:bold; color:#1d4ed8; text-transform:uppercase; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:6px; }
    .ligne { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #f8fafc; font-size:10pt; }
    .lbl { color:#64748b; }
    .val { font-weight:bold; }

    /* Calendrier 15 jours */
    .cal-grid { display:flex; flex-wrap:wrap; gap:4px; margin-top:10px; }
    .jour-box  { width:42px; height:38px; border-radius:4px; background:#dbeafe; display:inline-block;
                  text-align:center; padding-top:5px; font-size:9pt; font-weight:bold; color:#1d4ed8;
                  border:1px solid #bfdbfe; }
    .jour-box.weekend { background:#f1f5f9; color:#94a3b8; border-color:#e2e8f0; }
    .jour-box .dn { font-size:7pt; font-weight:normal; color:#94a3b8; }

    .signature { margin-top:40px; display:flex; justify-content:space-between; }
    .sig-box   { text-align:center; width:200px; }
    .sig-line  { border-top:1px solid #374151; margin-top:50px; padding-top:6px; font-size:9pt; color:#374151; }
    .pied      { margin-top:30px; text-align:center; font-size:8pt; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:10px; }
</style>
</head>
<body>

<div class="entete">
    <div>
        <div class="soc-name">EDEN GROUP</div>
        <div class="soc-sub">Direction des Ressources Humaines</div>
        <div class="soc-sub">Yaoundé, Cameroun</div>
    </div>
    <div style="text-align:right;font-size:9pt;color:#64748b;">
        <div>N° Attestation : ATT-CONGE-{{ str_pad($conge->id, 4, '0', STR_PAD_LEFT) }}</div>
        <div>Émise le : {{ now()->format('d/m/Y') }}</div>
    </div>
</div>

<div class="titre">
    <h1>Attestation de Congé</h1>
    <div class="sous">Document officiel de planification des congés</div>
</div>

{{-- Employé --}}
<div class="bloc">
    <div class="bloc-titre">👤 Informations Employé</div>
    <div class="ligne"><span class="lbl">Nom & Prénom</span><span class="val">{{ $conge->employe?->nom }} {{ $conge->employe?->prenom }}</span></div>
    <div class="ligne"><span class="lbl">Matricule</span>    <span class="val">{{ $conge->employe?->matricule }}</span></div>
    <div class="ligne"><span class="lbl">Direction</span>    <span class="val">{{ $conge->employe?->direction?->nom ?? '-' }}</span></div>
    <div class="ligne"><span class="lbl">Service</span>      <span class="val">{{ $conge->employe?->service?->nom ?? '-' }}</span></div>
    <div class="ligne"><span class="lbl">Poste</span>        <span class="val">{{ $conge->employe?->poste?->nom ?? '-' }}</span></div>
</div>

{{-- Période --}}
<div class="bloc">
    <div class="bloc-titre">📅 Période de Congé</div>
    <div class="ligne"><span class="lbl">Date de début</span><span class="val" style="color:#1d4ed8;">{{ $conge->date_debut->format('d/m/Y') }}</span></div>
    <div class="ligne"><span class="lbl">Date de fin</span>  <span class="val" style="color:#1d4ed8;">{{ $conge->date_fin->format('d/m/Y') }}</span></div>
    <div class="ligne"><span class="lbl">Durée</span>        <span class="val">{{ $conge->nb_jours }} jours calendaires</span></div>
    <div class="ligne"><span class="lbl">Motif</span>        <span class="val">{{ $conge->motif ?? 'Congé annuel' }}</span></div>
    <div class="ligne"><span class="lbl">Statut</span>       <span class="val">{{ $conge->statut_label }}</span></div>

    {{-- Calendrier visuel --}}
    <div style="margin-top:14px;">
        <div style="font-size:9pt;font-weight:bold;color:#64748b;margin-bottom:6px;">CALENDRIER DES 15 JOURS :</div>
        <div class="cal-grid">
            @for($i = 0; $i < 15; $i++)
                @php
                    $jour    = $conge->date_debut->copy()->addDays($i);
                    $isWeek  = $jour->isWeekend();
                    $jours   = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
                    $nomJour = $jours[$jour->dayOfWeek === 0 ? 6 : $jour->dayOfWeek - 1];
                @endphp
                <div class="jour-box {{ $isWeek ? 'weekend' : '' }}">
                    {{ $jour->format('d') }}<br>
                    <span class="dn">{{ $nomJour }}</span>
                </div>
            @endfor
        </div>
        <div style="font-size:8pt;color:#94a3b8;margin-top:6px;">
            ☐ Fond bleu = jour ouvré &nbsp; ☐ Fond gris = weekend
        </div>
    </div>
</div>

@if($conge->notes)
<div class="bloc">
    <div class="bloc-titre">📝 Notes</div>
    <div style="font-size:10pt;color:#374151;line-height:1.6;">{{ $conge->notes }}</div>
</div>
@endif

{{-- Signatures --}}
<div class="signature">
    <div class="sig-box">
        <div class="sig-line">L'Employé(e)</div>
    </div>
    <div class="sig-box">
        <div class="sig-line">Le Responsable RH</div>
    </div>
    <div class="sig-box">
        <div class="sig-line">La Direction</div>
    </div>
</div>

<div class="pied">
    Document généré le {{ now()->format('d/m/Y à H:i') }} — Eden Group · Direction des Ressources Humaines
</div>

</body>
</html>