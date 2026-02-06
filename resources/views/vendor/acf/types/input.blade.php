<input type="{{ $type ?? 'text' }}" :name="`{{ $inputName }}`" id="acf-{{ $field->key }}"
  class="form-control {{ $presentation['input']['class'] ?? '' }}" style="{{ $presentation['input']['style'] ?? '' }}"
  value="{{ $value }}" placeholder="{{ $field->options['placeholder'] ?? '' }}"
  @if ($field->is_required) required @endif {!! collect($presentation['input']['attributes'] ?? [])->map(fn($v, $k) => "{$k}=\"{$v}\"")->implode(' ') !!}>
