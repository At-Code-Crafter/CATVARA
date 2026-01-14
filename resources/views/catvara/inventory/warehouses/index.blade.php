@extends('catvara.layouts.app')

@section('title', 'Warehouses')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Warehouses</h1>
            <p class="text-slate-500 mt-1">Manage your storage facilities and distribution centers.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ company_route('inventory.warehouses.create') }}"
                class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-brand-600 rounded-xl hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-lg shadow-brand-500/30 transition-all">
                <i class="fas fa-plus mr-2"></i> Add Warehouse
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
        <div class="p-6">
            <table class="w-full text-left" id="warehousesTable">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Warehouse</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Code</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Location</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Phone</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-widest text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($warehouses as $wh)
                        <tr class="transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $wh->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded uppercase tracking-wider">{{ $wh->code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-500 max-w-xs truncate">{{ $wh->address ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ $wh->phone ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($wh->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 text-slate-500 border border-slate-100">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ company_route('inventory.warehouses.edit', ['warehouse' => $wh->id]) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:bg-brand-50 hover:text-brand-600 transition-all border border-slate-200">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <form action="{{ company_route('inventory.warehouses.destroy', ['warehouse' => $wh->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this warehouse?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all border border-slate-200">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#warehousesTable').DataTable({
                responsive: true,
                language: {
                    search: "",
                    searchPlaceholder: "Search warehouses...",
                    processing: '<div class="flex items-center justify-center"><i class="fas fa-circle-notch fa-spin text-brand-600 text-xl"></i></div>'
                },
                dom: '<"flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-6 gap-4"ip>',
            });
        });
    </script>
@endpush
