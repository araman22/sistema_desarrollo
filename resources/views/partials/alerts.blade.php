@if ($errors->any())
    <div class="panel" style="border-left: 4px solid #dc2626; margin-bottom: 16px;">
        <strong style="color:#991b1b;">Se encontraron errores:</strong>
        <ul style="margin: 8px 0 0 18px; color:#991b1b;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="panel" style="border-left: 4px solid #16a34a; margin-bottom: 16px;">
        <span class="success">{{ session('success') }}</span>
    </div>
@endif
