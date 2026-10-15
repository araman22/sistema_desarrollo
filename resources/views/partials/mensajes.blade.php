@if (session('success'))
    <p role="status">{{ session('success') }}</p>
@endif

@if (session('error'))
    <p role="alert">{{ session('error') }}</p>
@endif

@if ($errors->any())
    <div role="alert">
        <p>Revisá los siguientes errores:</p>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
