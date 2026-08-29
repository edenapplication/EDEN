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

    .badge-cnps { display:inline-block; padding:1px 6px; border-radius:4px; font-size:7px; font-weight:600; }
    .badge-cnps.affilie { background:#dcfce7; color:#15803d; }
    .badge-cnps.non_affilie { background:#fee2e2; color:#b91c1c; }
    .badge-cnps.en_cours { background:#fef3c7; color:#92400e; }
    .badge-cnps.radie { background:#f1f5f9; color:#475569; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="company">EDEN GROUP — Fiche Employé</div>
        <div style="font-size:9px;color:#64748b;">Générée le {{ now()->format('d/m/Y à H:i') }}</div>
        <div style="font-size:8px;color:#94a3b8;margin-top:2px;">
            @if(!$employe->actif) 🚫 ARCHIVÉ @endif
        </div>
    </div>
    <div style="text-align:center;">
        @if($employe->photo_path)
            <img src="{{ storage_path('app/public/' . $employe->photo_path) }}" class="photo" alt="Photo">
        @else
            <div class="photo-placeholder">{{ strtoupper(substr($employe->prenom,0,1)) }}{{ strtoupper(substr($employe->nom,0,1)) }}</div>
        @endif
        <div style="font-size:10px;font-weight:700;color:#1e3a5f;margin-top:4px;">{{ $employe->matricule }}</div>
        <div style="font-size:8px;color:#94a3b8;">{{ $employe->intitule_poste ?? '-' }}</div>
    </div>
</div>

{{-- KPIs --}}
<div class="kpi-row">
    <div class="kpi"><div class="v">{{ $totalAbsences }}</div><div class="l">Jours absences</div></div>
    <div class="kpi" style="border-color:#dc2626;"><div class="v" style="color:#dc2626;">{{ $totalRetards }}</div><div class="l">Retards</div></div>
    <div class="kpi" style="border-color:#16a34a;"><div class="v" style="color:#16a34a;">{{ $employe->solde_conges_restant }}</div><div class="l">Congés restants</div></div>
    <div class="kpi" style="border-color:#7c3aed;">
        <div class="v" style="color:#7c3aed;font-size:10px;">{{ $pretRestant > 0 ? number_format($pretRestant,0,',',' ') : '0' }}</div>
        <div class="l">Prêt restant (FCFA)</div>
    </div>
    <div class="kpi" style="border-color:#f59e0b;">
        <div class="v" style="color:#f59e0b;font-size:10px;">{{ $dernierBulletin ? number_format($dernierBulletin->net_a_payer,0,',',' ') : '-' }}</div>
        <div class="l">Dernier net (FCFA)</div>
    </div>
</div>

<div class="grid-2">
    <div>
        {{-- IDENTITÉ --}}
        <div class="section">
            <div class="section-title">👤 Identité</div>
            <div class="row"><span class="lbl">Nom & Prénom</span><span class="val">{{ $employe->nom }} {{ $employe->prenom }}</span></div>
            <div class="row"><span class="lbl">Sexe</span><span class="val">{{ $employe->sexe === 'M' ? 'Masculin' : 'Féminin' }}</span></div>
            <div class="row"><span class="lbl">Nationalité</span><span class="val">{{ $employe->nationalite ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Date naissance</span><span class="val">{{ $employe->date_naissance?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Lieu naissance</span><span class="val">{{ $employe->lieu_naissance ?? '-' }}</span></div>
            <div class="row"><span class="lbl">N° CNI</span><span class="val">{{ $employe->numero_cni ?? '-' }}</span></div>
            <div class="row"><span class="lbl">NIU</span><span class="val">{{ $employe->niu ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Origines</span><span class="val">{{ $employe->origines ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Situation matrimoniale</span><span class="val">{{ $employe->situation_matrimoniale }}</span></div>
            <div class="row"><span class="lbl">Nb enfants</span><span class="val">{{ $employe->nb_enfants }}</span></div>
            <div class="row"><span class="lbl">État de santé</span><span class="val">{{ $employe->etat_sante }}</span></div>
        </div>

        {{-- CONTACT --}}
        <div class="section">
            <div class="section-title">📞 Contact</div>
            <div class="row"><span class="lbl">Téléphone</span><span class="val">{{ $employe->telephone ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Email</span><span class="val">{{ $employe->email ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Adresse</span><span class="val">{{ $employe->adresse ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Contact urgence</span><span class="val">{{ $employe->personne_a_contacter ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Tél. urgence</span><span class="val">{{ $employe->tel_urgence ?? '-' }}</span></div>
        </div>
    </div>

    <div>
        {{-- POSTE & CONTRAT --}}
        <div class="section">
            <div class="section-title">🏢 Poste & Contrat</div>
            <div class="row"><span class="lbl">Direction</span><span class="val">{{ $employe->direction?->nom ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Service</span><span class="val">{{ $employe->service?->nom ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Agence / Site</span><span class="val">{{ $employe->agenceSite?->nom ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Poste</span><span class="val">{{ $employe->intitule_poste ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Niveau / Échelon</span><span class="val">{{ $employe->niveauCheleon?->nom ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Responsable hiérarchique</span><span class="val">{{ $employe->responsableHierarchique?->nom_complet ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Type contrat</span><span class="val">{{ $employe->type_contrat }}</span></div>
            <div class="row"><span class="lbl">Catégorie</span><span class="val">{{ $employe->categorie ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Date intégration</span><span class="val">{{ $employe->date_integration?->format('d/m/Y') }}</span></div>
            <div class="row"><span class="lbl">Prise de fonction</span><span class="val">{{ $employe->date_prise_fonction?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Fin période d'essai</span><span class="val">{{ $employe->date_fin_periode_essai?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Ancienneté</span><span class="val">{{ $employe->anciennete }}</span></div>
            <div class="row"><span class="lbl">Vague paiement</span><span class="val">{{ $employe->vague_paiement ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Mode de paiement</span><span class="val">{{ $employe->mode_paiement ?? '-' }}</span></div>
            <div class="row" style="border-bottom:2px solid #e2e8f0;padding-bottom:4px;">
                <span class="lbl" style="font-weight:700;">Salaire base</span>
                <span class="val" style="color:#1d4ed8;font-weight:700;font-size:11px;">{{ number_format($employe->salaire_base, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        {{-- CNPS --}}
        <div class="section">
            <div class="section-title">🏛️ CNPS & Cotisations</div>
            <div class="row"><span class="lbl">N° CNPS</span><span class="val" style="color:#1d4ed8;font-weight:700;">{{ $employe->numero_cnps ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Date d'affiliation</span><span class="val">{{ $employe->date_affiliation_cnps?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Centre CNPS</span><span class="val">{{ $employe->centre_cnps ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Situation affiliation</span>
                <span class="val">
                    @if($employe->situation_affiliation_cnps)
                        <span class="badge-cnps {{ $employe->situation_affiliation_cnps }}">
                            {{ ucfirst(str_replace('_', ' ', $employe->situation_affiliation_cnps)) }}
                        </span>
                    @else
                        -
                    @endif
                </span>
            </div>
        </div>

        {{-- FORMATION --}}
        <div class="section">
            <div class="section-title">🎓 Formation académique</div>
            <div class="row"><span class="lbl">Niveau académique</span><span class="val">{{ $employe->niveau_academique ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Spécialité</span><span class="val">{{ $employe->specialite_academique ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Diplôme recrutement</span><span class="val">{{ $employe->diplome_recrutement ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Poste précédent</span><span class="val">{{ $employe->exp_poste_precedent ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Entreprise précédente</span><span class="val">{{ $employe->entreprise_precedente ?? '-' }}</span></div>
            <div class="row"><span class="lbl">Durée expérience</span><span class="val">{{ $employe->duree_exp_precedente ?? '-' }}</span></div>
        </div>
    </div>
</div>

{{-- DERNIERS BULLETINS --}}
@if($employe->bulletins->count())
<div class="section">
    <div class="section-title">💰 Derniers bulletins de paie</div>
    <table>
        <thead><tr><th>Période</th><th>Vague</th><th>Brut</th><th>CNPS</th><th>Net</th><th>Statut</th></tr></thead>
        <tbody>
        @foreach($employe->bulletins as $b)
            <tr>
                <td>{{ $b->mois_annee ?? $b->periode }}</td>
                <td>{{ $b->vague }}</td>
                <td>{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
                <td style="color:#dc2626;">{{ number_format($b->cnps ?? 0, 0, ',', ' ') }}</td>
                <td style="font-weight:700;color:#16a34a;">{{ number_format($b->net_a_payer, 0, ',', ' ') }}</td>
                <td>{{ $b->statut }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ABSENCES RÉCENTES --}}
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

{{-- PRÊTS EN COURS --}}
@if($employe->prets->where('statut', 'en_cours')->count())
<div class="section">
    <div class="section-title">🏦 Prêts en cours</div>
    <table>
        <thead><tr><th>Type</th><th>Montant</th><th>Remboursé</th><th>Restant</th></tr></thead>
        <tbody>
        @foreach($employe->prets->where('statut', 'en_cours') as $p)
            <tr>
                <td>{{ ucfirst($p->type) }}</td>
                <td>{{ number_format($p->montant, 0, ',', ' ') }}</td>
                <td style="color:#16a34a;">{{ number_format($p->montant_rembourse, 0, ',', ' ') }}</td>
                <td style="color:#dc2626;font-weight:700;">{{ number_format($p->montant - $p->montant_rembourse, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- SANCTIONS RÉCENTES --}}
@if($employe->sanctions->count())
<div class="section">
    <div class="section-title">⚠️ Sanctions récentes</div>
    <table>
        <thead><tr><th>Date</th><th>Type</th><th>Motif</th><th>Montant</th></tr></thead>
        <tbody>
        @foreach($employe->sanctions as $s)
            <tr>
                <td>{{ $s->date?->format('d/m/Y') }}</td>
                <td>{{ $s->type }}</td>
                <td>{{ $s->motif }}</td>
                <td style="color:#dc2626;">{{ number_format($s->montant, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- NOTES --}}
@if($employe->notes)
<div class="section">
    <div class="section-title">📝 Notes</div>
    <div style="font-size:8.5px;line-height:1.6;padding:6px;background:#f8fafc;border-radius:4px;border:1px solid #e2e8f0;">
        {{ $employe->notes }}
    </div>
</div>
@endif

<div style="text-align:center;margin-top:16px;font-size:8px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:8px;">
    EDEN GROUP — Fiche confidentielle — {{ now()->format('d/m/Y à H:i') }}
</div>

</body>
</html>