@extends('catvara.layouts.app')

@section('title', 'Stores')

@section('content')
  <div class="max-w-7xl mx-auto">
    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
      <div>
        <nav class="flex mb-1" aria-label="Breadcrumb">
          <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm">
            <li class="inline-flex items-center">
              <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-brand-600 transition-colors">
                <i class="fas fa-home mr-2"></i> Dashboard
              </a>
            </li>
            <li><i class="fas fa-chevron-right text-slate-300 text-xs"></i></li>
            <li>
              <a href="#" class="text-slate-500 hover:text-brand-600 transition-colors">Inventory</a>
            </li>
            <li><i class="fas fa-chevron-right text-slate-300 text-xs"></i></li>
            <li aria-current="page">
              <span class="font-medium text-slate-400">Stores</span>
            </li>
          </ol>
        </nav>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Stores</h1>
        <p class="text-sm text-slate-500 font-medium mt-1">Manage your physical retail locations and outlets.</p>
      </div>

      <a href="{{ company_route('inventory.stores.create') }}" class="btn btn-primary shadow-lg shadow-brand-500/30">
        <i class="fas fa-plus mr-2"></i> Add Store
      </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="p-6">
        <table class="w-full text-left" id="storesTable">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Store</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Code</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Address</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Phone</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            @foreach ($stores as $store)
              <tr class="hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="font-bold text-slate-800">{{ $store->name }}</div>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-md uppercase tracking-wider border border-slate-200">{{ $store->code }}</span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-slate-500 max-w-xs truncate font-medium">{{ $store->address ?? 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500 font-medium">
                  {{ $store->phone ?? '-' }}
                </td>
                <td class="px-6 py-4">
                  @if ($store->is_active)
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                      <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span> Active
                    </span>
                  @else
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">
                      Inactive
                    </span>
                  @endif
                </td>
                <td class="px-6 py-4 text-right space-x-1">
                  <a href="{{ company_route('inventory.stores.edit', ['store' => $store->id]) }}"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:bg-brand-50 hover:text-brand-600 transition-all">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ company_route('inventory.stores.destroy', ['store' => $store->id]) }}" method="POST"
                    class="inline-block" onsubmit="return confirm('Are you sure you want to delete this store?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(document).ready(function() {
      $('#storesTable').DataTable({
        responsive: true,
        language: {
          search: "",
          searchPlaceholder: "Search stores...",
          processing: '<div class="flex items-center justify-center"><i class="fas fa-circle-notch fa-spin text-brand-600 text-xl"></i></div>',
          paginate: {
            previous: '<i class="fas fa-chevron-left"></i>',
            next: '<i class="fas fa-chevron-right"></i>'
          }
        },
        dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
        drawCallback: function() {
          $('.dataTables_paginate > .pagination').addClass('flex items-center gap-1');
          $('.dataTables_paginate .paginate_button').addClass(
            'px-3 py-1 text-sm font-medium rounded-lg hover:bg-slate-100 text-slate-600 transition-colors');
          $('.dataTables_paginate .paginate_button.current').addClass(
            'bg-brand-50 text-brand-600 font-bold border border-brand-100 hover:bg-brand-100');
        }
      });
    });
  </script>
@endpush
