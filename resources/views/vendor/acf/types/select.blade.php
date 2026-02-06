<select :name="'{{ $inputName }}' + ({{ $field->options['multiple'] ?? false ? 'true' : 'false' }} ? '[]' : '')"
  id="acf-{{ $field->key }}" class="form-select {{ $presentation['input']['class'] ?? '' }}"
  {{ $field->options['multiple'] ?? false ? 'multiple' : '' }}>
  @foreach ($field->options['choices'] ?? [] as $val => $label)
    <option value="{{ $val }}"
      {{ (is_array($value) ? in_array($val, $value) : $value == $val) ? 'selected' : '' }}>
      {{ $label }}
    </option>
  @endforeach
</select>
