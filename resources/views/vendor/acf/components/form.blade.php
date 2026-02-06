<div class="acf-form-container">
  @foreach ($groups as $group)
    <div class="acf-group-card card mb-4 shadow-sm border-0 rounded-3 overflow-hidden">
      <div class="card-header bg-light border-bottom py-3 px-4">
        <h5 class="mb-0 fw-bold">{{ $group->name }}</h5>
        @if ($group->description)
          <p class="text-muted small mb-0">{{ $group->description }}</p>
        @endif
      </div>
      <div class="card-body p-4">
        @foreach ($group->fields as $field)
          <div class="acf-field-wrapper mb-4" data-key="{{ $field->key }}" x-data="acfConditionalLogic({{ json_encode($field->conditional_logic ?? []) }})" x-show="show" x-cloak>
            <label class="form-label fw-bold d-block mb-1">
              {{ $field->name }}
              @if ($field->is_required)
                <span class="text-danger">*</span>
              @endif
            </label>
            @if ($field->instructions)
              <div class="form-text small text-muted mb-2">{{ $field->instructions }}</div>
            @endif

            <div class="acf-input-slot">
              {!! app(\Arshpharala\AdvancedCustomFields\Support\FieldTypeRegistry::class)->get($field->type)->renderInput($field, $model ? $model->acf($field->key) : null) !!}
            </div>

            @error('acf.' . $field->key)
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
</div>
