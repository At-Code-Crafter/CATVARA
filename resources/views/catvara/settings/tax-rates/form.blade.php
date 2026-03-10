@extends('catvara.layouts.app')

@section('title', isset($taxRate) ? 'Edit Tax Rate' : 'Create Tax Rate')

@section('content')
    <div class="w-full mx-auto pb-24 animate-fade-in">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <a href="{{ company_route('settings.tax-rates.index') }}"
                        class="text-slate-400 hover:text-brand-600 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <span
                        class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">Settings</span>
                </div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight">
                    {{ isset($taxRate) ? 'Edit Tax Rate' : 'Create Tax Rate' }}
                </h1>
                <p class="text-slate-500 font-medium mt-1">Configure individual tax rate settings</p>
            </div>
        </div>

        <form
            action="{{ isset($taxRate) ? company_route('settings.tax-rates.update', ['tax_rate' => $taxRate->id]) : company_route('settings.tax-rates.store') }}"
            method="POST" class="space-y-8">
            @csrf
            @if (isset($taxRate))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Left Column: Form Fields --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- Basic Details --}}
                    <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-1 h-full bg-blue-400"></div>
                        <div class="flex items-center gap-4 mb-8">
                            <div
                                class="h-10 w-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shadow-sm">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-800 tracking-tight">Rate Details</h3>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Basic Information</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Rate Code
                                    <span class="text-rose-500">*</span></label>
                                <div class="input-icon-group">
                                    <i class="fas fa-fingerprint"></i>
                                    <input type="text" name="code" value="{{ old('code', $taxRate->code ?? '') }}"
                                        required class="w-full py-2.5 font-semibold uppercase" placeholder="e.g. CGST_9">
                                </div>
                                @error('code')
                                    <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Rate Name
                                    <span class="text-rose-500">*</span></label>
                                <div class="input-icon-group">
                                    <i class="fas fa-tag"></i>
                                    <input type="text" name="name" value="{{ old('name', $taxRate->name ?? '') }}"
                                        required class="w-full py-2.5 font-semibold" placeholder="e.g. CGST 9%">
                                </div>
                                @error('name')
                                    <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tax Group
                                    <span class="text-rose-500">*</span></label>
                                <select name="tax_group_id" required
                                    class="w-full py-2.5 px-4 font-semibold border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-400 focus:border-brand-400 transition-all">
                                    <option value="">Select Tax Group</option>
                                    @foreach ($taxGroups as $group)
                                        <option value="{{ $group->id }}"
                                            {{ old('tax_group_id', $taxRate->tax_group_id ?? '') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }} ({{ $group->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('tax_group_id')
                                    <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Rate (%)
                                    <span class="text-rose-500">*</span></label>
                                <div class="input-icon-group">
                                    <i class="fas fa-percent"></i>
                                    <input type="number" name="rate" step="0.0001" min="0" max="100"
                                        value="{{ old('rate', $taxRate->rate ?? '') }}" required
                                        class="w-full py-2.5 font-semibold" placeholder="e.g. 9.0000">
                                </div>
                                @error('rate')
                                    <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label
                                    class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Priority</label>
                                <div class="input-icon-group">
                                    <i class="fas fa-sort-numeric-up"></i>
                                    <input type="number" name="priority" min="0"
                                        value="{{ old('priority', $taxRate->priority ?? 0) }}"
                                        class="w-full py-2.5 font-semibold" placeholder="0">
                                </div>
                                <p class="text-[10px] text-slate-400 ml-1">Lower numbers are applied first. Compound taxes
                                    should have higher priority.</p>
                                @error('priority')
                                    <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Region --}}
                    <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-1 h-full bg-violet-400"></div>
                        <div class="flex items-center gap-4 mb-8">
                            <div
                                class="h-10 w-10 rounded-xl bg-violet-50 text-violet-500 flex items-center justify-center shadow-sm">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-800 tracking-tight">Region</h3>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Geographic
                                    Applicability (Optional)</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1.5">
                                <label
                                    class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Country</label>
                                <select name="country_id" id="countrySelect"
                                    class="w-full py-2.5 px-4 font-semibold border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-400 focus:border-brand-400 transition-all">
                                    <option value="">All Countries</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                            {{ old('country_id', $taxRate->country_id ?? '') == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                    <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label
                                    class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">State</label>
                                <select name="state_id" id="stateSelect"
                                    class="w-full py-2.5 px-4 font-semibold border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-400 focus:border-brand-400 transition-all">
                                    <option value="">All States</option>
                                    @if (isset($states))
                                        @foreach ($states as $state)
                                            <option value="{{ $state->id }}"
                                                {{ old('state_id', $taxRate->state_id ?? '') == $state->id ? 'selected' : '' }}>
                                                {{ $state->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('state_id')
                                    <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Settings --}}
                    <div class="card p-8 bg-white border-slate-100 shadow-soft relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-1 h-full bg-emerald-400"></div>
                        <div class="flex items-center gap-4 mb-8">
                            <div
                                class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shadow-sm">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-800 tracking-tight">Settings</h3>
                                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Configuration Options
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label
                                class="flex items-center gap-3 cursor-pointer p-4 rounded-xl border border-slate-100 hover:bg-slate-50">
                                <input type="checkbox" name="is_compound" value="1"
                                    {{ old('is_compound', $taxRate->is_compound ?? false) ? 'checked' : '' }}
                                    class="h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:bg-amber-500 checked:border-amber-500 relative">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700">Compound Tax</span>
                                    <span class="text-[10px] text-slate-400 font-medium">This tax is calculated on the
                                        subtotal + other non-compound taxes</span>
                                </div>
                            </label>

                            <label
                                class="flex items-center gap-3 cursor-pointer p-4 rounded-xl border border-slate-100 hover:bg-slate-50">
                                <input type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', $taxRate->is_active ?? true) ? 'checked' : '' }}
                                    class="h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:bg-blue-500 checked:border-blue-500 relative">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700">Active Rate</span>
                                    <span class="text-[10px] text-slate-400 font-medium">Enable this rate for tax
                                        calculations</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Actions --}}
                <div class="space-y-8">
                    <div class="card p-8 bg-slate-900 border-none shadow-2xl shadow-slate-900/40">
                        <h3 class="text-white font-black text-lg mb-6 flex items-center gap-2">
                            <i class="fas fa-save text-brand-400"></i> Save Rate
                        </h3>
                        <div class="space-y-6">
                            <button type="submit"
                                class="w-full btn btn-primary py-4 shadow-xl shadow-brand-500/20 font-black tracking-tight flex items-center justify-center gap-3">
                                <i class="fas fa-check opacity-50"></i>
                                {{ isset($taxRate) ? 'Update Rate' : 'Create Rate' }}
                            </button>
                            <a href="{{ company_route('settings.tax-rates.index') }}"
                                class="w-full flex items-center justify-center py-2 text-[10px] font-black text-slate-500 hover:text-rose-400 transition-colors uppercase tracking-widest">
                                Cancel
                            </a>
                        </div>
                    </div>

                    @if (isset($taxRate))
                        <div class="card p-6 bg-white border-slate-100 shadow-soft">
                            <h3
                                class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i class="fas fa-info-circle text-blue-400"></i> Rate Info
                            </h3>
                            <div class="space-y-3 text-xs">
                                <div class="flex justify-between items-center py-2 border-b border-slate-50">
                                    <span class="text-slate-400 font-bold">Created</span>
                                    <span
                                        class="text-slate-600 font-bold">{{ $taxRate->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-slate-50">
                                    <span class="text-slate-400 font-bold">Updated</span>
                                    <span
                                        class="text-slate-600 font-bold">{{ $taxRate->updated_at->format('M d, Y') }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-slate-400 font-bold">Group</span>
                                    <span class="text-slate-600 font-bold">{{ $taxRate->group->name ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            // Country → State cascade
            $('#countrySelect').on('change', function() {
                const countryId = $(this).val();
                const $stateSelect = $('#stateSelect');

                $stateSelect.html('<option value="">All States</option>');

                if (!countryId) return;

                $.get('/ajax/countries/' + countryId + '/states', function(states) {
                    states.forEach(function(state) {
                        $stateSelect.append(
                            $('<option>', {
                                value: state.id,
                                text: state.name
                            })
                        );
                    });
                });
            });
        });
    </script>
@endpush
