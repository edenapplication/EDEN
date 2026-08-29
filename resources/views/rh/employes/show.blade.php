@extends('rh.layout')
@section('content')

<style>
.info-card { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.info-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px; }
.info-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
.info-row span:first-child { color:#64748b; }
.info-row span:last-child  { font-weight:600; color:#1e3a5f; }
.tab-btn { padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; background:#f1f5f9; color:#64748b; cursor:pointer; border:none; transition:0.2s; }
.tab-btn:hover { background:#e2e8f0; }
.tab-btn.active { background:#1d4ed8; color:white; }
.tab-panel { display:none; }
.tab-panel.active { display:block; }
.doc-row { display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:#f8fafc; border-radius:8px; margin-bottom:6px; border-left:3px solid #1d4ed8; }

/* Avatar */
.avatar-wrap { position:relative; width:90px; height:90px; margin:0 auto; }
.avatar-wrap img,
.avatar-initiales {
    width:90px; height:90px; border-radius:50%;
    object-fit:cover; border:3px solid #1d4ed8;
    display:flex; align-items:center; justify-content:center;
    font-size:28px; font-weight:900; color:white;
    background:linear-gradient(135deg,#1d4ed8,#7c3aed);
}
.avatar-photo-btn {
    position:absolute; bottom:0; right:0;
    width:28px; height:28px; border-radius:50%;
    background:#f59e0b; border:2px solid white;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; font-size:13px;
    box-shadow:0 2px 6px rgba(0,0,0,0.2);
}
.avatar-photo-btn:hover { background:#d97706; }
#photoInput { display:none; }

.badge-cnps { padding:2px 10px; border-radius:10px; font-size:10px; font-weight:600; }
.badge-cnps.affilie { background:#dcfce7; color:#15803d; }
.badge-cnps.non_affilie { background:#fee2e2; color:#b91c1c; }
.badge-cnps.en_cours { background:#fef3c7; color:#92400e; }
.badge-cnps.radie { background:#f1f5f9; color:#475569; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('rh.employes.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <span class="ms-3" style="font-size:20px;font-weight:800;color:#1e3a5f;">
            {{ $employe->nom }} {{ $employe->prenom }}
        </span>
        <span style="background:#dbeafe;color:#1d4ed8;padding:3px 10px;border-radius:10px;font-size:12px;font-weight:700;margin-left:8px;">
            {{ $employe->matricule }}
        </span>
        @if(!$employe->actif)
            <span style="background:#fee2e2;color:#b91c1c;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:600;margin-left:6px;">
                🚫 Archivé
            </span>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.employes.pdf', $employe->id) }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-pdf"></i> Fiche PDF
        </a>
        <a href="{{ route('rh.employes.edit', $employe->id) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        <a href="{{ route('rh.paie.create') }}?employe_id={{ $employe->id }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle"></i> Bulletin
        </a>
    </div>
</div>

<div class="row g-3">

    {{-- FICHE RÉSUMÉ --}}
    <div class="col-md-4">
        <div class="info-card">
            <h5>👤 Fiche employé</h5>

            {{-- AVATAR AVEC BOUTON PHOTO --}}
            <div class="text-center mb-3">
                <div class="avatar-wrap">
                    @if($employe->photo_path)
                        <img src="{{ asset('storage/' . $employe->photo_path) }}" alt="Photo" id="avatarImg">
                    @else
                        <div class="avatar-initiales" id="avatarImg">
                            {{ strtoupper(substr($employe->prenom,0,1)) }}{{ strtoupper(substr($employe->nom,0,1)) }}
                        </div>
                    @endif

                    <div class="avatar-photo-btn" onclick="document.getElementById('photoInput').click()" title="Changer la photo">
                        <i class="bi bi-camera"></i>
                    </div>
                </div>

                <form id="photoForm" method="POST" action="{{ route('rh.employes.photo', $employe->id) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="photoInput" name="photo" accept="image/*" onchange="document.getElementById('photoForm').submit()">
                </form>

                <div style="margin-top:10px;font-weight:700;font-size:15px;">{{ $employe->nom }} {{ $employe->prenom }}</div>
                <div style="font-size:12px;color:#64748b;">{{ $employe->intitule_poste ?? 'Poste non défini' }}</div>
                <span style="background:{{ $employe->actif?'#dcfce7':'#fee2e2'}};color:{{ $employe->actif?'#15803d':'#b91c1c'}};padding:3px 10px;border-radius:10px;font-size:11px;font-weight:600;display:inline-block;margin-top:4px;">
                    {{ $employe->actif ? '✅ Actif' : '🚫 Archivé' }}
                </span>
            </div>

            <div class="info-row"><span>Matricule</span><span>{{ $employe->matricule }}</span></div>
            <div class="info-row"><span>Sexe</span><span>{{ $employe->sexe === 'M' ? 'Masculin' : 'Féminin' }}</span></div>
            <div class="info-row"><span>Nationalité</span><span>{{ $employe->nationalite ?? '-' }}</span></div>
            <div class="info-row"><span>Email</span><span>{{ $employe->email ?? '-' }}</span></div>
            <div class="info-row"><span>Direction</span><span>{{ $employe->direction?->nom ?? '-' }}</span></div>
            <div class="info-row"><span>Service</span><span>{{ $employe->service?->nom ?? '-' }}</span></div>
            <div class="info-row"><span>Agence / Site</span><span>{{ $employe->agenceSite?->nom ?? '-' }}</span></div>
            <div class="info-row"><span>Niveau / Échelon</span><span>{{ $employe->niveauCheleon?->nom ?? '-' }}</span></div>
            <div class="info-row"><span>Responsable hiérarchique</span><span>{{ $employe->responsableHierarchique?->nom_complet ?? '-' }}</span></div>
            <div class="info-row"><span>Contrat</span><span>{{ $employe->type_contrat }}</span></div>
            <div class="info-row"><span>Catégorie</span><span>{{ $employe->categorie ?? '-' }}</span></div>
            <div class="info-row"><span>Intégration</span><span>{{ $employe->date_integration?->format('d/m/Y') }}</span></div>
            <div class="info-row"><span>Prise de fonction</span><span>{{ $employe->date_prise_fonction?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="info-row"><span>Fin période d'essai</span><span>{{ $employe->date_fin_periode_essai?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="info-row"><span>Ancienneté</span><span>{{ $employe->anciennete }}</span></div>
            <div class="info-row"><span>Vague</span><span>{{ $employe->vague_paiement ?? '-' }}</span></div>
            <div class="info-row"><span>Mode de paiement</span><span>{{ $employe->mode_paiement ?? '-' }}</span></div>
            <div class="info-row" style="border-top:2px solid #e2e8f0;margin-top:6px;padding-top:6px;">
                <span>Salaire base</span>
                <span style="color:#1d4ed8;font-size:14px;">{{ number_format($employe->salaire_base, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        {{-- SECTION CNPS --}}
        <div class="info-card">
            <h5>🏛️ CNPS & Cotisations</h5>
            <div class="info-row"><span>N° CNPS</span><span style="color:#1d4ed8;font-weight:700;">{{ $employe->numero_cnps ?? '-' }}</span></div>
            <div class="info-row"><span>Date d'affiliation</span><span>{{ $employe->date_affiliation_cnps?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="info-row"><span>Centre CNPS</span><span>{{ $employe->centre_cnps ?? '-' }}</span></div>
            <div class="info-row"><span>Situation affiliation</span>
                <span>
                    @if($employe->situation_affiliation_cnps)
                        <span class="badge-cnps {{ $employe->situation_affiliation_cnps }}">
                            {{ ucfirst(str_replace('_', ' ', $employe->situation_affiliation_cnps)) }}
                        </span>
                    @else
                        -
                    @endif
                </span>
            </div>
            <div class="mt-2">
                <a href="{{ route('rh.cnps.affiliations') }}?search={{ $employe->matricule }}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-arrow-right"></i> Voir les déclarations CNPS
                </a>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="row g-2">
            <div class="col-6">
                <div style="background:white;border-radius:10px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #f59e0b;text-align:center;">
                    <div style="font-size:20px;font-weight:800;color:#f59e0b;">{{ $totalAbsences }}</div>
                    <div style="font-size:10px;color:#64748b;font-weight:600;">JOURS ABSENCES</div>
                </div>
            </div>
            <div class="col-6">
                <div style="background:white;border-radius:10px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #dc2626;text-align:center;">
                    <div style="font-size:20px;font-weight:800;color:#dc2626;">{{ $totalRetards }}</div>
                    <div style="font-size:10px;color:#64748b;font-weight:600;">RETARDS</div>
                </div>
            </div>
            <div class="col-6">
                <div style="background:white;border-radius:10px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #16a34a;text-align:center;">
                    <div style="font-size:20px;font-weight:800;color:#16a34a;">{{ $employe->solde_conges_restant }}</div>
                    <div style="font-size:10px;color:#64748b;font-weight:600;">CONGÉS RESTANTS</div>
                </div>
            </div>
            <div class="col-6">
                <div style="background:white;border-radius:10px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #1d4ed8;text-align:center;">
                    <div style="font-size:16px;font-weight:800;color:#1d4ed8;">{{ number_format($dernierBulletin?->net_a_payer ?? 0, 0, ',', ' ') }}</div>
                    <div style="font-size:10px;color:#64748b;font-weight:600;">DERNIER NET</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ONGLETS --}}
    <div class="col-md-8">
        <div class="info-card">
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <button class="tab-btn active" onclick="showTab('t-info', this)"><i class="bi bi-info-circle"></i> Infos</button>
                <button class="tab-btn" onclick="showTab('t-paie', this)"><i class="bi bi-wallet2"></i> Bulletins</button>
                <button class="tab-btn" onclick="showTab('t-abs', this)"><i class="bi bi-calendar-x"></i> Absences</button>
                <button class="tab-btn" onclick="showTab('t-pret', this)"><i class="bi bi-bank"></i> Prêts</button>
                <button class="tab-btn" onclick="showTab('t-sanc', this)"><i class="bi bi-exclamation-triangle"></i> Sanctions</button>
                <button class="tab-btn" onclick="showTab('t-docs', this)"><i class="bi bi-folder"></i> Documents</button>
                <button class="tab-btn" onclick="showTab('t-contrat', this)"><i class="bi bi-file-earmark-text"></i> Contrats</button>
            </div>

            {{-- INFOS --}}
            <div id="t-info" class="tab-panel active">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-row"><span>Date naissance</span><span>{{ $employe->date_naissance?->format('d/m/Y') ?? '-' }}</span></div>
                        <div class="info-row"><span>Lieu naissance</span><span>{{ $employe->lieu_naissance ?? '-' }}</span></div>
                        <div class="info-row"><span>N° CNI</span><span>{{ $employe->numero_cni ?? '-' }}</span></div>
                        <div class="info-row"><span>NIU</span><span>{{ $employe->niu ?? '-' }}</span></div>
                        <div class="info-row"><span>Origines</span><span>{{ $employe->origines ?? '-' }}</span></div>
                        <div class="info-row"><span>Situation matrimoniale</span><span>{{ $employe->situation_matrimoniale }}</span></div>
                        <div class="info-row"><span>Nb enfants</span><span>{{ $employe->nb_enfants }}</span></div>
                        <div class="info-row"><span>État de santé</span><span>{{ $employe->etat_sante }}</span></div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row"><span>Téléphone</span><span>{{ $employe->telephone ?? '-' }}</span></div>
                        <div class="info-row"><span>Email</span><span>{{ $employe->email ?? '-' }}</span></div>
                        <div class="info-row"><span>Adresse</span><span>{{ $employe->adresse ?? '-' }}</span></div>
                        <div class="info-row"><span>Contact urgence</span><span>{{ $employe->personne_a_contacter ?? '-' }}</span></div>
                        <div class="info-row"><span>Tél. urgence</span><span>{{ $employe->tel_urgence ?? '-' }}</span></div>
                        <div class="info-row"><span>Niveau académique</span><span>{{ $employe->niveau_academique ?? '-' }}</span></div>
                        <div class="info-row"><span>Spécialité</span><span>{{ $employe->specialite_academique ?? '-' }}</span></div>
                        <div class="info-row"><span>Diplôme recrutement</span><span>{{ $employe->diplome_recrutement ?? '-' }}</span></div>
                        <div class="info-row"><span>Expérience précédente</span><span>{{ $employe->exp_poste_precedent ?? '-' }}</span></div>
                        <div class="info-row"><span>Entreprise précédente</span><span>{{ $employe->entreprise_precedente ?? '-' }}</span></div>
                        <div class="info-row"><span>Durée expérience</span><span>{{ $employe->duree_exp_precedente ?? '-' }}</span></div>
                    </div>
                </div>
                @if($employe->notes)
                    <div class="mt-3 p-3" style="background:#f8fafc;border-radius:8px;">
                        <strong style="color:#64748b;">📝 Notes :</strong>
                        <div style="margin-top:4px;white-space:pre-wrap;font-size:13px;">{{ $employe->notes }}</div>
                    </div>
                @endif
            </div>

            {{-- BULLETINS --}}
            <div id="t-paie" class="tab-panel">
                <table style="width:100%;font-size:12px;border-collapse:collapse;">
                    <thead><tr style="background:#1e3a5f;color:white;">
                        <th style="padding:8px;">Période</th>
                        <th>Vague</th>
                        <th>Brut</th>
                        <th>CNPS</th>
                        <th>Net</th>
                        <th>Statut</th>
                        <th>PDF</th>
                    </tr></thead>
                    <tbody>
                    @forelse($employe->bulletins as $b)
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:7px;">{{ $b->mois_annee ?? $b->periode }}</td>
                            <td>{{ $b->vague }}</td>
                            <td>{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
                            <td style="color:#dc2626;">{{ number_format($b->cnps ?? 0, 0, ',', ' ') }}</td>
                            <td style="font-weight:700;color:#1d4ed8;">{{ number_format($b->net_a_payer, 0, ',', ' ') }}</td>
                            <td><span class="badge-status {{ $b->statut === 'payé' ? 'paye' : ($b->statut === 'validé' ? 'valide' : 'brouillon') }}">{{ $b->statut }}</span></td>
                            <td><a href="{{ route('rh.paie.pdf', $b->id) }}" class="btn btn-sm btn-danger"><i class="bi bi-file-pdf"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Aucun bulletin</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ABSENCES --}}
            <div id="t-abs" class="tab-panel">
                <table style="width:100%;font-size:12px;border-collapse:collapse;">
                    <thead><tr style="background:#1e3a5f;color:white;">
                        <th style="padding:8px;">Référence</th>
                        <th>Type</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Jours</th>
                        <th>Statut</th>
                    </tr></thead>
                    <tbody>
                    @forelse($employe->absences as $a)
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:7px;">{{ $a->reference ?? '-' }}</td>
                            <td>{{ $a->type_absence }}</td>
                            <td>{{ $a->date_debut?->format('d/m/Y') }}</td>
                            <td>{{ $a->date_fin?->format('d/m/Y') }}</td>
                            <td style="font-weight:700;">{{ $a->nombre_jours }}</td>
                            <td><span class="badge-status {{ $a->statut === 'approuvé' ? 'approuve' : ($a->statut === 'refusé' ? 'refuse' : 'en_attente') }}">{{ $a->statut }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Aucune absence</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PRÊTS --}}
            <div id="t-pret" class="tab-panel">
                <table style="width:100%;font-size:12px;border-collapse:collapse;">
                    <thead><tr style="background:#1e3a5f;color:white;">
                        <th style="padding:8px;">Type</th>
                        <th>Montant</th>
                        <th>Remboursé</th>
                        <th>Restant</th>
                        <th>Statut</th>
                    </tr></thead>
                    <tbody>
                    @forelse($employe->prets as $p)
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:7px;">{{ ucfirst($p->type) }}</td>
                            <td>{{ number_format($p->montant, 0, ',', ' ') }}</td>
                            <td style="color:#16a34a;font-weight:600;">{{ number_format($p->montant_rembourse, 0, ',', ' ') }}</td>
                            <td style="color:#dc2626;font-weight:600;">{{ number_format($p->montant - $p->montant_rembourse, 0, ',', ' ') }}</td>
                            <td><span class="badge-status {{ $p->statut === 'rembourse' ? 'paye' : ($p->statut === 'annule' ? 'refuse' : 'en_attente') }}">{{ $p->statut }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucun prêt</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- SANCTIONS --}}
            <div id="t-sanc" class="tab-panel">
                <table style="width:100%;font-size:12px;border-collapse:collapse;">
                    <thead><tr style="background:#1e3a5f;color:white;">
                        <th style="padding:8px;">Date</th>
                        <th>Type</th>
                        <th>Motif</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr></thead>
                    <tbody>
                    @forelse($employe->sanctions as $s)
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:7px;">{{ $s->date?->format('d/m/Y') }}</td>
                            <td>{{ $s->type }}</td>
                            <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;">{{ $s->motif }}</td>
                            <td style="color:#dc2626;font-weight:600;">{{ number_format($s->montant, 0, ',', ' ') }}</td>
                            <td><span class="badge-status {{ $s->statut === 'valide' ? 'paye' : ($s->statut === 'annule' ? 'refuse' : 'en_attente') }}">{{ $s->statut }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucune sanction</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- DOCUMENTS --}}
            <div id="t-docs" class="tab-panel">
                <form method="POST"
                      action="{{ route('rh.employes.documents.store', $employe->id) }}"
                      enctype="multipart/form-data"
                      style="background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:16px;">
                    @csrf
                    <div style="font-size:12px;font-weight:700;color:#1e3a5f;margin-bottom:10px;">📤 Ajouter un document</div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label style="font-size:11px;font-weight:600;color:#64748b;">Nom</label>
                            <input type="text" name="nom" class="form-control form-control-sm" placeholder="Ex: CV 2024" required>
                        </div>
                        <div class="col-md-2">
                            <label style="font-size:11px;font-weight:600;color:#64748b;">Type</label>
                            <select name="type" class="form-control form-control-sm" required>
                                <option value="cv">CV</option>
                                <option value="cni">CNI</option>
                                <option value="diplome">Diplôme</option>
                                <option value="contrat">Contrat</option>
                                <option value="plan_localisation">Plan localisation</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label style="font-size:11px;font-weight:600;color:#64748b;">Fichier</label>
                            <input type="file" name="fichier" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label style="font-size:11px;font-weight:600;color:#64748b;">Description</label>
                            <input type="text" name="description" class="form-control form-control-sm" placeholder="Optionnel">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-upload"></i></button>
                        </div>
                    </div>
                </form>

                @forelse($employe->documents as $doc)
                    <div class="doc-row">
                        <div>
                            <div style="font-weight:600;font-size:13px;">{{ $doc->nom }}</div>
                            <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                <span style="background:#dbeafe;color:#1d4ed8;padding:1px 6px;border-radius:4px;font-size:10px;margin-right:6px;">
                                    {{ strtoupper(str_replace('_', ' ', $doc->type)) }}
                                </span>
                                {{ $doc->fichier_nom }} — {{ $doc->taille_formattee }}
                                @if($doc->description) — {{ $doc->description }} @endif
                                <span style="color:#94a3b8;margin-left:6px;">{{ $doc->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('rh.employes.documents.download', [$employe->id, $doc->id]) }}"
                               class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                            <form action="{{ route('rh.employes.documents.destroy', $doc->id) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce document ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-muted text-center py-4" style="font-size:13px;">📂 Aucun document enregistré</div>
                @endforelse
            </div>

            {{-- CONTRATS --}}
            <div id="t-contrat" class="tab-panel">
                <table style="width:100%;font-size:12px;border-collapse:collapse;">
                    <thead><tr style="background:#1e3a5f;color:white;">
                        <th style="padding:8px;">N° Contrat</th>
                        <th>Type</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Salaire</th>
                        <th>Statut</th>
                        <th>PDF</th>
                    </tr></thead>
                    <tbody>
                    @forelse($employe->contrats as $c)
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:7px;font-weight:600;color:#1d4ed8;">{{ $c->numero_contrat }}</td>
                            <td>{{ $c->typeContrat?->nom ?? '-' }}</td>
                            <td>{{ $c->date_debut?->format('d/m/Y') }}</td>
                            <td>{{ $c->date_fin?->format('d/m/Y') ?? 'CDI' }}</td>
                            <td>{{ number_format($c->salaire_base, 0, ',', ' ') }}</td>
                            <td><span class="badge-status {{ $c->statut === 'actif' ? 'actif' : ($c->statut === 'termine' ? 'refuse' : 'en_attente') }}">{{ $c->statut_label }}</span></td>
                            <td><a href="{{ route('rh.contrats.pdf', $c->id) }}" class="btn btn-sm btn-danger"><i class="bi bi-file-pdf"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Aucun contrat</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="mt-2">
                    <a href="{{ route('rh.contrats.create') }}?employe_id={{ $employe->id }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouveau contrat
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ACTIONS DANGEREUSES --}}
<div class="mt-3 d-flex gap-2">
    @if($employe->actif)
        <form action="{{ route('rh.employes.destroy', $employe->id) }}" method="POST" onsubmit="return confirm('Archiver cet employé ?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-archive"></i> Archiver</button>
        </form>
    @else
        <form action="{{ route('rh.employes.update', $employe->id) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="actif" value="1">
            <input type="hidden" name="nom" value="{{ $employe->nom }}">
            <input type="hidden" name="prenom" value="{{ $employe->prenom }}">
            <input type="hidden" name="sexe" value="{{ $employe->sexe }}">
            <input type="hidden" name="date_integration" value="{{ $employe->date_integration?->format('Y-m-d') }}">
            <input type="hidden" name="type_contrat" value="{{ $employe->type_contrat }}">
            <input type="hidden" name="salaire_base" value="{{ $employe->salaire_base }}">
            <button class="btn btn-outline-success"><i class="bi bi-arrow-repeat"></i> Réactiver</button>
        </form>
    @endif
</div>

@endsection

@section('scripts')
<script>
function showTab(id, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    btn.classList.add('active');
}

// Auto-open du premier onglet
document.addEventListener('DOMContentLoaded', function() {
    const firstTab = document.querySelector('.tab-btn');
    if (firstTab) {
        firstTab.classList.add('active');
        document.getElementById('t-info').classList.add('active');
    }
});
</script>
@endsection