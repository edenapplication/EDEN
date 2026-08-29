<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:10pt; color:#1e293b; background:white; padding:30px; }
    .entete { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:30px; border-bottom:3px solid #1d4ed8; padding-bottom:16px; }
    .soc-name { font-size:20pt; font-weight:bold; color:#1e3a5f; }
    .soc-sub  { font-size:9pt; color:#64748b; }
    .titre { text-align:center; margin:28px 0; }
    .titre h1 { font-size:16pt; font-weight:bold; color:#1e3a5f; text-transform:uppercase; letter-spacing:1px; }
    .titre .sous { font-size:10pt; color:#64748b; margin-top:4px; }
    .bloc { border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:16px; background:#fafcff; }
    .bloc-titre { font-size:10pt; font-weight:bold; color:#1d4ed8; text-transform:uppercase; margin-bottom:10px; border-bottom:2px solid #e2e8f0; padding-bottom:6px; letter-spacing:0.5px; }
    .ligne { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #f1f5f9; font-size:10pt; }
    .ligne:last-child { border-bottom:none; }
    .lbl { color:#64748b; font-weight:500; }
    .val { font-weight:600; color:#1e293b; }

    /* Calendrier des jours ouvrés (excluant dimanches) */
    .cal-grid { display:flex; flex-wrap:wrap; gap:4px; margin-top:10px; }
    .jour-box { 
        width:44px; height:40px; border-radius:6px; 
        background:#dbeafe; display:inline-block;
        text-align:center; padding-top:5px; font-size:9pt; font-weight:bold; color:#1d4ed8;
        border:1px solid #bfdbfe; 
    }
    .jour-box.ouvre { background:#dbeafe; color:#1d4ed8; border-color:#bfdbfe; }
    .jour-box.dimanche { background:#f1f5f9; color:#94a3b8; border-color:#e2e8f0; opacity:0.6; }
    .jour-box .dn { font-size:7pt; font-weight:normal; color:#94a3b8; display:block; margin-top:1px; }
    .jour-box.dimanche .dn { color:#cbd5e1; }
    .jour-box .badge-jour { 
        display:inline-block; 
        background:#ef4444; color:white; 
        font-size:6pt; padding:1px 4px; border-radius:3px; 
        margin-top:1px; 
    }
    .jour-box.dimanche .badge-jour { background:#94a3b8; }

    .signature { margin-top:40px; display:flex; justify-content:space-between; }
    .sig-box   { text-align:center; width:200px; }
    .sig-line  { border-top:2px solid #1e3a5f; margin-top:50px; padding-top:8px; font-size:9pt; color:#1e3a5f; font-weight:600; }
    .pied      { margin-top:30px; text-align:center; font-size:8pt; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:10px; }

    .badge-statut { display:inline-block; padding:2px 10px; border-radius:10px; font-size:8pt; font-weight:600; }
    .badge-statut.planifie { background:#fef3c7; color:#92400e; }
    .badge-statut.en_cours { background:#dbeafe; color:#1d4ed8; }
    .badge-statut.termine { background:#dcfce7; color:#15803d; }
    .badge-statut.annule { background:#f1f5f9; color:#475569; }
</style>
</head>
<body>

<div class="entete">
    <div>
        <div class="soc-name">EDEN GROUP</div>
        <div class="soc-sub">Direction des Ressources Humaines</div>
        <div class="soc-sub">Yaoundé, Cameroun</div>
        <div class="soc-sub" style="font-weight:600;margin-top:4px;">N° CONTRIBUABLE : __________</div>
    </div>
    <div style="text-align:right;font-size:9pt;color:#64748b;">
        <div style="font-weight:700;color:#1d4ed8;">N° Attestation : ATT-CONGE-{{ str_pad($conge->id, 4, '0', STR_PAD_LEFT) }}</div>
        <div>Émise le : {{ now()->format('d/m/Y') }}</div>
        <div style="margin-top:6px;">
            <span class="badge-statut {{ $conge->statut }}">
                {{ $conge->statut_label }}
            </span>
        </div>
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
    <div class="ligne"><span class="lbl">Matricule</span>    <span class="val" style="color:#1d4ed8;">{{ $conge->employe?->matricule }}</span></div>
    <div class="ligne"><span class="lbl">Direction</span>    <span class="val">{{ $conge->employe?->direction?->nom ?? '-' }}</span></div>
    <div class="ligne"><span class="lbl">Service</span>      <span class="val">{{ $conge->employe?->service?->nom ?? '-' }}</span></div>
    <div class="ligne"><span class="lbl">Poste</span>        <span class="val">{{ $conge->employe?->intitule_poste ?? $conge->employe?->poste?->nom ?? '-' }}</span></div>
    <div class="ligne"><span class="lbl">Type de contrat</span><span class="val">{{ $conge->employe?->type_contrat ?? '-' }}</span></div>
</div>

{{-- Période de Congé --}}
<div class="bloc">
    <div class="bloc-titre">📅 Période de Congé</div>
    <div class="ligne"><span class="lbl">Date de début</span><span class="val" style="color:#1d4ed8;font-size:11pt;">{{ $conge->date_debut->format('d/m/Y') }}</span></div>
    <div class="ligne"><span class="lbl">Date de fin</span>  <span class="val" style="color:#1d4ed8;font-size:11pt;">{{ $conge->date_fin->format('d/m/Y') }}</span></div>
    <div class="ligne"><span class="lbl">Durée</span>        <span class="val" style="color:#16a34a;font-size:11pt;">{{ $conge->nb_jours }} jours ouvrés</span></div>
    <div class="ligne"><span class="lbl">Motif</span>        <span class="val">{{ $conge->motif ?? 'Congé annuel' }}</span></div>
    <div class="ligne"><span class="lbl">Statut</span>       <span class="val">{{ $conge->statut_label }}</span></div>

    {{-- Calendrier visuel des jours ouvrés (excluant dimanches) --}}
    <div style="margin-top:14px;">
        <div style="font-size:9pt;font-weight:bold;color:#64748b;margin-bottom:8px;">
            📊 CALENDRIER DES JOURS OUVRÉS 
            <span style="font-weight:400;font-size:8pt;color:#94a3b8;">
                (les dimanches ne sont pas comptabilisés)
            </span>
        </div>
        <div class="cal-grid">
            @php
                $date = $conge->date_debut->copy();
                $joursAffiches = 0;
                $maxJours = 21; // Affichage jusqu'à 21 jours pour couvrir les week-ends
                $joursSemaine = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            @endphp
            @for($i = 0; $i < $maxJours && $joursAffiches < $conge->nb_jours; $i++)
                @php
                    $jourNum = $date->dayOfWeek; // 0 = Dimanche, 6 = Samedi
                    $nomJour = $joursSemaine[$jourNum === 0 ? 6 : $jourNum - 1];
                    $estDimanche = $date->isSunday();
                    $estAujourdhui = $date->isToday();
                    $estDansPeriode = $date->between($conge->date_debut, $conge->date_fin);
                    
                    if (!$estDimanche) {
                        $joursAffiches++;
                    }
                    
                    $isOuvre = !$estDimanche && $estDansPeriode;
                    $classe = $estDimanche ? 'dimanche' : 'ouvre';
                @endphp
                <div class="jour-box {{ $classe }}" 
                     title="{{ $date->format('d/m/Y') }} - {{ $nomJour }}{{ $estDimanche ? ' (Dimanche non compté)' : '' }}">
                    {{ $date->format('d') }}
                    <span class="dn">{{ $nomJour }}</span>
                    @if($estDimanche)
                        <span class="badge-jour">✕</span>
                    @endif
                </div>
                @php $date->addDay(); @endphp
            @endfor
        </div>
        <div style="display:flex;gap:16px;font-size:8pt;color:#94a3b8;margin-top:8px;flex-wrap:wrap;">
            <span><span style="display:inline-block;width:14px;height:14px;background:#dbeafe;border-radius:3px;border:1px solid #bfdbfe;margin-right:4px;vertical-align:middle;"></span>Jour ouvré</span>
            <span><span style="display:inline-block;width:14px;height:14px;background:#f1f5f9;border-radius:3px;border:1px solid #e2e8f0;margin-right:4px;vertical-align:middle;"></span>Dimanche (non compté)</span>
            <span style="font-weight:600;color:#16a34a;">Total : {{ $conge->nb_jours }} jour(s) ouvré(s)</span>
        </div>
    </div>
</div>

@if($conge->notes)
<div class="bloc">
    <div class="bloc-titre">📝 Notes</div>
    <div style="font-size:10pt;color:#374151;line-height:1.8;padding:4px 0;">{{ $conge->notes }}</div>
</div>
@endif

{{-- Signatures --}}
<div class="signature">
    <div class="sig-box">
        <div class="sig-line">L'Employé(e)</div>
        <div style="font-size:8pt;color:#94a3b8;margin-top:4px;">Nom et signature</div>
    </div>
    <div class="sig-box">
        <div class="sig-line">Le Responsable RH</div>
        <div style="font-size:8pt;color:#94a3b8;margin-top:4px;">Cachet et signature</div>
    </div>
    <div class="sig-box">
        <div class="sig-line">La Direction</div>
        <div style="font-size:8pt;color:#94a3b8;margin-top:4px;">Visa et signature</div>
    </div>
</div>

<div class="pied">
    Document généré le {{ now()->format('d/m/Y à H:i') }} — Eden Group · Direction des Ressources Humaines
    <br>
    <span style="font-size:7pt;">Conformément à la législation en vigueur · Les dimanches ne sont pas comptabilisés dans la durée des congés</span>
</div>

</body>
</html>