@props(['name', 'label', 'value' => null, 'type' => 'text', 'required' => false])
<div><label for="{{ $name }}">{{ $label }}{{ $required ? ' *' : '' }}</label>
@if($type === 'textarea')<textarea id="{{ $name }}" name="{{ $name }}" {{ $attributes }} @required($required)>{{ old($name, $value) }}</textarea>
@else<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" {{ $attributes }} @required($required)>@endif
@error($name)<span class="field-error">{{ $message }}</span>@enderror</div>
