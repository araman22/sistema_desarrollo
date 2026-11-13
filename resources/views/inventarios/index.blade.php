@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
<div class="workspace-view view-index">
<div class="row page-header" style="justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h1>Inventario</h1>
        <p class="muted">Recursos y bienes asignados a oficinas y responsables.</p>
    </div>
    @can('create', App\Models\Inventario::class)
        <a href="{{ route('inventarios.create') }}" class="action-button action-primary">Nuevo recurso</a>
    @endcan
</div>

<form method="GET" class="panel filter-panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:2; min-width:200px;">
            <div class="filter-field"><label for="search">Buscar</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Código, descripción o serie"></div>
        </div>
        <div style="flex:1; min-width:170px;">
            <div class="filter-field"><label for="oficina_id">Oficina</label>
            <select id="oficina_id" name="oficina_id">
                <option value="">Todas</option>
                @foreach($oficinas as $oficina)
                    <option value="{{ $oficina->id }}" {{ (string) request('oficina_id') === (string) $oficina->id ? 'selected' : '' }}>{{ $oficina->nombre }}</option>
                @endforeach
            </select></div>
        </div>
        <div style="flex:1; min-width:170px;">
            <div class="filter-field"><label for="categoria">Categoría</label>
            <select id="categoria" name="categoria">
                <option value="">Todas</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria }}" {{ request('categoria') === $categoria ? 'selected' : '' }}>{{ $categoria }}</option>
                @endforeach
            </select></div>
        </div>
        <div style="flex:1; min-width:170px;">
            <div class="filter-field"><label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                @foreach($estados as $estado)
                    <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$estado)) }}</option>
                @endforeach
            </select></div>
        </div>
        <div>
            <button type="submit" class="action-button action-primary">Filtrar</button>
            <a href="{{ url()->current() }}" class="action-button action-neutral">Limpiar</a>
        </div>
    </div>
</form>

<section class="panel" style="margin-top: 20px;" aria-labelledby="totales-heading">
    <h2 id="totales-heading">Totales según los filtros</h2>
    <div class="table-panel"><table>
        <thead>
            <tr>
                <th>Oficina</th>
                <th>Registros</th>
                <th>Unidades</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subtotales as $subtotal)
                <tr>
                    <td>{{ $subtotal['oficina'] }}</td>
                    <td>{{ number_format($subtotal['registros'], 0, ',', '.') }}</td>
                    <td>{{ number_format($subtotal['unidades'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No hay recursos que coincidan con los filtros.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th scope="row">Total general</th>
                <th>{{ number_format($totalGeneral['registros'], 0, ',', '.') }}</th>
                <th>{{ number_format($totalGeneral['unidades'], 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table></div>
</section>

<div class="panel" style="margin-top: 20px;">
    <div class="table-panel"><table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Cantidad</th>
                <th>Oficina</th>
                <th>Estado</th>
                <th class="actions-column">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @php($oficinaAnterior = false)
            @forelse($inventarios as $inventario)
                @if($agrupado && $inventario->oficina_id !== $oficinaAnterior)
                    @php($oficinaAnterior = $inventario->oficina_id)
                    @php($subtotal = $subtotalesPorOficina[$inventario->oficina_id ?? 'sin'])
                    <tr>
                        <th colspan="7" scope="rowgroup">{{ $subtotal['oficina'] }} · Subtotal: {{ number_format($subtotal['unidades'], 0, ',', '.') }} unidades</th>
                    </tr>
                @endif
                <tr>
                    <td>{{ $inventario->codigo }}</td>
                    <td>{{ $inventario->descripcion }}</td>
                    <td>{{ $inventario->categoria }}</td>
                    <td>{{ number_format($inventario->cantidad, 0, ',', '.') }}</td>
                    <td>{{ $inventario->oficina?->nombre ?? 'Sin oficina' }}</td>
                    <td><span class="badge">{{ $inventario->estado }}</span></td>
                    <td class="row">
                        <a href="{{ route('inventarios.show', $inventario) }}" class="action-button action-view">Ver</a>
                        @can('update', $inventario)
                            <a href="{{ route('inventarios.edit', $inventario) }}" class="action-button action-edit">Editar</a>
                        @endcan
                        @can('delete', $inventario)
                            <form method="POST" action="{{ route('inventarios.destroy', $inventario) }}" onsubmit="return confirm('¿Desea eliminar el recurso?');" class="record-action-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-button action-delete">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No hay recursos de inventario registrados.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    @include('partials.pagination', ['paginator' => $inventarios])
</div>
</div>
@endsection
