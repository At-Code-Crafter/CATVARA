@extends('catvara.layouts.app')

@section('title', isset($category) ? 'Edit Category' : 'Create Category')

@section('content')
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                {{ isset($category) ? 'Edit Category' : 'Create Category' }}
            </h1>
            <p class="text-slate-500 mt-1">
                {{ isset($category) ? 'Update category details and hierarchy.' : 'Add a new category to your catalog.' }}
            </p>
        </div>
        <a href="{{ company_route('catalog.categories.index') }}"
            class="text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <form
        action="{{ isset($category) ? company_route('catalog.categories.update', ['category' => $category->id]) : company_route('catalog.categories.store') }}"
        method="POST">
        @csrf
        @if(isset($category))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Side: Main Info --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Basic Information --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Basic Information</h3>
                    </div>
                    <div class="p-8 space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Category Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $category->name ?? '') }}"
                                class="w-full rounded-xl focus:border-brand-500 focus:ring-brand-500 shadow-sm placeholder-slate-400 text-sm py-2.5 transition-all"
                                placeholder="e.g. Electronics, Men's Wear" required>
                            @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <!-- Parent Category -->
                        <div>
                            <label for="parent_id" class="block text-sm font-semibold text-slate-700 mb-2">Parent
                                Category</label>
                            <select name="parent_id" id="parent_id" class="select2 w-full"
                                data-placeholder="Select a parent category (Optional)">
                                <option value="">None (Root Category)</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (old('parent_id', $category->parent_id ?? '') == $cat->id) ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                    @foreach($cat->children as $child)
                                        <option value="{{ $child->id }}" {{ (old('parent_id', $category->parent_id ?? '') == $child->id) ? 'selected' : '' }}>
                                            &nbsp;&nbsp;— {{ $child->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-slate-500">Nest this category under another one to create a
                                hierarchy.</p>
                            @error('parent_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Attributes --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Related Attributes</h3>
                    </div>
                    <div class="p-8">
                        <label for="attributes" class="block text-sm font-semibold text-slate-700 mb-2">Attributes</label>
                        <select name="attributes[]" id="attributes" class="select2 w-full" multiple
                            data-placeholder="Select attributes for this category">
                            @foreach($attributes as $attr)
                                <option value="{{ $attr->id }}" {{ (collect(old('attributes', isset($category) ? $category->attributes->pluck('id') : []))->contains($attr->id)) ? 'selected' : '' }}>
                                    {{ $attr->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Products in this category will inherit these attributes for
                            variant creation.</p>
                    </div>
                </div>
            </div>

            {{-- Right Side: Options & Actions --}}
            <div class="space-y-8">
                {{-- Actions --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 p-6">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Actions</h3>

                    <div class="space-y-4">
                        <button type="submit"
                            class="w-full px-5 py-3 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-lg shadow-brand-500/30 transition-all focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                            <i class="fas fa-save mr-2"></i>
                            {{ isset($category) ? 'Update Category' : 'Create Category' }}
                        </button>
                        <a href="{{ company_route('catalog.categories.index') }}"
                            class="w-full flex items-center justify-center px-5 py-3 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors shadow-sm">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Status Card --}}
                <div class="bg-white rounded-2xl shadow-soft border border-slate-100 p-6">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Status</h3>
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Active</label>
                            <p class="text-[10px] text-slate-500 uppercase tracking-tight">Public visibility</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                            <div
                                class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600">
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection