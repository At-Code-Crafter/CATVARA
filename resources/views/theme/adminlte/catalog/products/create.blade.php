@extends('theme.adminlte.layouts.app')

@section('title', 'Create Product')

@section('content-header')
<link rel="stylesheet" href="{{ asset('assets/css/enterprise.css') }}">
  <div class="row mb-2 align-items-center">
    <div class="col-sm-8">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <span class="customer-page-icon d-inline-flex align-items-center justify-content-center">
            <i class="fas fa-plus"></i>
          </span>
        </div>
        <div>
          <h1 class="m-0">Create Product</h1>
          <div class="text-muted small">Create product, generate variants and pricing in one flow.</div>
        </div>
      </div>
    </div>

    <div class="col-sm-4 d-flex justify-content-sm-end mt-3 mt-sm-0" style="gap:10px;">
      <a href="{{ company_route('catalog.products.index') }}" class="btn btn-outline-secondary btn-ent">
        <i class="fas fa-arrow-left mr-1"></i> Back
      </a>
      <button type="button" class="btn btn-primary btn-ent" id="btnSubmitTop">
        <i class="fas fa-save mr-1"></i> Create
      </button>
    </div>
  </div>
@endsection

@section('content')
  <form id="productForm" action="{{ company_route('catalog.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row">
      {{-- LEFT: BASIC + IMAGE + VARIANT SETTINGS --}}
      <div class="col-lg-4 col-md-12">

        {{-- BASIC INFORMATION --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Basic Information</h3>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label for="name">Product Name <span class="req">*</span></label>
              <input type="text" class="form-control ent-control" id="name" name="name" required
                placeholder="e.g. Cotton T-Shirt">
            </div>

            <div class="form-group">
              <label for="category_id">Category <span class="req">*</span></label>
              <select class="form-control ent-control select2" name="category_id" required>
                <option value="">Select Category</option>
                @foreach ($categories as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
              </select>
              <div class="help-hint">Changing category will refresh available attributes for variant generation.</div>
            </div>

            <div class="form-group mb-0">
              <label for="description">Description</label>
              <textarea class="form-control ent-control" name="description" rows="3"></textarea>
            </div>
          </div>
        </div>

        {{-- MAIN IMAGE (SINGLE) --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-image"></i> Main Image</h3>
          </div>
          <div class="card-body">

            @php
              $placeholder = asset('theme/adminlte/dist/img/default-150x150.png');
            @endphp

            <label class="mb-2">Upload Product Image</label>

            <div class="ent-preview-card text-center"
                 style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;background:#fff;">
              <img id="mainImagePreview"
                   src="{{ $placeholder }}"
                   class="img-fluid rounded"
                   style="max-height: 240px;"
                   alt="preview">
            </div>

            <div class="d-flex align-items-center flex-wrap mt-3" style="gap:10px;">
              <label class="btn btn-outline-primary btn-ent mb-0" style="cursor:pointer;">
                <i class="fas fa-upload mr-1"></i> Choose Image
                <input id="mainImageInput" type="file" name="image" class="d-none" accept="image/*">
              </label>

              <button type="button" id="clearMainImage" class="btn btn-outline-secondary btn-ent">
                <i class="fas fa-times mr-1"></i> Clear
              </button>

              <div class="text-muted small" id="mainImageFileName">No file selected</div>
            </div>

            <div class="help-hint mt-2">
              This will be saved into <b>$product->image</b> and used as primary listing thumbnail.
            </div>

          </div>
        </div>

        {{-- VARIANT SETTINGS --}}
        <div class="card ent-card mb-3">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-sliders-h"></i> Variant Settings</h3>
          </div>
          <div class="card-body">
            <label>Select Attributes to Generate Variants</label>

            <div class="ent-divider"></div>

            <div class="form-group mb-0" id="attrContainer">
              @foreach ($attributes as $attr)
                <div class="mb-3">
                  <label>{{ $attr->name }}</label>
                  <select class="form-control ent-control attribute-selector"
                    data-attr-id="{{ $attr->id }}"
                    data-attr-name="{{ $attr->name }}"
                    multiple>
                    @foreach ($attr->values as $val)
                      <option value="{{ $val->id }}">{{ $val->value }}</option>
                    @endforeach
                  </select>
                </div>
              @endforeach
            </div>

            <div class="ent-divider"></div>

            <div class="d-flex" style="gap:10px;">
              <button type="button" class="btn btn-primary btn-ent flex-fill" id="btnGenerate">
                <i class="fas fa-bolt mr-1"></i> Generate
              </button>
              <button type="button" class="btn btn-outline-secondary btn-ent flex-fill" id="btnClear">
                <i class="fas fa-undo mr-1"></i> Clear
              </button>
            </div>

            <div class="help-hint">Select values in one or more attributes, then generate combinations.</div>
          </div>
        </div>

        {{-- QUICK HELP --}}
        <div class="card ent-card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-lightbulb"></i> Tips</h3>
          </div>
          <div class="card-body">
            <div class="text-muted small">
              <div class="mb-2">
                <b>SKU:</b> You can leave SKU empty and auto-generate later, or fill now.
              </div>
              <div class="mb-2">
                <b>Prices:</b> Cost and Selling price are per-variant.
              </div>
              <div>
                <b>Remove:</b> Use the trash icon to delete a single variant row.
              </div>
            </div>
          </div>
        </div>

      </div>

      {{-- RIGHT: VARIANTS TABLE --}}
      <div class="col-lg-8 col-md-12">
        <div class="card ent-card">
          <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
              <h3 class="card-title"><i class="fas fa-tags"></i> Product Variants</h3>
              <span class="ent-chip" id="variantCountChip">
                <i class="fas fa-layer-group"></i> 0 Variants
              </span>
            </div>
          </div>

          <div class="card-body p-0">
            <div class="table-responsive ent-variant-scroll">
              <table class="table table-hover table-enterprise mb-0" id="variantTable">
                <thead>
                  <tr>
                    <th style="min-width:260px;">Variant Name</th>
                    <th style="min-width:160px;">SKU</th>
                    <th style="min-width:140px;">Cost Price</th>
                    <th style="min-width:140px;">Selling Price</th>
                    <th style="min-width:90px;" class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr id="emptyRow">
                    <td colspan="5" class="text-center text-muted">No variants generated yet.</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="ent-callout">
              <i class="fas fa-info-circle mr-2"></i>
              Generate variants from selected attributes, then review SKU and pricing before creating.
            </div>
          </div>

          <div class="card-footer d-flex justify-content-end" style="gap:10px;">
            <button type="button" class="btn btn-outline-secondary btn-ent" id="btnClearBottom">
              <i class="fas fa-undo mr-1"></i> Clear
            </button>
            <button type="button" class="btn btn-primary btn-ent" id="btnSubmit">
              <i class="fas fa-save mr-1"></i> Create Product & Variants
            </button>
          </div>
        </div>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
  <script>
    $(document).ready(function() {

      // Tooltips
      if ($.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
      }

      // Init Select2
      if ($.fn.select2) {
        $('.select2').select2({
          theme: 'bootstrap4',
          width: '100%'
        });

        $('.attribute-selector').select2({
          theme: 'bootstrap4',
          width: '100%',
          placeholder: "Select values..."
        });
      }

      // --- Single Image Preview ---
      const $imgInput = $('#mainImageInput');
      const $imgPreview = $('#mainImagePreview');
      const $imgName = $('#mainImageFileName');
      const originalSrc = $imgPreview.attr('src');

      function resetMainImage() {
        $imgInput.val('');
        $imgPreview.attr('src', originalSrc);
        $imgName.text('No file selected');
      }

      $('#clearMainImage').on('click', function() {
        resetMainImage();
      });

      $imgInput.on('change', function(e) {
        const file = (e.target.files && e.target.files[0]) ? e.target.files[0] : null;

        if (!file) {
          resetMainImage();
          return;
        }

        if (!file.type || !file.type.startsWith('image/')) {
          resetMainImage();
          return;
        }

        $imgName.text(file.name);
        const url = URL.createObjectURL(file);
        $imgPreview.attr('src', url);
      });

      // Dynamic Attribute Loading by Category
      $('[name="category_id"]').change(function() {
        var catId = $(this).val();
        if (!catId) return;

        var url = "{{ company_route('catalog.categories.attributes', ['category' => ':id']) }}";
        url = url.replace(':id', catId);

        $.get(url, function(data) {
          var container = $('#attrContainer');
          container.empty();

          if (!Array.isArray(data) || data.length === 0) {
            container.html('<p class="text-muted mb-0">No attributes assigned to this category.</p>');
            return;
          }

          data.forEach(function(attr) {
            var html = `
              <div class="mb-3">
                <label>${attr.name}</label>
                <select class="form-control ent-control attribute-selector"
                  data-attr-id="${attr.id}"
                  data-attr-name="${attr.name}"
                  multiple="multiple" style="width:100%;">
                  ${(attr.values || []).map(v => `<option value="${v.id}">${v.value}</option>`).join('')}
                </select>
              </div>
            `;
            container.append(html);
          });

          if ($.fn.select2) {
            $('.attribute-selector').select2({
              theme: 'bootstrap4',
              width: '100%',
              placeholder: "Select values..."
            });
          }
        });
      });

      // count chip
      function updateVariantCount() {
        var count = $('#variantTable tbody tr.variant-row').length;
        $('#variantCountChip').html('<i class="fas fa-layer-group"></i> ' + count + ' Variants');
      }

      // Generate Variants
      $('#btnGenerate').click(function() {
        let selectedAttrs = [];

        $('.attribute-selector').each(function() {
          let attrId = $(this).data('attr-id');
          let values = $(this).select2 ? $(this).select2('data') : [];

          if (values && values.length > 0) {
            selectedAttrs.push({
              id: attrId,
              values: values.map(v => ({
                id: v.id,
                text: v.text
              }))
            });
          }
        });

        if (selectedAttrs.length === 0) {
          alert("Please select at least one attribute value.");
          return;
        }

        let combinations = cartesian(selectedAttrs.map(a => a.values));
        let tbody = $('#variantTable tbody');
        tbody.empty();

        combinations.forEach((combo, index) => {
          if (!Array.isArray(combo)) combo = [combo];

          let name = combo.map(c => c.text).join(' / ');
          let attrIds = combo.map(c => c.id);

          let rowId = 'row_' + Date.now() + '_' + index;

          let tr = `
            <tr id="${rowId}" class="variant-row">
              <td>
                <div class="font-weight-bold">${name}</div>
                <input type="hidden" name="variants[${index}][name]" value="${name}">
                ${attrIds.map(id => `<input type="hidden" name="variants[${index}][attributes][]" value="${id}">`).join('')}
              </td>

              <td>
                <input type="text" class="form-control ent-control form-control-sm"
                  name="variants[${index}][sku]" placeholder="SKU">
              </td>

              <td>
                <input type="number" step="0.01" class="form-control ent-control form-control-sm"
                  name="variants[${index}][cost]" placeholder="0.00">
              </td>

              <td>
                <input type="number" step="0.01" class="form-control ent-control form-control-sm"
                  name="variants[${index}][price]" placeholder="0.00">
              </td>

              <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger btn-ent ent-icon-btn"
                  data-toggle="tooltip" title="Remove"
                  onclick="$('#${rowId}').remove(); updateVariantCount();">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          `;
          tbody.append(tr);
        });

        if ($.fn.tooltip) {
          $('[data-toggle="tooltip"]').tooltip();
        }

        updateVariantCount();
      });

      function resetVariants() {
        $('#variantTable tbody').html(
          '<tr id="emptyRow"><td colspan="5" class="text-center text-muted">No variants generated yet.</td></tr>'
        );
        updateVariantCount();
      }

      $('#btnClear, #btnClearBottom').click(function() {
        resetVariants();
      });

      // Cartesian Helper
      function cartesian(args) {
        var r = [],
          max = args.length - 1;

        function helper(arr, i) {
          for (var j = 0, l = args[i].length; j < l; j++) {
            var a = arr.slice(0);
            a.push(args[i][j]);
            if (i == max) r.push(a);
            else helper(a, i + 1);
          }
        }
        helper([], 0);
        return r;
      }

      // AJAX Submit with FormData (required for file upload)
      function submitCreate() {
        if (!$('#name').val()) {
          alert('Name is required');
          return;
        }

        // Ensure at least 1 variant row exists
        var variantCount = $('#variantTable tbody tr.variant-row').length;
        if (variantCount === 0) {
          alert('Please generate at least one variant.');
          return;
        }

        let form = document.getElementById('productForm');
        let fd = new FormData(form);

        $.ajax({
          url: $('#productForm').attr('action'),
          method: 'POST',
          data: fd,
          processData: false,
          contentType: false,
          success: function(res) {
            if (res && res.success) {
              window.location.href = res.redirect;
            } else {
              alert('Unexpected response from server.');
            }
          },
          error: function(err) {
            let msg = 'Currently there is an error';
            if (err.responseJSON && err.responseJSON.message) msg = err.responseJSON.message;
            alert('Error: ' + msg);
          }
        });
      }

      $('#btnSubmit, #btnSubmitTop').click(function() {
        submitCreate();
      });

      // Initial
      updateVariantCount();
    });
  </script>
@endpush
