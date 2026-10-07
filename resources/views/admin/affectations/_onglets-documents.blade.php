{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- 🗂️ BARRE D'ONGLETS — Navigation contextuelle                --}}
{{-- Variable : $ongletActif (string)                             --}}
{{-- ═══════════════════════════════════════════════════════════ --}}

@php
    $ongletActif = $ongletActif ?? '';

    // ✅ Boutons visibles selon la page active
    $boutonsVisibles = match($ongletActif) {
        'initiales', 'avant-date' => ['initiales', 'avant-date', 'actives', 'avec-date'],
        'actives', 'avec-date'    => ['initiales', 'avant-date', 'actives', 'avec-date', 'finales'],
        'finales'                 => ['finales'],
        'tous'                    => ['initiales', 'avant-date', 'actives', 'avec-date', 'finales', 'tous'],
        default                   => ['initiales', 'avant-date', 'actives', 'avec-date', 'finales', 'tous'],
    };

    // Définition de chaque bouton
    $tousLesBoutons = [
        'initiales' => [
            'route' => 'affectations.documents.initiales',
            'icone' => '📅',
            'label' => 'Rapport d\'implantation',
            'class' => 'o-initiales',
        ],
        'avant-date' => [
            'route' => 'affectations.documents.avant-date',
            'icone' => '📄',
            'label' => 'Rapport des attributions',
            'class' => 'o-avant-date',
        ],
        'actives' => [
            'route' => 'affectations.documents.actives',
            'icone' => '🎯',
            'label' => 'Rapport des appréciations',
            'class' => 'o-actives',
        ],
        'avec-date' => [
            'route' => 'affectations.documents.avec-date',
            'icone' => '📅',
            'label' => 'Rapport de planification des implantations',
            'class' => 'o-avec-date',
        ],
        'finales' => [
            'route' => 'affectations.documents.finales',
            'icone' => '🔒',
            'label' => 'Rapport de clôture de planification des implantations',
            'class' => 'o-finales',
        ],
        'tous' => [
            'route' => 'affectations.documents.tous',
            'icone' => '🗂️',
            'label' => 'Tous les documents',
            'class' => 'o-tous',
        ],
    ];
@endphp

<style>
.onglets-docs-bar {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    padding: 10px;
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.btn-onglet-doc {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 800;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid #e2e8f0;
    background: white;
    color: #64748b;
    white-space: nowrap;
    position: relative;
    overflow: hidden;
}
.btn-onglet-doc::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
    transition: left 0.5s;
}
.btn-onglet-doc:hover::before { left: 100%; }
.btn-onglet-doc:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.08);
    color: #1e3a5f;
    background: #f8fafc;
}
.btn-onglet-doc.active {
    color: white;
    border-color: transparent;
    box-shadow: 0 6px 18px rgba(0,0,0,0.18);
    transform: translateY(-2px);
}
.btn-onglet-doc.o-initiales.active  { background: linear-gradient(135deg, #5b21b6, #7c3aed); }
.btn-onglet-doc.o-avant-date.active { background: linear-gradient(135deg, #7c3aed, #a855f7); }
.btn-onglet-doc.o-actives.active    { background: linear-gradient(135deg, #d97706, #f59e0b); }
.btn-onglet-doc.o-avec-date.active  { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
.btn-onglet-doc.o-finales.active    { background: linear-gradient(135deg, #15803d, #16a34a); }
.btn-onglet-doc.o-tous.active       { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
</style>

<div class="onglets-docs-bar">
    @foreach($boutonsVisibles as $key)
        @if(isset($tousLesBoutons[$key]))
            @php $b = $tousLesBoutons[$key]; @endphp
            <a href="{{ route($b['route']) }}"
               class="btn-onglet-doc {{ $b['class'] }} {{ $ongletActif === $key ? 'active' : '' }}">
                {{ $b['icone'] }} {{ $b['label'] }}
            </a>
        @endif
    @endforeach
</div>