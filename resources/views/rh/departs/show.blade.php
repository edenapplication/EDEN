@extends('rh.layout')
@section('content')

<style>
.info-card { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.info-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px; }
.info-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
.info-row span:first-child { color:#64748b; }
.info-row span:last-child { font-weight:600; color:#1e3a5f; }
.badge-statut { padding:4px 14px; border-radius:10px; font-size:12px; font-weight:600; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('rh.departs.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
        <span class="ms-3" style="font-size:18px;font-weight:800;color:#1e3a5f;">
            🚪 Départ — {{ $depart->employe?->nom }} {{ $depart->employe?->prenom }}
        </span>
        <span class="badge-statut ms-2" style="background:{{ $depart->statut_color }};color:#1e293b;">
            {{ $depart->statut_label }}
        </span>
    </div>
    <div class="d-flex gap-2">
        @if($depart->statut === 'en_attente')
            <a href="{{ route('rh.departs.edit', $depart->id) }}" class="btn btn-warning btn-sm">✏️ Modifier</a>
            <form action="{{ route('rh.departs.valider', $depart->id) }}" method="POST" style="display:inline;">
                @csrf
                <button class="btn btn-success btn-sm">✅ Valider</button>
            </form>
        @endif
        @if(!$depart->soldeToutCompte && in_array($depart->statut, ['valide', 'en_cours']))
            <form action="{{ route('rh.departs.generer-solde', $depart->id) }}" method="POST" style="display:inline;">
                @csrf
                <button class="btn btn-outline-success btn-sm">💰 Générer solde</button>
            </form>
        @endif
        @if(!$depart->certificatCessation && $depart->statut === 'termine')
            <form action="{{ route('rh.departs.generer-certificat', $depart->id) }}" method="POST" style="display:inline;">
                @csrf
                <button class="btn btn-outline-info btn-sm">📄 Générer certificat</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    {{-- INFOS DÉPART --}}
    <div class="col-md-6">
        <div class="info-card">
            <h5>📋 Informations du départ</h5>
            <div class="info-row"><span>Date de départ</span><span>{{ $depart->date_depart?->format('d/m/Y') }}</span></div>
            <div class="info-row"><span>Date de notification</span><span>{{ $depart->date_notification?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="info-row"><span>Date de préavis</span><span>{{ $depart->date_preavis?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="info-row"><span>Motif</span><span>{{ $depart->motifDepart?->nom ?? $depart->motif_libre ?? '-' }}</span></div>
            <div class="info-row"><span>Statut</span><span>{{ $depart->statut_label }}</span></div>
            <div class="info-row"><span>Validé par</span><span>{{ $depart->validePar?->name ?? '-' }}</span></div>
            <div class="info-row"><span>Date validation</span><span>{{ $depart->date_validation?->format('d/m/Y') ?? '-' }}</span></div>
            @if($depart->observations)
                <div class="mt-2 p-2" style="background:#f8fafc;border-radius:6px;font-size:12px;">
                    <strong style="color:#64748b;">Observations :</strong>
                    <div style="margin-top:4px;white-space:pre-wrap;">{{ $depart->observations }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- SOLDE DE TOUT COMPTE --}}
    <div class="col-md-6">
        <div class="info-card">
            <h5>💰 Solde de tout compte</h5>
            @if($depart->soldeToutCompte)
                @php $s = $depart->soldeToutCompte; @endphp
                <div class="info-row"><span>Salaire de base</span><span>{{ number_format($s->salaire_base, 0, ',', ' ') }} FCFA</span></div>
                <div class="info-row"><span>Indemnité congés</span><span>{{ number_format($s->indemnite_conges, 0, ',', ' ') }} FCFA</span></div>
                <div class="info-row"><span>Indemnité préavis</span><span>{{ number_format($s->indemnite_preavis, 0, ',', ' ') }} FCFA</span></div>
                <div class="info-row"><span>Prime ancienneté</span><span>{{ number_format($s->prime_anciennete, 0, ',', ' ') }} FCFA</span></div>
                <div class="info-row" style="border-top:2px solid #e2e8f0;padding-top:8px;font-weight:700;">
                    <span>Total brut</span>
                    <span style="color:#1d4ed8;">{{ number_format($s->total_brut, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="info-row"><span>CNPS</span><span style="color:#dc2626;">- {{ number_format($s->cnps, 0, ',', ' ') }} FCFA</span></div>
                <div class="info-row"><span>Impôts</span><span style="color:#dc2626;">- {{ number_format($s->impots, 0, ',', ' ') }} FCFA</span></div>
                <div class="info-row" style="border-top:2px solid #1e3a5f;padding-top:8px;">
                    <span style="font-size:15px;font-weight:700;">Net à payer</span>
                    <span style="font-size:18px;font-weight:800;color:#16a34a;">{{ number_format($s->net_a_payer, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="info-row"><span>Statut paiement</span><span>{{ $s->statut_label }}</span></div>
                @if($s->date_paiement)
                    <div class="info-row"><span>Date paiement</span><span>{{ $s->date_paiement?->format('d/m/Y') }}</span></div>
                @endif
                <div class="mt-2 d-flex gap-2">
                    <a href="{{ route('rh.departs.pdf-solde', $s->id) }}" class="btn btn-danger btn-sm">📄 PDF</a>
                </div>
            @else
                <div class="text-center text-muted py-3">
                    <p>Aucun solde de tout compte généré.</p>
                    @if(in_array($depart->statut, ['valide', 'en_cours']))
                        <form action="{{ route('rh.departs.generer-solde', $depart->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-primary btn-sm">💰 Générer le solde</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{{-- CERTIFICAT DE CESSATION --}}
<div class="row g-3 mt-2">
    <div class="col-md-6">
        <div class="info-card">
            <h5>📄 Certificat de cessation</h5>
            @if($depart->certificatCessation)
                @php $c = $depart->certificatCessation; @endphp
                <div class="info-row"><span>Référence</span><span style="color:#1d4ed8;font-weight:700;">{{ $c->reference }}</span></div>
                <div class="info-row"><span>Date d'émission</span><span>{{ $c->date_emission?->format('d/m/Y') }}</span></div>
                <div class="info-row"><span>Date d'effet</span><span>{{ $c->date_effet?->format('d/m/Y') }}</span></div>
                <div class="info-row"><span>Motif</span><span>{{ $c->motif ?? '-' }}</span></div>
                <div class="info-row"><span>Statut</span><span>{{ $c->statut_label }}</span></div>
                <div class="mt-2 d-flex gap-2">
                    <a href="{{ route('rh.departs.pdf-certificat', $c->id) }}" class="btn btn-danger btn-sm">📄 PDF</a>
                </div>
            @else
                <div class="text-center text-muted py-3">
                    <p>Aucun certificat de cessation généré.</p>
                    @if($depart->statut === 'termine')
                        <form action="{{ route('rh.departs.generer-certificat', $depart->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-primary btn-sm">📄 Générer le certificat</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- HISTORIQUE --}}
    @if($historique->count() > 0)
    <div class="col-md-6">
        <div class="info-card">
            <h5>📜 Historique des départs</h5>
            <table class="table table-sm" style="font-size:12px;">
                <thead>
                    <tr>
                        <th>Date départ</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($historique as $h)
                    <tr>
                        <td>{{ $h->date_depart?->format('d/m/Y') }}</td>
                        <td>{{ $h->motifDepart?->nom ?? $h->motif_libre ?? '-' }}</td>
                        <td><span class="badge" style="background:{{ $h->statut_color }};">{{ $h->statut_label }}</span></td>
                        <td><a href="{{ route('rh.departs.show', $h->id) }}" class="btn btn-sm btn-outline-primary">👁</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- ACTIONS DANGEREUSES --}}
<div class="mt-3">
    @if(in_array($depart->statut, ['en_attente', 'valide']))
        <form action="{{ route('rh.departs.annuler', $depart->id) }}" method="POST" style="display:inline;">
            @csrf
            <button class="btn btn-outline-secondary" onclick="return confirm('Annuler ce départ ?')">🚫 Annuler le départ</button>
        </form>
    @endif
    @if(!in_array($depart->statut, ['termine']))
        <form action="{{ route('rh.departs.destroy', $depart->id) }}" method="POST" style="display:inline;" 
              onsubmit="return confirm('Supprimer ce départ ?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger">🗑 Supprimer</button>
        </form>
    @endif
</div>

@endsection