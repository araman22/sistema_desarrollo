@props(['action', 'label' => 'Eliminar'])
<form class="inline" method="POST" action="{{ $action }}" onsubmit="return confirm('¿Confirmás la eliminación?')">@csrf @method('DELETE')<button type="submit" class="action-button action-delete">{{ $label }}</button></form>
