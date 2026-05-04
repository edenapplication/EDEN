@extends('admin.layout')

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<h2>👤 Gestion des Commerciaux</h2>

{{-- ===================== --}}
{{-- FORMULAIRE --}}
{{-- ===================== --}}
<div class="card-box mt-3">

    <h5>➕ Ajouter un commercial</h5>

    <form method="POST" action="{{ route('commerciaux.store') }}">
        @csrf

        <div class="row">

            <div class="col-md-3">
                <input type="text" name="name" class="form-control" placeholder="Nom commercial" required>
            </div>

            <div class="col-md-3">
                <input type="text" name="phone" class="form-control" placeholder="Téléphone">
            </div>

            <div class="col-md-3">
                <input type="text" name="agence" class="form-control" placeholder="Agence">
            </div>

            <div class="col-md-3">
                <button class="btn btn-success w-100">Créer</button>
            </div>

        </div>

    </form>

</div>

{{-- ===================== --}}
{{-- LISTE --}}
{{-- ===================== --}}
<div class="card-box mt-4">

    <h5>📋 Liste des commerciaux</h5>

    <table class="table table-hover mt-3">

        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Agence</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

        @foreach($commerciaux as $c)

            <tr>
                <td>{{ $c->id }}</td>
                <td>{{ $c->name }}</td>
                <td>{{ $c->phone ?? '-' }}</td>
                <td>{{ $c->agence ?? '-' }}</td>

                <td class="d-flex gap-2">

                    {{-- ✏️ MODIFIER --}}
                    <button class="btn btn-sm btn-primary"
                        onclick="openEditModal({{ $c->id }}, '{{ $c->name }}', '{{ $c->phone }}', '{{ $c->agence }}')">
                        Modifier
                    </button>

                    {{-- ❌ SUPPRIMER --}}
                    <form method="POST"
                          action="{{ route('commerciaux.destroy', $c->id) }}"
                          onsubmit="return confirm('Supprimer ce commercial ?');">
                        @csrf
                        @method('DELETE')

                        <button class="btn btn-sm btn-danger">
                            Supprimer
                        </button>
                    </form>

                </td>
            </tr>

        @endforeach

        </tbody>

    </table>

</div>

{{-- ===================== --}}
{{-- MODAL EDIT --}}
{{-- ===================== --}}
<div id="editModal" style="
display:none;
position:fixed;
top:50%;
left:50%;
transform:translate(-50%, -50%);
background:white;
padding:20px;
border-radius:12px;
box-shadow:0 10px 30px rgba(0,0,0,0.3);
z-index:9999;
width:400px;
">

<h4>✏️ Modifier commercial</h4>

<form id="editForm" method="POST">
    @csrf
    @method('PUT')

    <input type="text" id="edit_name" name="name" class="form-control mb-2" required>
    <input type="text" id="edit_phone" name="phone" class="form-control mb-2">
    <input type="text" id="edit_agence" name="agence" class="form-control mb-2">

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="button" onclick="closeModal()" class="btn btn-light">Annuler</button>
        <button class="btn btn-success">Mettre à jour</button>
    </div>
</form>

</div>

@endsection

@section('scripts')
<script>

function openEditModal(id, name, phone, agence) {
    document.getElementById('editModal').style.display = 'block';

    document.getElementById('edit_name').value = name || '';
    document.getElementById('edit_phone').value = phone || '';
    document.getElementById('edit_agence').value = agence || '';

    document.getElementById('editForm').action = '/admin/commerciaux/' + id;
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

</script>
@endsection