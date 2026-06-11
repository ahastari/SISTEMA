@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between mb-4">
    <h2>Agenda de Clientes</h2>
    <a href="{{ route('clientes.create') }}" class="btn btn-success">
        + Nuevo Cliente
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" action="{{ route('clientes.index') }}">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Buscar cliente..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                        @if(request('search'))
                            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Limpiar</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>RFC</th>
                    <th>Ciudad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->id }}</td>
                    <td>{{ $cliente->nombre_completo }}</td>
                    <td>{{ $cliente->telefono }}</td>
                    <td>{{ $cliente->rfc ?? 'N/A' }}</td>
                    <td>{{ $cliente->ciudad ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-info btn-sm">
                            Ver
                        </a>
                        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-warning btn-sm">
                            Editar
                        </a>
                        <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este cliente?')">
                                Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No hay clientes registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $clientes->links() }}
    </div>
</div>
@endsection