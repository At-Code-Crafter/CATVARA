<div class="acf-repeater" x-data="repeaterField({
    field: @json($field),
    value: @json($value)
})">
  <div class="acf-repeater-rows" id="repeater-{{ $field->id }}">
    <template x-for="(row, index) in rows" :key="row.__row_index">
      <div class="acf-repeater-row mb-3 p-3 bg-white border rounded shadow-sm pos-relative">
        <div class="acf-repeater-row-handle me-2 cursor-move pos-absolute"
          style="left: -10px; top: 50%; transform: translateY(-50%);">
          <i class="bi bi-grip-vertical text-muted"></i>
        </div>

        <button type="button" class="btn-close btn-sm pos-absolute" style="top:5px; right:5px"
          @click="removeRow(index)"></button>

        <div class="row g-3">
          @foreach ($subFields as $subField)
            <div class="col-12">
              <label class="form-label small fw-bold">{{ $subField->name }}</label>
              {!! acf_field($subField)->render($subField, null) !!}
              {{-- Note: we need to handle the name attribute for subfields to be row-aware --}}
            </div>
          @endforeach
        </div>
      </div>
    </template>
  </div>

  <div class="text-center py-3 bg-light border border-dashed rounded mb-3" x-show="rows.length === 0">
    No rows added yet.
  </div>

  <button type="button" class="btn btn-sm btn-outline-primary" @click="addRow()">
    <i class="bi bi-plus-lg me-1"></i> Add Row
  </button>
</div>

<script>
  if (!window.repeaterField) {
    window.repeaterField = function(config) {
      return {
        rows: config.value || [],
        nextIndex: (config.value || []).length,
        init() {
          // Logic to handle sorting can be added here
        },
        addRow() {
          const newRow = {
            __row_index: Date.now()
          };
          config.field.sub_fields.forEach(sf => {
            newRow[sf.key] = sf.default_value || '';
          });
          this.rows.push(newRow);
        },
        removeRow(index) {
          this.rows.splice(index, 1);
        }
      }
    }
  }
</script>
