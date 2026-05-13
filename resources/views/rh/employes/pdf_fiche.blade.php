<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:9.5px; color:#1e293b; padding:20px; }
    .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:3px solid #1e3a5f; padding-bottom:12px; }
    .company { font-size:15px; font-weight:900; color:#1e3a5f; }
    .photo { width:70px; height:70px; border-radius:50%; object-fit:cover; border:2px solid #1d4ed8; }
    .photo-placeholder { width:70px; height:70px; border-radius:50%; background:#1d4ed8; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:900; color:white; }
    .section { margin-bottom:12px; }
    .section-title { font-size:10px; font-weight:700; color:white; background:#1e3a5f; padding:4px 8px; border-radius:3px; margin-bottom:6px; }
    .grid-2 { display:flex; gap:12px; }
    .grid-2 > div { flex:1; }
    .row { display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px solid #f1f5f9; font-size:9px; }
    .row .lbl { color:#64748b; }
    .row .val { font-weight:600; }
    table { width:100%; border-collapse:collapse; margin-top:4px; }
    thead tr { background:#1e3a5f; color:white; }
    thead th { padding:4px; font-size:8px; text-align:left; }
    tbody td { padding:4px; border-bottom:1px solid #f1f5f9; font-size:8.5px; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    .kpi-row { display:flex; gap:8px; margin-bottom:12px; }
    .kpi { background:#f8fafc; border-radius:6px; padding:8px; border-top:2px solid #1d4ed8; text-align:center; flex:1; }
    .kpi .v { font-size:14px; font-weight:800; color:#1d4ed8; }
    .kpi .l { font-size:7.5px; color:#64748b; text-transform:uppercase; font-weight:600; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="company">EDEN GROUP — Fiche Employé</div>
        <div style="font-size:9px;color:#64748b;">Générée le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>
    <div style="text-align:center;">
        @if($employe->photo_path)
            <img src="{{ storage_path('app/public/' . $employe->photo_path) }}" class="photo" alt="Photo">
        @else
            <div class="photo-placeholder">{{ strtoupper(substr($employe->prenom,0,1)) }}{{ strtoupper(substr($employe->nom,0,1)) }}</div>
        @endif
        <div style="font-size:10px;font-weight:700;color:#1e3a5f;margin-top:4px;">{{ $employe->matricule }}</div>
    </div>
</div>

<div class="kpi-row">
    <div class="kpi"><div class="v">{{ $totalAbsences }}</div><div class="l">Jours absences</div></div>
    <div class="kpi" style="border-color:#dc2626;"><div class="v" style="color:#dc2626;">{{ $totalRetards }}</div><div class="l">Retards</div></div>
    <div class="kpi" style="border-color:#16a34a;"><div class="v" style="color:#16a34a;">{{ $employe->solde_conges_restant }}</div><div class="l">Congés restants</div></div>
    <div class="kpi" style="border-color:#7c3aed;">
        <div class="v" style="color:#7c3aed;font-size:10px;">{{ $pretRestant > 0 ? number_format($pretRestant,0,',','&nbsp;') : '0' }}</div>
        <div class="l">Prêt restant (FCFA)</div>
    </div>
    <div class="kpi" style="border-color:#f59e0b;">
        <div class="v" style="color:#f59e0b;font-size:10px;">{{ $dernierBulletin ? number_format($dernierBulletin->net_a_payer,0,',','&nbsp;') : '-' }}</div>
        <div class="l">Dernier net (FCFA)</div>
    </div>
</div>

<div class="grid-2">
    <div>
        <div class="section">
            <div class="section-title">👤 Identité</div>
            <div class="row"><span class="lbl">Nom & Prénom</span><span class="val">{{ $employe->nom }} {{ $employe->prenom }}</span></div>
            <div class="row"><span class="lbl">Sexe</span><span class="val">{{ $employe->sexe === 'M' ? 'Masculin' : 'Féminin' }}</span></div>
            <div class="row"><span class="lbl">Date naissance</span><span class="val">{{ $employe->date_naissance?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Lieu naissance</span><span class="val">{{ $employe->lieu_naissance ?? '-' }}</span></div>
            <div class="row"><span class="lbl">N° CNI</span><span class="val">{{ $employe->numero_cni ?? '-' }}</span></div>
            <div class="row"><span class="lbl">NIU</span><span class="val">{{ $employe->niu ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Situation</span><span class="val">{{ $employe->situation_matrimoniale }}</span></div>
            <div class="row"><span class="lbl">Nb enfants</span><span class="val">{{ $employe->nb_enfants }}</span></div>
        </div>
        <div class="section">
            <div class="section-title">📞 Contact</div>
            <div class="row"><span class="lbl">Téléphone</span><span class="val">{{ $employe->telephone ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Adresse</span><span class="val">{{ $employe->adresse ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Contact urgence</span><span class="val">{{ $employe->personne_a_contacter ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Tél. urgence</span><span class="val">{{ $employe->tel_urgence ?? '-' }}</span></div>
        </div>
    </div>
    <div>
        <div class="section">
            <div class="section-title">🏢 Poste & Contrat</div>
            <div class="row"><span class="lbl">Direction</span><span class="val">{{ $employe->direction?->nom ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Service</span><span class="val">{{ $employe->service?->nom ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Poste</span><span class="val">{{ $employe->intitule_poste ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Type contrat</span><span class="val">{{ $employe->type_contrat }}</span></div>
            <div class="row"><span class="lbl">Catégorie</span><span class="val">{{ $employe->categorie ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Date intégration</span><span class="val">{{ $employe->date_integration?->format('d/m/Y') }}</span></div>
            <div class="row"><span class="lbl">Ancienneté</span><span class="val">{{ $employe->anciennete }}</span></div>
            <div class="row"><span class="lbl">Vague</span><span class="val">{{ $employe->vague_paiement ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Salaire base</span><span class="val" style="color:#1d4ed8;font-weight:700;">{{ number_format($employe->salaire_base, 0, ',', ' ') }} FCFA</span></div>
        </div>
        <div class="section">
            <div class="section-title">🎓 Formation</div>
            <div class="row"><span class="lbl">Niveau</span><span class="val">{{ $employe->niveau_academique ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Spécialité</span><span class="val">{{ $employe->specialite_academique ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Diplôme recrut.</span><span class="val">{{ $employe->diplome_recrutement ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Exp. précédente</span><span class="val">{{ $employe->exp_poste_precedent ?? '-' }}</span></div>
        </div>
    </div>
</div>

{{-- DERNIERS BULLETINS --}}
@if($employe->bulletins->count())
<div class="section">
    <div class="section-title">💰 Derniers bulletins de paie</div>
    <table>
        <thead><tr><th>Période</th><th>Vague</th><th>Brut</th><th>Net</th><th>Statut</th></tr></thead>
        <tbody>
        @foreach($employe->bulletins as $b)
            <tr>
                <td>{{ $b->mois_annee ?? $b->periode }}</td>
                <td>{{ $b->vague }}</td>
                <td>{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
                <td style="font-weight:700;color:#16a34a;">{{ number_format($b->net_a_payer, 0, ',', ' ') }}</td>
                <td>{{ $b->statut }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ABSENCES --}}
@if($employe->absences->count())
<div class="section">
    <div class="section-title">🗓️ Absences récentes</div>
    <table>
        <thead><tr><th>Référence</th><th>Type</th><th>Début</th><th>Fin</th><th>Jours</th><th>Statut</th></tr></thead>
        <tbody>
        @foreach($employe->absences as $a)
            <tr>
                <td>{{ $a->reference }}</td>
                <td>{{ $a->type_absence }}</td>
                <td>{{ $a->date_debut?->format('d/m/Y') }}</td>
                <td>{{ $a->date_fin?->format('d/m/Y') }}</td>
                <td style="text-align:center;font-weight:700;">{{ $a->nombre_jours }}</td>
                <td>{{ $a->statut }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

<div style="text-align:center;margin-top:16px;font-size:8px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:8px;">
    EDEN GROUP — Fiche confidentielle — {{ now()->format('d/m/Y') }}
</div>

</body>
</html>