@extends('layouts.app')

@section('title', 'Dashboard - SIGDET')

@section('content')
<div class="panel">
    <h1> SIGDET</h1>
    <p class="muted">Resumen general del módulo de gestión del departamento.</p>
</div>

<div class="grid" style="margin-top: 20px;">
    <div class="card">
        <div class="muted">Oficinas</div>
        <h2>{{ $stats['oficinas'] }}</h2>
    </div>
</section>
@can('viewAny', App\Models\FondoFijoMovimiento::class)
<section class="card finance-panel" aria-labelledby="caja-heading">
    <div class="card-heading"><div><h2 id="caja-heading">Total en caja</h2><p>Últimos movimientos y saldo del fondo operativo.</p></div><a class="button secondary" href="{{ route('fondo-fijo.index') }}">Ver movimientos</a></div>
    <div class="finance-grid">
        <div><span class="stat-label">Último ingreso</span><strong class="finance-value">${{ number_format($stats['ultimo_ingreso'], 2, '.', '') }}</strong><span class="stat-description">Origen: {{ $stats['origen_ultimo_ingreso'] }}</span></div>
        <div><span class="stat-label">Último egreso</span><strong class="finance-value">${{ number_format($stats['ultimo_egreso'], 2, '.', '') }}</strong><span class="stat-description">Destino: {{ $stats['destino_ultimo_egreso'] }}</span></div>
        <div class="balance"><span class="stat-label">Saldo acumulado</span><strong class="finance-value">${{ number_format($stats['diferencia_fondo_fijo'], 2, '.', '') }}</strong><span class="stat-description">Total de ingresos menos egresos registrados.</span></div>
    </div>
</div>
@endcan
<section class="card"><div class="card-heading"><div><h2>Accesos de trabajo</h2><p>Módulos disponibles según los permisos de tu cuenta.</p></div></div><div class="module-shortcuts">
    @can('viewAny', App\Models\Oficina::class)<a href="{{ route('oficinas.index') }}"><strong>Oficinas</strong><span>Dependencias y responsables</span><span class="shortcut-arrow" aria-hidden="true">→</span></a>@endcan
    @can('viewAny', App\Models\Inventario::class)<a href="{{ route('inventarios.index') }}"><strong>Inventario</strong><span>Recursos y asignaciones</span><span class="shortcut-arrow" aria-hidden="true">→</span></a>@endcan
    @can('viewAny', App\Models\EquipamientoTecnologico::class)<a href="{{ route('equipamientos-tecnologicos.index') }}"><strong>Equipamiento</strong><span>Equipos y seguimiento técnico</span><span class="shortcut-arrow" aria-hidden="true">→</span></a>@endcan
    @can('viewAny', App\Models\Documento::class)<a href="{{ route('documentos.index') }}"><strong>Documentos</strong><span>Consulta y gestión documental</span><span class="shortcut-arrow" aria-hidden="true">→</span></a>@endcan
    @can('viewAny', App\Models\DroneRobot::class)<a href="{{ route('movilidad.drones.index') }}"><strong>Drones y robots</strong><span>Recursos y mantenimientos</span><span class="shortcut-arrow" aria-hidden="true">→</span></a>@endcan
    @can('viewAny', App\Models\Bateria::class)<a href="{{ route('movilidad.baterias.index') }}"><strong>Baterías</strong><span>Ciclos, asignaciones y revisiones</span><span class="shortcut-arrow" aria-hidden="true">→</span></a>@endcan
    @can('viewAny', App\Models\FondoFijoMovimiento::class)<a href="{{ route('fondo-fijo.index') }}"><strong>Fondo fijo</strong><span>Ingresos, egresos y saldo</span><span class="shortcut-arrow" aria-hidden="true">→</span></a>@endcan
    @can('viewAny', App\Models\Usuario::class)<a href="{{ route('usuarios.index') }}"><strong>Usuarios</strong><span>Cuentas y acceso al sistema</span><span class="shortcut-arrow" aria-hidden="true">→</span></a>@endcan
    @can('viewAny', App\Models\Rol::class)<a href="{{ route('roles.index') }}"><strong>Roles</strong><span>Perfiles y permisos</span><span class="shortcut-arrow" aria-hidden="true">→</span></a>@endcan
</div></section>
@endsection
