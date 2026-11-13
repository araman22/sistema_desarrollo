@extends('layouts.app')

@section('title', 'Biblioratos')

@section('content')
<div class="workspace-view view-index">
<div class="row page-header" style="justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h1>Biblioratos y carpetas</h1>
        <p class="muted">Categorías en las que se organizan los expedientes. Las que ya no se usan se desactivan para conservar lo archivado.</p>
    </div>
    <a href="{{ route('expedientes.index') }}" class="action-button action-neutral">Volver a expedientes</a>
</div>

<div class="panel">
    <div class="table-panel"><table>
        <thead><tr><th>Nombre</th><th>Descripción</th><th>Expedientes</th><th>Estado</th>@can('categorias', App\Models\Expediente::class)<th>Editar</th>@endcan</tr></thead>
        <tbody>
            @forelse($biblioratos as $bibliorato)
                <tr>
                    @can('categorias', App\Models\Expediente::class)
                        <td colspan="2">
                            <form method="POST" action="{{ route('biblioratos.update', $bibliorato) }}" class="row record-form" style="gap:8px;" id="bibliorato-{{ $bibliorato->id }}">
                                @csrf
                                @method('PUT')
                                <input name="nombre" value="{{ $bibliorato->nombre }}" maxlength="100" required aria-label="Nombre de {{ $bibliorato->nombre }}">
                                <input name="descripcion" value="{{ $bibliorato->descripcion }}" maxlength="255" aria-label="Descripción de {{ $bibliorato->nombre }}">
                            </form>
                        </td>
                    @else
                        <td>{{ $bibliorato->nombre }}</td>
                        <td>{{ $bibliorato->descripcion ?? '—' }}</td>
                    @endcan
                    <td><a href="{{ route('expedientes.index', ['bibliorato_id' => $bibliorato->id]) }}">{{ $bibliorato->expedientes_count }}</a></td>
                    <td>
                        @can('categorias', App\Models\Expediente::class)
                            <label style="font-weight:normal;"><input type="checkbox" name="activo" value="1" form="bibliorato-{{ $bibliorato->id }}" {{ $bibliorato->activo ? 'checked' : '' }}> Activo</label>
                        @else
                            {{ $bibliorato->activo ? 'Activo' : 'Inactivo' }}
                        @endcan
                    </td>
                    @can('categorias', App\Models\Expediente::class)
                        <td><button type="submit" form="bibliorato-{{ $bibliorato->id }}" class="action-button action-primary">Guardar</button></td>
                    @endcan
                </tr>
            @empty
                <tr><td colspan="5">No hay biblioratos.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    @can('categorias', App\Models\Expediente::class)
        <form method="POST" action="{{ route('biblioratos.store') }}" style="margin-top:16px;" class="record-form">
            @csrf
            <div class="row" style="gap:12px; align-items:end;">
                <div class="field" style="flex:1; min-width:200px;"><label for="nombre">Nueva categoría</label><input id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="100" required></div>
                <div class="field" style="flex:2; min-width:200px;"><label for="descripcion">Descripción</label><input id="descripcion" name="descripcion" value="{{ old('descripcion') }}" maxlength="255"></div>
                <div class="field"><button type="submit" class="action-button action-primary">Agregar</button></div>
            </div>
        </form>
    @endcan
</div>
</div>
@endsection
