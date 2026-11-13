@extends('layouts.app')

@section('title', 'Documentos')

@section('content')
<div class="workspace-view view-index">
<div class="row page-header" style="justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h1>Documentos</h1>
        <p class="muted">Gestión documental y archivado privado.</p>
    </div>
    @can('create', App\Models\Documento::class)
        <a href="{{ route('documentos.create') }}" class="action-button action-primary">Nuevo documento</a>
    @endcan
</div>

<form method="GET" class="panel filter-panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:2; min-width:200px;">
            <div class="filter-field"><label for="search">Buscar</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Nombre, número o descripción"></div>
        </div>
        <div style="flex:1; min-width:170px;">
            <div class="filter-field"><label for="tipo_documento_id">Tipo</label>
            <select id="tipo_documento_id" name="tipo_documento_id">
                <option value="">Todos</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->id }}" {{ request('tipo_documento_id') == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                @endforeach
            </select></div>
        </div>
        <div>
            <button type="submit" class="action-button action-primary">Filtrar</button>
            <a href="{{ url()->current() }}" class="action-button action-neutral">Limpiar</a>
        </div>
    </div>
</form>

<div class="panel" style="margin-top:20px;">
    <div class="table-panel"><table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Número</th>
                <th>Tipo</th>
                <th>Archivo</th>
                <th class="actions-column">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documentos as $documento)
                <tr>
                    <td>{{ $documento->nombre }}</td>
                    <td>{{ $documento->numero }}</td>
                    <td>{{ $documento->tipo?->nombre ?? 'Sin tipo' }}</td>
                    <td>{{ $documento->archivo_original ?? 'Sin archivo' }}</td>
                    <td class="row">
                        <a href="{{ route('documentos.show', $documento) }}" class="action-button action-view">Ver</a>
                        @can('download', $documento)
                            <a href="{{ route('documentos.download', $documento) }}" class="action-button action-download">Descargar</a>
                        @endcan
                        @can('update', $documento)
                            <a href="{{ route('documentos.edit', $documento) }}" class="action-button action-edit">Editar</a>
                        @endcan
                        @can('delete', $documento)
                            <form method="POST" action="{{ route('documentos.destroy', $documento) }}" onsubmit="return confirm('¿Desea eliminar el documento?');" class="record-action-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-button action-delete">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No hay documentos registrados.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    @include('partials.pagination', ['paginator' => $documentos])
</div>
</div>
@endsection
