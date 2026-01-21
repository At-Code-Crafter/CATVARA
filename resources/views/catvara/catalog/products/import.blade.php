@extends('catvara.layouts.app')

@section('title', 'Import Products')

@section('content')
  <div class="mx-auto" id="import-app">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-slate-800">Product Import Wizard</h2>
        <p class="text-slate-500 mt-1">Upload and map your spreadsheet to import products efficiently.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ company_route('catalog.products.index') }}" class="btn btn-white">
          <i class="fas fa-arrow-left mr-2"></i> Back to Products
        </a>
      </div>
    </div>

    <!-- Stepper -->
    <div class="mb-10 px-8">
      <div class="relative flex items-center justify-between">
        <div class="absolute left-0 top-1/2 w-full h-0.5 bg-slate-100 -translate-y-1/2 z-0"></div>
        <div id="step-line"
          class="absolute left-0 top-1/2 h-0.5 bg-brand-400 -translate-y-1/2 z-0 transition-all duration-500"
          style="width: 0%"></div>

        @foreach (['Upload', 'Configure', 'Validate', 'Complete'] as $index => $step)
          <div class="relative z-10 flex flex-col items-center step-item" data-step="{{ $index + 1 }}">
            <div
              class="step-circle w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $index === 0 ? 'bg-brand-400 text-white shadow-lg shadow-brand-400/30' : 'bg-white border-2 border-slate-200 text-slate-400' }}">
              {{ $index + 1 }}
            </div>
            <span
              class="mt-2 text-xs font-bold uppercase tracking-wider {{ $index === 0 ? 'text-brand-400' : 'text-slate-400' }}">{{ $step }}</span>
          </div>
        @endforeach
      </div>
    </div>

    <!-- Step 1: Upload -->
    <div id="step-1" class="step-content animate-fade-in group">
      <div
        class="card p-10 flex flex-col items-center justify-center border-dashed border-2 bg-slate-50/50 hover:bg-white hover:border-brand-400 transition-all cursor-pointer min-h-[400px]"
        onclick="document.getElementById('file-input').click()">
        <div
          class="w-20 h-20 bg-brand-50 rounded-3xl flex items-center justify-center text-brand-400 mb-6 group-hover:scale-110 transition-transform shadow-sm">
          <i class="fas fa-cloud-upload-alt text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">Upload your file</h3>
        <p class="text-slate-500 text-center max-w-sm mb-6">Drag and drop your XLSX or CSV file here, or click to browse.
          Multiple sheets are supported.</p>
        <input type="file" id="file-input" class="hidden" accept=".xlsx, .xls, .csv">
        <div class="flex flex-wrap justify-center gap-4">
          <div
            class="flex items-center gap-2 text-xs font-bold text-slate-400 bg-white px-4 py-2 rounded-full border border-slate-100 shadow-sm">
            <i class="fas fa-file-excel text-emerald-500"></i> XLSX / XLS
          </div>
          <div
            class="flex items-center gap-2 text-xs font-bold text-slate-400 bg-white px-4 py-2 rounded-full border border-slate-100 shadow-sm">
            <i class="fas fa-file-csv text-blue-500"></i> CSV
          </div>
        </div>
      </div>
    </div>

    <!-- Step 2: Configure Sheet -->
    <div id="step-2" class="step-content hidden animate-fade-in">
      <div class="card p-8 mb-8">
        <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
          <i class="fas fa-layer-group mr-3 text-brand-400"></i> Select Worksheet
        </h3>
        <div id="sheet-selector" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Dynamic sheet list -->
        </div>
        <div class="mt-8 flex justify-end">
          <button class="btn btn-primary px-8" onclick="validateMapping()">
            Continue to Preview <i class="fas fa-chevron-right ml-2 opacity-70"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Step 3: Validate -->
    <div id="step-3" class="step-content hidden animate-fade-in">
      <div class="card overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
          <div>
            <h3 class="font-bold text-slate-800">Data Validation Preview</h3>
            <p class="text-xs text-slate-500 mt-0.5" id="preview-subtitle">Showing first 10 rows. Errors must be resolved
              before import.</p>
          </div>
          <div class="flex gap-4">
            <div class="text-center">
              <span id="valid-count" class="block text-lg font-bold text-emerald-500">0</span>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Valid</span>
            </div>
            <div class="text-center px-4 border-l border-slate-100">
              <span id="new-count" class="block text-lg font-bold text-brand-400">0</span>
              <span class="text-[10px] font-bold text-slate-400 uppercase">New</span>
            </div>
            <div class="text-center px-4 border-l border-slate-100">
              <span id="update-count" class="block text-lg font-bold text-blue-500">0</span>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Updates</span>
            </div>
            <div class="text-center px-4 border-l border-slate-100">
              <span id="invalid-count" class="block text-lg font-bold text-rose-500">0</span>
              <span class="text-[10px] font-bold text-slate-400 uppercase">Errors</span>
            </div>
          </div>
        </div>
        <div class="overflow-auto max-h-[600px] border-t border-slate-100">
          <table class="w-full text-left" id="preview-table">
            <thead class="bg-slate-50 sticky top-0 z-10">
              <tr class="divide-x divide-slate-100">
                <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase text-center w-20">Row</th>
                <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase text-center w-24">Status</th>
                <!-- Dynamic Headers -->
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <!-- Dynamic Rows -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="flex justify-between items-center">
        <button class="btn btn-white" onclick="changeStep(2)">
          <i class="fas fa-chevron-left mr-2"></i> Adjust Mapping
        </button>
        <button id="import-btn" class="btn btn-primary px-10" onclick="processImport()">
          Confirm & Import <i class="fas fa-check-double ml-2"></i>
        </button>
      </div>
    </div>

    <!-- Step 4: Complete -->
    <div id="step-4" class="step-content hidden animate-fade-in">
      <div class="card p-12 flex flex-col items-center justify-center text-center">
        <div id="completion-icon"
          class="w-20 h-20 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center text-3xl mb-6 animate-bounce">
          <i class="fas fa-check"></i>
        </div>
        <h3 id="completion-title" class="text-2xl font-bold text-slate-800 mb-2">Import Successful!</h3>
        <p id="completion-text" class="text-slate-500 max-w-sm mb-10">We've successfully imported your products into the
          catalog.</p>

        <div class="grid grid-cols-4 gap-4 w-full max-w-2xl mb-10">
          <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
            <span id="imported-total" class="block text-2xl font-black text-slate-800">0</span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Total</span>
          </div>
          <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100">
            <span id="new-total" class="block text-2xl font-black text-emerald-500">0</span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">New Items</span>
          </div>
          <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
            <span id="updated-total" class="block text-2xl font-black text-blue-500">0</span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Updated</span>
          </div>
          <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
            <span id="failed-total" class="block text-2xl font-black text-rose-500">0</span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Failed</span>
          </div>
        </div>

        <div class="flex gap-4">
          <a href="{{ company_route('catalog.products.index') }}" class="btn btn-primary">
            View Products
          </a>
          <button class="btn btn-white" onclick="location.reload()">
            Import Another File
          </button>
        </div>
      </div>
    </div>
  </div>

  @push('styles')
    <style>
      .step-item {
        transition: all 0.5s ease;
        width: 100px;
      }

      .step-circle {
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      }

      .card-mapping {
        @apply flex items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl mb-3 hover:border-brand-400 hover:shadow-soft transition-all;
      }
    </style>
  @endpush

  @push('scripts')
    <script>
      let currentStep = 1;
      let fileData = {
        temp_path: '',
        sheets: [],
        headers: [],
        selectedSheet: 0,
        price_channels: [],
        locations: []
      };
      let mapping = {};
      const coreFields = {
        'brand_id': 'Brand ID',
        'brand_name': 'Brand Name',
        'product_id': 'Product ID',
        'product_name': 'Product Name',
        'variant_sku': 'Variant SKU',
        'cost': 'Cost Price',
        'category_id': 'Category ID',
        'category_name': 'Category Name',
        'description': 'Description',
        'variant_id': 'Variant ID',
        'variant_attributes': 'Variant Attributes'
      };

      function changeStep(step) {
        // Update UI
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        document.getElementById(`step-${step}`).classList.remove('hidden');

        // Update Stepper
        const stepItems = document.querySelectorAll('.step-item');
        stepItems.forEach((item, idx) => {
          const circle = item.querySelector('.step-circle');
          const label = item.querySelector('span');
          const stepNum = idx + 1;

          if (stepNum < step) {
            circle.innerHTML = '<i class="fas fa-check"></i>';
            circle.classList.remove('bg-white', 'text-slate-400', 'border-slate-200', 'bg-brand-400');
            circle.classList.add('bg-emerald-500', 'text-white', 'border-emerald-500');
            label.classList.remove('text-brand-400', 'text-slate-400');
            label.classList.add('text-emerald-500');
          } else if (stepNum === step) {
            circle.innerHTML = stepNum;
            circle.classList.add('bg-brand-400', 'text-white', 'shadow-lg', 'shadow-brand-400/30');
            circle.classList.remove('bg-white', 'text-slate-400', 'border-slate-200', 'bg-emerald-500');
            label.classList.add('text-brand-400');
            label.classList.remove('text-slate-400', 'text-emerald-500');
          } else {
            circle.innerHTML = stepNum;
            circle.classList.add('bg-white', 'text-slate-400', 'border-2', 'border-slate-200');
            circle.classList.remove('bg-brand-400', 'text-white', 'shadow-lg', 'bg-emerald-500', 'border-emerald-500');
            label.classList.add('text-slate-400');
            label.classList.remove('text-brand-400', 'text-emerald-500');
          }
        });

        // Line progress
        const prog = ((step - 1) / (stepItems.length - 1)) * 100;
        document.getElementById('step-line').style.width = `${prog}%`;

        currentStep = step;
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      }

      // File Upload
      document.getElementById('file-input').addEventListener('change', function (e) {
        if (!e.target.files.length) return;

        const formData = new FormData();
        formData.append('file', e.target.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        Swal.fire({
          title: 'Uploading...',
          text: 'Parsing your document structure',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        fetch('{{ company_route('catalog.products.import.upload') }}', {
          method: 'POST',
          body: formData
        })
          .then(res => res.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              fileData.temp_path = data.temp_path;
              fileData.sheets = data.sheets;
              fileData.headers = data.headers;
              fileData.price_channels = data.price_channels || [];
              fileData.locations = data.locations || [];
              renderStep2();
              changeStep(2);
            } else {
              Swal.fire('Error', data.message || 'Upload failed', 'error');
            }
          })
          .catch(err => {
            Swal.close();
            Swal.fire('Error', 'Connection error', 'error');
          });
      });



      function renderStep2() {
        // Render Sheets
        const sheetList = document.getElementById('sheet-selector');
        sheetList.innerHTML = fileData.sheets.map((name, idx) => `
                            <div onclick="selectSheet(${idx})" class="p-4 rounded-2xl border-2 transition-all cursor-pointer flex flex-col items-center justify-center text-center group ${idx === fileData.selectedSheet ? 'border-brand-400 bg-brand-50/50 ring-4 ring-brand-400/10' : 'border-slate-100 hover:border-slate-300 hover:bg-slate-50'} relative">
                                <i class="fas fa-file-excel text-3xl mb-3 ${idx === fileData.selectedSheet ? 'text-emerald-500' : 'text-slate-300 group-hover:text-emerald-400'} transition-colors"></i>
                                <span class="text-sm font-bold text-slate-700">${name}</span>
                                ${idx === fileData.selectedSheet ? '<div class="absolute top-3 right-3"><i class="fas fa-check-circle text-brand-400 text-lg"></i></div>' : ''}
                            </div>
                        `).join('');
      }

      function selectSheet(idx) {
        fileData.selectedSheet = idx;
        renderStep2();
      }

      function selectSheet(idx) {
        fileData.selectedSheet = idx;
        // In a real app, you'd fetch headers for the selected sheet here via AJAX if not already loaded
        renderStep2();
      }

      function validateMapping() {
        Swal.fire({
          title: 'Matching columns...',
          text: 'Identifying SKU, Names, Pricing and Stock',
          didOpen: () => {
            Swal.showLoading();
          }
        });

        fetch('{{ company_route('catalog.products.import.preview') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            temp_path: fileData.temp_path,
            sheet_index: fileData.selectedSheet
          })
        })
          .then(res => res.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              mapping = data.mapping;
              renderPreview(data);
              changeStep(3);
            } else {
              Swal.fire('Error', data.message, 'error');
            }
          });
      }

      function renderPreview(data) {
        const table = document.getElementById('preview-table');
        const thead = table.querySelector('thead tr');
        const tbody = table.querySelector('tbody');

        const mapEntries = Object.entries(mapping);
        const invMapping = {}; // Excel Header -> DB Field
        mapEntries.forEach(([db, excel]) => invMapping[excel] = db);

        const labels = {
          ...coreFields
        };
        fileData.price_channels.forEach(ch => labels[`price_${ch.id}`] = `Price (${ch.code})`);
        fileData.locations.forEach(loc => labels[`stock_${loc.id}`] = `Stock (${loc.name.split(' ')[0]})`);

        // Headers: Row index + Status + All Spreadsheet Headers
        thead.innerHTML = `
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase text-center w-20 bg-slate-50 sticky left-0 z-20">Row</th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase text-center w-24 bg-slate-50 sticky left-20 z-20 border-r border-slate-200">Status</th>
                            ${data.all_headers.map(h => {
          const isMapped = invMapping[h];
          return `
                                            <th class="px-6 py-3 text-[11px] font-bold uppercase whitespace-nowrap ${isMapped ? 'text-brand-600 bg-brand-50/50' : 'text-slate-500 bg-slate-100/30'}">
                                                ${h}
                                                ${isMapped ? `<div class="text-[9px] text-brand-400 font-normal mt-0.5 lowercase">&rarr; ${labels[isMapped] || isMapped}</div>` : ''}
                                            </th>
                                        `;
        }).join('')}
                        `;

        // Rows
        tbody.innerHTML = data.preview.map((row) => {
          const hasErrors = Object.keys(row.errors).length > 0;
          return `
                                <tr class="${hasErrors ? 'bg-rose-50/20' : 'hover:bg-slate-50'} transition-colors divide-x divide-slate-100">
                                    <td class="px-6 py-3 text-center text-xs font-bold text-slate-400 bg-white sticky left-0 z-10 border-r border-slate-100">${row.row_index + 1}</td>
                            <td class="px-6 py-3 text-center bg-white sticky left-20 z-10 border-r border-slate-200">
                                ${hasErrors ?
              `<span class="badge badge-danger cursor-help" title="${Object.values(row.errors).join(', ')}">Error</span>` :
              (row.row_type === 'update' ?
                '<span class="badge badge-primary flex items-center gap-1 justify-center"><i class="fas fa-sync-alt text-[8px]"></i> Update</span>' :
                '<span class="badge badge-success flex items-center gap-1 justify-center"><i class="fas fa-plus text-[8px]"></i> New</span>')
            }
                            </td>
                                    ${data.all_headers.map(h => {
              const dbField = invMapping[h];
              const val = row.raw_data[h] || '';
              const error = dbField ? row.errors[dbField] : null;

              return `
                                                    <td class="px-6 py-3 text-sm ${dbField ? 'bg-brand-50/10' : ''}">
                                                        <div class="${error ? 'text-rose-600 font-bold' : 'text-slate-600'}">
                                                            ${val || '<span class="text-slate-300 italic">-</span>'}
                                                        </div>
                                                        ${error ? `<div class="text-[10px] text-rose-500 font-bold italic mt-0.5">${error}</div>` : ''}
                                                    </td>
                                                `;
            }).join('')}
                                </tr>
                            `;
        }).join('');

        document.getElementById('valid-count').textContent = data.total_rows - data.error_count;
        document.getElementById('new-count').textContent = data.new_count;
        document.getElementById('update-count').textContent = data.update_count;
        document.getElementById('invalid-count').textContent = data.error_count;

        const btn = document.getElementById('import-btn');
        if (data.error_count > 0) {
          btn.disabled = true;
          btn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
          btn.disabled = false;
          btn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
      }

      function processImport() {
        Swal.fire({
          title: 'Importing...',
          html: '<div class="mb-4">Writing products to database</div><div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden"><div id="import-progress" class="bg-brand-400 h-full transition-all duration-300" style="width: 0%"></div></div>',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => {
            // Simulating progress for UX
            let p = 0;
            const clock = setInterval(() => {
              p += 5;
              if (p > 95) clearInterval(clock);
              const el = document.getElementById('import-progress');
              if (el) el.style.width = p + '%';
            }, 100);
          }
        });

        fetch('{{ company_route('catalog.products.import.process') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            temp_path: fileData.temp_path,
            sheet_index: fileData.selectedSheet
          })
        })
          .then(res => res.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              document.getElementById('imported-total').textContent = data.imported;
              document.getElementById('new-total').textContent = data.new;
              document.getElementById('updated-total').textContent = data.updated;
              document.getElementById('failed-total').textContent = data.failed;
              changeStep(4);
            } else {
              Swal.fire('Import Failed', data.message, 'error');
            }
          });
      }
    </script>
  @endpush
@endsection