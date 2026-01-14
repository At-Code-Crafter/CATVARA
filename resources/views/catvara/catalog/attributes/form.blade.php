@extends('catvara.layouts.app')

@section('title', isset($attribute) ? 'Edit Attribute' : 'Create Attribute')

@section('content')
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                {{ isset($attribute) ? 'Edit Attribute' : 'Create Attribute' }}
            </h1>
            <p class="text-slate-500 mt-1">
                {{ isset($attribute) ? 'Update attribute definition and manage values.' : 'Define a new product attribute and its initial values.' }}
            </p>
        </div>
        <a href="{{ company_route('catalog.attributes.index') }}"
            class="text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <form
        action="{{ isset($attribute) ? company_route('catalog.attributes.update', ['attribute' => $attribute->id]) : company_route('catalog.attributes.store') }}"
        method="POST">
        @csrf
        @if(isset($attribute))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left: Main Info --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Attribute Definition --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Attribute Definition</h3>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Display Name
                                    <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $attribute->name ?? '') }}"
                                    class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                                    placeholder="e.g. Color, Size" required>
                                @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="code" class="block text-sm font-semibold text-slate-700 mb-2">Attribute Code
                                    <span class="text-red-500">*</span></label>
                                <input type="text" name="code" id="code" value="{{ old('code', $attribute->code ?? '') }}"
                                    class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all bg-slate-50"
                                    placeholder="e.g. color" required {{ isset($attribute) ? 'readonly' : '' }}>
                                @error('code') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                <p class="mt-2 text-xs text-slate-500">Lowercase, no spaces. Used for filters and API.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Attribute Values --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Attribute Values</h3>
                    </div>
                    <div class="p-8 space-y-6">
                        @if(isset($attribute))
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                                Value</th>
                                            <th
                                                class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-center">
                                                Active</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($attribute->values as $val)
                                            <tr>
                                                <td class="px-4 py-3 border-b border-slate-50">
                                                    <span class="text-sm font-medium text-slate-700">{{ $val->value }}</span>
                                                </td>
                                                <td class="px-4 py-3 border-b border-slate-50 text-center">
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" name="existing_values[{{ $val->id }}][is_active]"
                                                            value="1" class="sr-only peer" {{ $val->is_active ? 'checked' : '' }}>
                                                        <div
                                                            class="w-9 h-5 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600">
                                                        </div>
                                                    </label>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="pt-4">
                                <label for="new_values" class="block text-sm font-semibold text-slate-700 mb-2">Add New
                                    Values</label>
                                <textarea name="new_values" id="new_values" rows="2"
                                    class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                                    placeholder="Enter values separated by comma (e.g. Yellow, Purple)">{{ old('new_values') }}</textarea>
                            </div>
                        @else
                            <div>
                                <label for="values" class="block text-sm font-semibold text-slate-700 mb-2">Values <span
                                        class="text-red-500">*</span></label>
                                <textarea name="values" id="values" rows="3"
                                    class="w-full rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                                    placeholder="Enter values separated by comma (e.g. Red, Blue, Green)"
                                    required>{{ old('values') }}</textarea>
                                @error('values') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                <p class="mt-2 text-xs text-slate-500">Initial values to seed this attribute. You can add
                                    more later.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Sidebar --}}
            <div class="space-y-8">
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 p-6">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Actions</h3>

                    <div class="space-y-4">
                        <button type="submit"
                            class="w-full px-5 py-3 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-lg shadow-brand-500/30 transition-all focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                            <i class="fas fa-save mr-2"></i>
                            {{ isset($attribute) ? 'Update Attribute' : 'Save Attribute' }}
                        </button>
                        <a href="{{ company_route('catalog.attributes.index') }}"
                            class="w-full flex items-center justify-center px-5 py-3 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors shadow-sm">
                            Cancel
                        </a>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-50">
                        <p class="text-xs text-slate-500 leading-relaxed">
                            <i class="fas fa-info-circle mr-1 text-brand-500"></i>
                            Attributes help customers filter products and allow you to create product variants (like
                            Size and Color combinations).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection