@props(['value'])
@php($labels = ['operativo'=>'Operativo','en_mantenimiento'=>'En mantenimiento','fuera_servicio'=>'Fuera de servicio','baja'=>'Baja','disponible'=>'Disponible','en_uso'=>'En uso','en_revision'=>'En revisión','agotada'=>'Agotada','programado'=>'Programado','en_curso'=>'En curso','realizado'=>'Realizado','cancelado'=>'Cancelado'])
<span class="badge" data-state="{{ $value }}">{{ $labels[$value] ?? ucfirst(str_replace('_', ' ', $value)) }}</span>
