@props([
    'name',
    'id'          => null,
    'options'     => [],
    'selected'    => null,
    'placeholder' => '— Selecciona —',
    'required'    => false,
])

<select
    name="{{ $name }}"
    id="{{ $id ?? $name }}"
    data-searchable
    data-placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    {{ $attributes->class(['mt-1 block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm']) }}
>
    <option value=""></option>
    @foreach ($options as $value => $label)
        <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $label }}</option>
    @endforeach
</select>
