<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIGDET')</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; color: #1f2937; }
        a { color: #1d4ed8; text-decoration: none; }
        .topbar { background: #111827; color: white; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
        .nav { display: flex; gap: 16px; flex-wrap: wrap; }
        .nav a { color: white; }
        .container { max-width: 1200px; margin: 24px auto; padding: 0 20px 40px; }
        .panel { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        .card { background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 18px; }
        .btn { display: inline-block; background: #2563eb; color: white; padding: 8px 14px; border-radius: 6px; border: none; cursor: pointer; }
        .btn.secondary { background: #e5e7eb; color: #111827; }
        .btn.danger { background: #dc2626; }
        .muted { color: #6b7280; }
        .row { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        input, select, textarea { width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; }
        .field { margin-bottom: 14px; }
        .field label { display: block; margin-bottom: 6px; font-weight: 600; }
        .error { color: #b91c1c; font-size: 0.85rem; }
        .success { color: #166534; }
        .small { font-size: 0.85rem; }
        .badge { display: inline-block; background: #dbeafe; color: #1d4ed8; padding: 4px 8px; border-radius: 999px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <header class="topbar">
        <strong>SIGDET</strong>
        <nav class="nav">
            <a href="{{ route('dashboard') }}">Principal</a>
            <a href="{{ route('caja-chica.index') }}">Control de gastos</a>
            <a href="{{ route('oficinas.index') }}">Oficinas</a>
            <a href="{{ route('inventarios.index') }}">Inventario</a>
            <a href="{{ route('equipamientos-tecnologicos.index') }}">Equipamiento</a>
            <a href="{{ route('tipo-documentos.index') }}">Tipos de documento</a>
            <a href="{{ route('documentos.index') }}">Documentos</a>
        </nav>
    </header>

    <main class="container">
        @include('partials.alerts')
        @yield('content')
    </main>
</body>
</html>
