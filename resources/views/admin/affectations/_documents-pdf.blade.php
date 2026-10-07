{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- 📄 PDFs ENREGISTRÉS POUR CETTE SEMAINE                      --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if(isset($documentsPdf) && $documentsPdf->count() > 0)
<div style="margin-top:24px;padding:20px;background:white;border-radius:12px;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);border-left:4px solid #16a34a;">
    <div style="display:flex;justify-content:space-between;align-items:center;
                margin-bottom:16px;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-weight:800;color:#1e3a5f;font-size:15px;">
                📄 Documents PDF enregistrés
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">
                {{ $documentsPdf->count() }} document(s) pour cette semaine
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px;">
        @foreach($documentsPdf as $doc)
        <div style="background:#f8fafc;border-radius:10px;padding:14px;
                    border-left:3px solid #16a34a;transition:all 0.2s;"
             onmouseover="this.style.background='#f1f5f9';this.style.transform='translateX(4px)'"
             onmouseout="this.style.background='#f8fafc';this.style.transform='translateX(0)'">
            <div style="font-weight:700;color:#1e3a5f;font-size:13px;
                        word-break:break-word;margin-bottom:8px;">
                📄 {{ $doc->nom_fichier }}
            </div>
            <div style="font-size:11px;color:#64748b;display:flex;
                        flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                <span>📅 {{ $doc->created_at->format('d/m/Y H:i') }}</span>
                @if($doc->nb_lignes)
                    <span>📋 {{ $doc->nb_lignes }} ligne(s)</span>
                @endif
                @if($doc->nb_lots)
                    <span>📦 {{ $doc->nb_lots }} lot(s)</span>
                @endif
            </div>
            @if($doc->user)
            <div style="font-size:10px;color:#94a3b8;margin-bottom:10px;">
                👤 Par {{ $doc->user->name }}
            </div>
            @endif
            <div style="display:flex;gap:8px;">
                <a href="{{ $doc->url }}" target="_blank"
                   style="background:linear-gradient(135deg,#1d4ed8,#1e40af);color:white;
                          border-radius:8px;padding:6px 14px;
                          font-size:11px;font-weight:700;text-decoration:none;
                          display:inline-flex;align-items:center;gap:4px;">
                    👁 Voir
                </a>
                <a href="{{ $doc->url }}" download
                   style="background:white;color:#1e3a5f;
                          border:2px solid #e2e8f0;border-radius:8px;padding:4px 12px;
                          font-size:11px;font-weight:700;text-decoration:none;
                          display:inline-flex;align-items:center;gap:4px;">
                    ⬇ Télécharger
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif