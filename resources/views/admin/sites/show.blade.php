@extends('admin.layout')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<style>
#tfModal input { border-radius:10px; padding:10px; }
#tfModal label { margin-bottom:5px; display:block; color:#333; }
#tfModal h4 { font-weight:700; }
#modalOverlay {
    display:none; position:fixed; top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.4); z-index:9998;
}
#tfModal { animation: fadeInScale 0.2s ease; }
@keyframes fadeInScale {
    from { transform:translate(-50%,-60%) scale(0.9); opacity:0; }
    to   { transform:translate(-50%,-50%) scale(1);   opacity:1; }
}

/* ✅ Bouton TF dans la zone SVG — 70% de la zone, gros et cliquable */
.tf-zone-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 70%;
    margin: 0 auto;
    background: linear-gradient(135deg, #1e3a5f, #2d6cdf);
    color: white;
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 800;
    text-align: center;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(30,58,95,0.3);
    transition: opacity 0.2s;
    border: none;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 70%;
}
.tf-zone-btn:hover { opacity: 0.85; color: white; }
</style>

<div class="d-flex align-items-center gap-3 mb-3">
    <a href="{{ route('sites.index', $site->grand_site_id) }}"
       class="btn btn-outline-secondary btn-sm">← Sites</a>
    <h2 class="mb-0">🗺️ {{ $site->name }}</h2>
</div>

<input type="hidden" id="site_id" value="{{ $site->id }}">

<div class="card p-3">
    @if($site->svg_path && file_exists(storage_path('app/public/maps/' . basename($site->svg_path))))
        <div id="map-container" style="border:1px solid #ddd; overflow:auto; position:relative;">
            {!! file_get_contents(storage_path('app/public/maps/' . basename($site->svg_path))) !!}
        </div>
    @else
        <p class="text-muted">Aucun SVG disponible</p>
    @endif
</div>

<div id="modalOverlay" onclick="closeTfModal()"></div>

{{-- MODAL CRÉER/MODIFIER TF --}}
<div id="tfModal" style="
    display:none; position:fixed; top:50%; left:50%;
    transform:translate(-50%,-50%); background:white;
    padding:25px; border-radius:16px;
    box-shadow:0 20px 50px rgba(0,0,0,0.2);
    z-index:9999; width:400px; max-width:90%;
">
    <h4 id="modalTitle">Créer un TF</h4>
    <div class="mb-3">
        <label>Nom du TF</label>
        <input type="text" id="tfTitle" class="form-control" placeholder="Ex: TF 102">
    </div>
    <div class="mb-3">
        <label>Fichier (optionnel)</label>
        <input type="file" id="tfFile" class="form-control">
    </div>
    <div class="d-flex justify-content-end gap-2 mt-3">
        <button onclick="closeTfModal()" class="btn btn-light">Annuler</button>
        <button onclick="saveTf()" class="btn btn-primary" id="saveBtn">Créer</button>
    </div>
</div>

{{-- MODAL ACTION TF --}}
<div id="tfActionModal" style="
    display:none; position:fixed; top:30%; left:50%;
    transform:translate(-50%,-50%); background:white;
    padding:20px; border-radius:10px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
    z-index:9999; width:300px; text-align:center;
">
    <h4>Que veux-tu faire ?</h4>
    <button class="btn btn-primary w-100 mt-2" onclick="goToTf()">👁 Voir le TF</button>
    <button class="btn btn-warning w-100 mt-2" onclick="editTf()">✏️ Modifier</button>
    <button class="btn btn-light w-100 mt-2" onclick="closeTfActionModal()">Annuler</button>
</div>

{{-- LÉGENDE --}}
<div style="position:fixed; bottom:20px; right:20px; background:white;
            padding:10px; border:1px solid #ccc; max-width:200px; border-radius:8px;">
    <strong>Légende TF</strong>
    <div id="legend-items"></div>
</div>

@endsection

@section('scripts')
<script>
let selectedTf  = null;
let isEditMode  = false;
let currentZone = null;

function getRandomColor() {
    const colors = ["#FF6B6B","#4D96FF","#6BCB77","#FFD93D","#845EC2","#FF9671","#00C9A7","#C34A36","#3D5A80","#98C1D9"];
    return colors[Math.floor(Math.random() * colors.length)];
}

document.addEventListener("DOMContentLoaded", function () {
    const siteId = document.getElementById("site_id").value;
    const tfs    = @json(\App\Models\Tf::where('site_id', $site->id)->get());
    const svg    = document.querySelector("#map-container svg");
    if (!svg) return;

    svg.querySelectorAll("text").forEach(t => t.remove());

    let usedColors = {};

    svg.querySelectorAll("path").forEach(el => {
        const tf = tfs.find(t => t.svg_zone_id === el.id);

        el.style.cursor      = "pointer";
        el.style.transition  = "0.2s";
        el.style.fill        = "transparent";
        el.style.strokeWidth = "2px";

        el.addEventListener("mouseenter", function () {
            if (!tf) this.style.fill = "rgba(0,150,255,0.25)";
        });
        el.addEventListener("mouseleave", function () {
            if (!tf) this.style.fill = "transparent";
        });

        if (tf) {
            const color = tf.color || getRandomColor();
            usedColors[tf.title] = color;
            el.style.stroke = color;
            el.style.fill   = color + "33";

            const bbox = el.getBBox();
            const cx   = bbox.x + bbox.width  / 2;
            const cy   = bbox.y + bbox.height / 2;

            // ✅ Bouton SVG étranger — foreignObject pour intégrer un vrai bouton HTML dans le SVG
            const fo = document.createElementNS("http://www.w3.org/2000/svg", "foreignObject");
            const btnW = bbox.width * 0.70;  // 70% de la largeur de la zone
            const btnH = Math.min(bbox.height * 0.55, 38);

            fo.setAttribute("x",      cx - btnW / 2);
            fo.setAttribute("y",      cy - btnH / 2);
            fo.setAttribute("width",  btnW);
            fo.setAttribute("height", btnH);
            fo.style.pointerEvents = "none"; // le clic passe au path en dessous

            const btn = document.createElement("a");
            btn.href      = `/admin/tf/${tf.id}`;
            btn.className = "tf-zone-btn";
            btn.style.width    = "100%";
            btn.style.maxWidth = "100%";
            btn.style.fontSize = Math.max(9, Math.min(13, btnW / 8)) + "px";
            btn.title     = tf.title;
            btn.innerText = tf.title;

            // ✅ Le bouton doit capter ses propres clics
            btn.style.pointerEvents = "auto";

            fo.appendChild(btn);
            svg.appendChild(fo);

            // Clic sur le path → action modal (pas le bouton)
            el.addEventListener("click", function (e) {
                // Si clic direct sur le path (pas sur le foreignObject)
                selectedTf = tf;
                openTfActionModal();
            });

        } else {
            el.style.stroke          = "#999";
            el.style.strokeDasharray = "2,2";
            el.addEventListener("click", function () { openTfModal(this.id, siteId); });
        }
    });

    Object.entries(usedColors).forEach(([name, color]) => {
        const div = document.createElement("div");
        div.style.cssText = "display:flex;align-items:center;margin-bottom:5px;";
        div.innerHTML = `<div style="width:12px;height:12px;background:${color};margin-right:5px;border-radius:2px;"></div><small>${name}</small>`;
        document.getElementById("legend-items").appendChild(div);
    });
});

function openTfModal(zoneId, siteId) {
    isEditMode  = false;
    currentZone = { zoneId, siteId };
    document.getElementById("tfTitle").value        = "";
    document.getElementById("tfFile").value         = "";
    document.getElementById("modalTitle").innerText = "Créer un TF";
    document.getElementById("saveBtn").innerText    = "Créer";
    document.getElementById("modalOverlay").style.display = "block";
    document.getElementById("tfModal").style.display      = "block";
}

function closeTfModal() {
    document.getElementById("tfModal").style.display      = "none";
    document.getElementById("modalOverlay").style.display = "none";
    currentZone = null;
}

function saveTf() {
    const title = document.getElementById("tfTitle").value;
    const file  = document.getElementById("tfFile").files[0];
    if (!title || !currentZone) return;

    const formData = new FormData();
    formData.append("title",       title);
    formData.append("svg_zone_id", currentZone.zoneId);
    formData.append("site_id",     currentZone.siteId);
    if (file) formData.append("file", file);

    const url = isEditMode ? `/admin/tf/update/${selectedTf.id}` : `/admin/tf/store`;
    if (isEditMode) formData.append("_method", "PUT");

    fetch(url, {
        method: "POST",
        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: formData
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert("Erreur"); });
}

function openTfActionModal()  { document.getElementById("tfActionModal").style.display = "block"; }
function closeTfActionModal() { document.getElementById("tfActionModal").style.display = "none"; }
function goToTf()  { window.location.href = "/admin/tf/" + selectedTf.id; }

function editTf() {
    isEditMode  = true;
    currentZone = { zoneId: selectedTf.svg_zone_id, siteId: selectedTf.site_id };
    document.getElementById("tfTitle").value        = selectedTf.title;
    document.getElementById("modalTitle").innerText = "Modifier le TF";
    document.getElementById("saveBtn").innerText    = "Mettre à jour";
    document.getElementById("modalOverlay").style.display = "block";
    document.getElementById("tfModal").style.display      = "block";
    closeTfActionModal();
}
</script>
@endsection