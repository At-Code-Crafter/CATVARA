{{-- resources/views/types/text.blade.php --}}
<input type="text" :name="`{{ $inputName }}`" id="acf-{{ $field->key }}"
  class="form-control {{ $presentation['input']['class'] ?? '' }}" style="{{ $presentation['input']['style'] ?? '' }}"
  value="{{ $value }}" placeholder="{{ $field->options['placeholder'] ?? '' }}"
  @if ($field->is_required) required @endif {!! collect($presentation['input']['attributes'] ?? [])->map(fn($v, $k) => "{$k}=\"{$v}\"")->implode(' ') !!}>

<!-- slide -->

{{-- resources/views/types/textarea.blade.php --}}
<textarea :name="`{{ $inputName }}`" id="acf-{{ $field->key }}"
  class="form-control {{ $presentation['input']['class'] ?? '' }}" rows="{{ $field->options['rows'] ?? 3 }}"
  @if ($field->is_required) required @endif>{{ $value }}</textarea>

<!-- slide -->

{{-- resources/views/types/toggle.blade.php --}}
<div class="form-check form-switch">
  <input class="form-check-input" type="checkbox" :name="`{{ $inputName }}`" value="1"
    id="acf-{{ $field->key }}" {{ $value ? 'checked' : '' }}>
  <label class="form-check-label" for="acf-{{ $field->key }}">
    {{ $field->options['label'] ?? '' }}
  </label>
</div>

<!-- slide -->

{{-- resources/views/types/select.blade.php --}}
<select :name="`{{ $inputName }}` + '{{ $field->options['multiple'] ?? false ? '[]' : '' }}'"
  id="acf-{{ $field->key }}" class="form-select {{ $presentation['input']['class'] ?? '' }}"
  {{ $field->options['multiple'] ?? false ? 'multiple' : '' }}>
  @foreach ($field->options['choices'] ?? [] as $val => $label)
    <option value="{{ $val }}"
      {{ (is_array($value) ? in_array($val, $value) : $value == $val) ? 'selected' : '' }}>
      {{ $label }}
    </option>
  @endforeach
</select>
