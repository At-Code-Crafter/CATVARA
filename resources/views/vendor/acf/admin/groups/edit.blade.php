@extends('acf::admin.layout')

@section('title', $is_edit ? 'Edit Field Group' : 'New Field Group')

@section('breadcrumb')
  <li class="breadcrumb-item active">{{ $is_edit ? $group->name : 'New' }}</li>
@endsection

@section('content')
  <form x-data="fieldManager()"
    action="{{ $is_edit ? route('acf.admin.groups.update', $group) : route('acf.admin.groups.store') }}" method="POST">
    @csrf
    @if ($is_edit)
      @method('PUT')
    @endif

    <div class="row">
      <div class="col-md-8">
        <div class="acf-card bg-white p-4 mb-4">
          <h5 class="fw-bold mb-4">Group Settings</h5>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Group Name</label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $group->name) }}" placeholder="e.g. Hero Section Settings">
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Group Key</label>
              <input type="text" name="key" class="form-control @error('key') is-invalid @enderror"
                value="{{ old('key', $group->key) }}" placeholder="hero_settings">
              @error('key')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Position</label>
              <select name="position" class="form-select">
                <option value="normal" {{ $group->position == 'normal' ? 'selected' : '' }}>Normal (Main column)</option>
                <option value="sidebar" {{ $group->position == 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                <option value="after_title" {{ $group->position == 'after_title' ? 'selected' : '' }}>After Title</option>
              </select>
            </div>
          </div>
        </div>

        <div class="acf-card bg-white p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold m-0">Fields</h5>
            @if ($is_edit)
              <button type="button" class="btn btn-sm btn-outline-primary" @click="addField()">
                <i class="bi bi-plus-lg me-1"></i> Add Field
              </button>
            @else
              <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Save group first to add
                fields</span>
            @endif
          </div>

          <div id="fields-container">
            @forelse($group->fields as $field)
              <div class="field-row p-3 d-flex align-items-center" data-id="{{ $field->id }}">
                <div class="me-3 text-muted"><i class="bi bi-grip-vertical fs-5"></i></div>
                <div class="flex-grow-1">
                  <div class="fw-bold">{{ $field->name }}</div>
                  <div class="small text-muted"><code>{{ $field->key }}</code> | {{ ucfirst($field->type) }}</div>
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-light border" @click="editField({{ $field }})"><i
                      class="bi bi-pencil"></i></button>
                  <button type="button" class="btn btn-sm btn-light border text-danger"
                    @click="deleteField({{ $field->id }})"><i class="bi bi-trash"></i></button>
                </div>
              </div>
            @empty
              <div class="text-center py-4 text-muted border border-dashed rounded" x-show="fields.length === 0">
                No fields created yet.
              </div>
            @endforelse
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="acf-card bg-white p-4 mb-4">
          <h5 class="fw-bold mb-3">Publish</h5>
          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_active" value="1"
                {{ $group->is_active ? 'selected' : '' }} id="statusSwitch">
              <label class="form-check-label" for="statusSwitch">Active</label>
            </div>
          </div>
          <hr>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('acf.admin.index') }}" class="btn btn-light text-muted">Cancel</a>
          </div>
        </div>

        <div class="acf-card bg-white p-4" x-data="locationManager()">
          <h5 class="fw-bold mb-3">Location Rules</h5>
          <p class="small text-muted mb-3">Select where this group should appear.</p>

          <template x-for="(loc, index) in locations" :key="index">
            <div class="mb-3 p-3 bg-light rounded border border-dashed pos-relative">
              <button type="button" class="btn-close btn-sm pos-absolute" style="top:5px; right:5px"
                @click="removeRule(index)"></button>
              <div class="mb-2" x-data="{ custom: false }">
                <label class="form-label small fw-bold">Model Type</label>
                <div class="input-group input-group-sm">
                  <select :name="'locations[' + index + '][model_type]'" x-model="loc.model_type"
                    class="form-select form-select-sm" x-show="!custom">
                    @foreach ($models as $model)
                      <option value="{{ $model }}">{{ class_basename($model) }} ({{ $model }})</option>
                    @endforeach
                    <option value="" disabled>-- Custom --</option>
                  </select>
                  <input type="text" :name="'locations[' + index + '][model_type]'" x-model="loc.model_type"
                    class="form-control" placeholder="App\Models\..." x-show="custom">
                  <button type="button" class="btn btn-outline-secondary" @click="custom = !custom">
                    <i class="bi" :class="custom ? 'bi-list' : 'bi-pencil'"></i>
                  </button>
                </div>
              </div>
            </div>
          </template>

          <button type="button" class="btn btn-sm btn-outline-secondary w-100" @click="addRule()">Add Rule</button>
        </div>
      </div>
    </div>

    <div x-show="showFieldModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" x-cloak>
      <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-light">
            <h5 class="modal-title fw-bold" x-text="editingField ? 'Edit Field' : 'Add New Field'"></h5>
            <button type="button" class="btn-close" @click="closeModal()"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Field Label</label>
                <input type="text" x-model="fieldForm.name" class="form-control" placeholder="e.g. Subtitle">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Field Key</label>
                <input type="text" x-model="fieldForm.key" class="form-control" placeholder="e.g. subtitle">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Field Type</label>
                <select x-model="fieldForm.type" class="form-select">
                  <option value="text">Text</option>
                  <option value="textarea">Text Area</option>
                  <option value="number">Number</option>
                  <option value="select">Select</option>
                  <option value="toggle">Toggle</option>
                  <option value="date">Date</option>
                  <option value="email">Email</option>
                  <option value="url">URL</option>
                  <option value="color">Color</option>
                  <option value="repeater">Repeater</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Required?</label>
                <select x-model="fieldForm.is_required" class="form-select">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small fw-bold">Instructions</label>
                <textarea x-model="fieldForm.instructions" class="form-control" rows="2"></textarea>
              </div>

              <!-- Sub Fields section only for Repeater -->
              <div class="col-12" x-show="fieldForm.type === 'repeater'">
                <div class="card border-0 bg-light p-3">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0 small">Sub Fields</h6>
                    <button type="button" class="btn btn-sm btn-primary" @click="addSubField()">
                      <i class="bi bi-plus-lg"></i> Add Sub Field
                    </button>
                  </div>

                  <div class="sub-fields-list">
                    <template x-for="(sub, sIndex) in fieldForm.sub_fields" :key="sIndex">
                      <div class="p-3 bg-white rounded border mb-2 pos-relative shadow-sm">
                        <button type="button" class="btn-close btn-sm pos-absolute" style="top:5px; right:5px"
                          @click="removeSubField(sIndex)"></button>
                        <div class="row g-2">
                          <div class="col-md-4">
                            <label class="form-label mb-1 xx-small fw-bold">Label</label>
                            <input type="text" x-model="sub.name" class="form-control form-control-sm"
                              placeholder="Label">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label mb-1 xx-small fw-bold">Key</label>
                            <input type="text" x-model="sub.key" class="form-control form-control-sm"
                              placeholder="key">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label mb-1 xx-small fw-bold">Type</label>
                            <select x-model="sub.type" class="form-select form-select-sm">
                              <option value="text">Text</option>
                              <option value="textarea">Text Area</option>
                              <option value="number">Number</option>
                              <option value="select">Select</option>
                              <option value="toggle">Toggle</option>
                              <option value="date">Date</option>
                              <option value="email">Email</option>
                              <option value="url">URL</option>
                              <option value="color">Color</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </template>
                    <div class="text-center py-2 text-muted x-small" x-show="fieldForm.sub_fields.length === 0">
                      No sub fields added.
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light border-0">
            <button type="button" class="btn btn-light" @click="closeModal()">Cancel</button>
            <button type="button" class="btn btn-primary px-4" @click="saveField()" :disabled="saving">
              <span x-show="saving" class="spinner-border spinner-border-sm me-1"></span>
              Save Field
            </button>
          </div>
        </div>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
  <script>
    function locationManager() {
      return {
        locations: @json($group->locations),
        addRule() {
          this.locations.push({
            model_type: @json($models[0] ?? '')
          });
        },
        removeRule(index) {
          this.locations.splice(index, 1);
        }
      }
    }

    function fieldManager() {
      return {
        fields: @json($group->fields),
        showFieldModal: false,
        editingField: null,
        saving: false,
        fieldForm: {
          group_id: '{{ $group->id }}',
          name: '',
          key: '',
          type: 'text',
          instructions: '',
          is_required: '0',
          default_value: '',
          options: {},
          presentation: {},
          sub_fields: []
        },
        init() {
          const el = document.getElementById('fields-container');
          if (el) {
            Sortable.create(el, {
              animation: 150,
              ghostClass: 'bg-light',
              onEnd: (evt) => {
                const order = Array.from(el.children).map(row => row.dataset.id);
                this.updateOrder(order);
              }
            });
          }
        },
        addField() {
          this.editingField = null;
          this.fieldForm = {
            group_id: '{{ $group->id }}',
            name: '',
            key: '',
            type: 'text',
            instructions: '',
            is_required: '0',
            options: {},
            presentation: {},
            sub_fields: []
          };
          this.showFieldModal = true;
        },
        editField(field) {
          this.editingField = field;
          this.fieldForm = {
            ...field,
            sub_fields: field.sub_fields || []
          };
          this.showFieldModal = true;
        },
        closeModal() {
          this.showFieldModal = false;
        },
        addSubField() {
          this.fieldForm.sub_fields.push({
            name: '',
            key: '',
            type: 'text',
            instructions: '',
            is_required: '0',
            options: {},
            presentation: {}
          });
        },
        removeSubField(index) {
          this.fieldForm.sub_fields.splice(index, 1);
        },
        async saveField() {
          this.saving = true;
          const url = this.editingField ?
            `{{ route('acf.admin.fields.store') }}/${this.editingField.id}` :
            `{{ route('acf.admin.fields.store') }}`;

          const method = this.editingField ? 'PUT' : 'POST';

          try {
            const response = await fetch(url, {
              method: method,
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: JSON.stringify(this.fieldForm)
            });

            if (response.ok) {
              window.location.reload();
            } else {
              const err = await response.json();
              alert(err.message || 'Error saving field');
            }
          } catch (e) {
            alert('An error occurred');
          } finally {
            this.saving = false;
          }
        },
        deleteField(id) {
          if (confirm('Delete this field? Stored values will remain but will be orphaned.')) {
            fetch(`{{ route('acf.admin.fields.store') }}/${id}`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              }
            }).then(() => window.location.reload());
          }
        },
        updateOrder(order) {
          fetch('{{ route('acf.admin.fields.sort') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              order
            })
          });
        }
      }
    }
  </script>
@endpush
