@extends('layouts.app')

@section('title', 'Caja chica - SIGDET')

@section('content')
<div class="panel">
    <div class="row" style="justify-content: space-between; align-items: center;">
        <div>
            <h1>Caja chica</h1>
            <p class="muted">Registro de ingresos y egresos del fondo operativo.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn secondary">Volver al dashboard</a>
    </div>
</div>

<div class="grid" style="margin-top: 20px;">
    <div class="card">
        <div class="muted">Último ingreso</div>
        <h2>${{ number_format($ultimoIngreso?->monto ?? 0, 2, '.', '') }}</h2>
        <div class="small">Origen: {{ $ultimoIngreso?->origen ?? 'Sin registros' }}</div>
        <div class="small">Fecha: {{ $ultimoIngreso?->fecha?->format('d/m/Y') ?? '-' }}</div>
    </div>
    <div class="card">
        <div class="muted">Último egreso</div>
        <h2>${{ number_format($ultimoEgreso?->monto ?? 0, 2, '.', '') }}</h2>
        <div class="small">Destino: {{ $ultimoEgreso?->destino ?? 'Sin registros' }}</div>
        <div class="small">Fecha: {{ $ultimoEgreso?->fecha?->format('d/m/Y') ?? '-' }}</div>
    </div>
    <div class="card">
        <div class="muted">Diferencia</div>
        <h2>${{ number_format($diferencia, 2, '.', '') }}</h2>
        <div class="small">Resultado entre último ingreso y último egreso.</div>
    </div>
</div>

<div class="grid" style="margin-top: 24px;">
    <div class="panel">
        <h2>Registrar ingreso</h2>
        <form action="{{ route('caja-chica.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tipo" value="ingreso">

            <div class="field">
                <label for="ingreso_monto">Monto</label>
                <input id="ingreso_monto" type="number" name="monto" min="0.01" step="0.01" required>
            </div>

            <div class="field">
                <label for="ingreso_origen">Origen del dinero</label>
                <input id="ingreso_origen" type="text" name="origen" placeholder="Ej: Junta mensual" required>
            </div>

            <div class="field">
                <label for="ingreso_fecha">Fecha</label>
                <input id="ingreso_fecha" type="date" name="fecha" required>
            </div>

            <div class="field">
                <label for="ingreso_descripcion">Descripción</label>
                <textarea id="ingreso_descripcion" name="descripcion" rows="3" placeholder="Detalle del ingreso"></textarea>
            </div>

            <button type="submit" class="btn">Guardar ingreso</button>
        </form>
    </div>

    <div class="panel">
        <h2>Registrar egreso</h2>
        <form action="{{ route('caja-chica.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tipo" value="egreso">

            <div class="field">
                <label for="egreso_monto">Monto</label>
                <input id="egreso_monto" type="number" name="monto" min="0.01" step="0.01" required>
            </div>

            <div class="field">
                <label for="egreso_destino">Destino del dinero</label>
                <input id="egreso_destino" type="text" name="destino" placeholder="Ej:Compra de útiles de oficina" required>
            </div>

            <div class="field">
                <label for="egreso_fecha">Fecha</label>
                <input id="egreso_fecha" type="date" name="fecha" required>
            </div>

            <div class="field">
                <label for="egreso_descripcion">Descripción</label>
                <textarea id="egreso_descripcion" name="descripcion" rows="3" placeholder="Detalle del gasto"></textarea>
            </div>

            <div class="field">
                <label for="ticket">Ticket de compra</label>
                <input id="ticket" type="file" name="ticket" accept=".pdf,.jpg,.jpeg,.png">
            </div>

            <button type="submit" class="btn">Guardar egreso</button>
        </form>
    </div>
</div>

<div class="panel" style="margin-top: 24px;">
    <h2>Historial</h2>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Monto</th>
                <th>Origen / Destino</th>
                <th>Fecha</th>
                <th>Ticket</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movimientos as $movimiento)
                <tr>
                    <td><span class="badge">{{ $movimiento->tipo === 'ingreso' ? 'Ingreso' : 'Egreso' }}</span></td>
                    <td>${{ number_format($movimiento->monto, 2, '.', '') }}</td>
                    <td>{{ $movimiento->tipo === 'ingreso' ? $movimiento->origen : $movimiento->destino }}</td>
                    <td>{{ $movimiento->fecha->format('d/m/Y') }}</td>
                    <td>
                        @if ($movimiento->ticket_path)
                            <a href="{{ route('caja-chica.ticket', $movimiento) }}" target="_blank">Ver comprobante</a>
                        @else
                            <span class="muted">Sin comprobante</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">No hay movimientos registrados todavía.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 16px;">
        {{ $movimientos->links() }}
    </div>
</div>
@endsection
