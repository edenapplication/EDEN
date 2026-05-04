@extends('admin.layout')
@section('content')
@include('admin.suivi_client._form', [
    'title'   => '➕ Nouveau Client & Dossier',
    'action'  => route('suivi-client.store'),
    'method'  => 'POST',
    'client'  => null,
    'dossier' => null,
])
@endsection