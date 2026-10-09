@extends('layouts.app')

@section('title', 'Tipos de documento')

@section('content')
<div class="row" style="justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h1>Tipos de documento</h1>
        <p class="muted">Catálogo de categorías documentales.</p>
    </div>
    @can('create', App\Models\TipoDocumento::class)
        <a href="{{ route('tipo-documentos.create') }}" class="btn">Nuevo tipo</a>
    @endcan
</div>

<form method="GET" class="panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:1; min-width:220px;">
            <label for="search">Buscar</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Nombre del tipo">
        </div>
        <div>
            <button type="submit" class="btn">Filtrar</button>
        </div>
    </div>
</form>

<div class="panel" style="margin-top:20px;">
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Documentos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tipos as $tipo)
                <tr>
                    <td>{{ $tipo->nombre }}</td>
                    <td>{{ $tipo->documentos()->count() }}</td>
                    <td class="row">
                        <a href="{{ route('tipo-documentos.show', $tipo) }}" class="btn secondary">Ver</a>
                        @can('update', $tipo)
                            <a href="{{ route('tipo-documentos.edit', $tipo) }}" class="btn secondary">Editar</a>
                        @endcan
                        @can('delete', $tipo)
                            <form method="POST" action="{{ route('tipo-documentos.destroy', $tipo) }}" onsubmit="return confirm('¿Desea eliminar el tipo de documento?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn danger">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">No hay tipos de documento registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('partials.pagination', ['paginator' => $tipos])
</div>
@endsection
