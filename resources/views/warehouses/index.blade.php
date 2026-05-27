@extends('layouts.app')

@section('content')
<h3>Daftar Gudang</h3>

<a href="{{ route('warehouses.create') }}" class="btn btn-primary mb-3">Tambah Gudang</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Kode</th>
        </tr>
    </thead>
    <tbody>
        @foreach($warehouses as $warehouse)
        <tr>
            <td>{{ $warehouse->name }}</td>
            <td>{{ $warehouse->code }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection