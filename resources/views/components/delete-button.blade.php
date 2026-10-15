@props(['action', 'label' => 'Eliminar'])
<form class="inline" method="POST" action="{{ $action }}" onsubmit="return confirm('¿Confirmás la eliminación?')">@csrf @method('DELETE')<button class="danger">{{ $label }}</button></form>
