<div class="form-check form-switch">
  <input class="form-check-input" type="checkbox" name="acf[{{ $field->key }}]" value="1" id="acf-{{ $field->key }}"
    {{ $value ? 'checked' : '' }}>
  <label class="form-check-label" for="acf-{{ $field->key }}">
    {{ $field->options['label'] ?? '' }}
  </label>
</div>
