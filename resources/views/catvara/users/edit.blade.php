@extends('catvara.layouts.app')

@section('title', 'Edit User')

@section('content')
  <div class="w-full pb-24 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <a href="{{ route('users.index') }}" class="text-slate-400 hover:text-brand-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </a>
          <span
            class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-500 uppercase tracking-widest">Settings</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Edit User</h1>
        <p class="text-slate-500 font-medium mt-1">Modify account details and system access for <span
            class="text-brand-500">{{ $user->name }}</span>.</p>
      </div>
    </div>

    <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        {{-- Left: Profile Photo --}}
        <div class="md:col-span-1 space-y-6">
          <div class="card p-6 flex flex-col items-center">
            <div class="relative group">
              <div
                class="w-32 h-32 rounded-3xl overflow-hidden border-4 border-white shadow-xl bg-slate-100 flex items-center justify-center relative">
                <img id="previewImage"
                  src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : asset('theme/adminlte/dist/img/user2-160x160.jpg') }}"
                  class="w-full h-full object-cover">
                <div
                  class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all cursor-pointer"
                  onclick="document.getElementById('photoInput').click()">
                  <i class="fas fa-camera text-white text-2xl"></i>
                </div>
              </div>
              <input type="file" name="profile_photo" id="photoInput" class="hidden" accept="image/*">
            </div>
            <div class="text-center mt-4">
              <h4 class="font-bold text-slate-800 text-sm">Profile Picture</h4>
              <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-1">Change photo</p>
            </div>
          </div>

          <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100">
            <div class="flex items-start gap-4">
              <div
                class="h-8 w-8 rounded-lg bg-indigo-100 text-indigo-500 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-info-circle"></i>
              </div>
              <div>
                <h4 class="font-bold text-indigo-900 text-sm">Update Password</h4>
                <p class="text-xs text-indigo-800/70 mt-1 leading-relaxed">
                  Leave the password fields <span class="font-black">empty</span> if you do not wish to change the current
                  password.
                </p>
              </div>
            </div>
          </div>
        </div>

        {{-- Right: Details --}}
        <div class="md:col-span-2 space-y-6">
          <div class="card p-8 bg-white">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              {{-- Name --}}
              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Full Name</label>
                <div class="input-icon-group">
                  <i class="fas fa-user"></i>
                  <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full py-2.5 font-semibold" placeholder="e.g. Johnathan Doe">
                </div>
                @error('name')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Email --}}
              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                <div class="input-icon-group">
                  <i class="fas fa-envelope"></i>
                  <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full py-2.5 font-semibold" placeholder="john@example.com">
                </div>
                @error('email')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Password --}}
              <div class="space-y-1.5 md:col-span-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">New Password
                  (Optional)</label>
                <div class="input-icon-group">
                  <i class="fas fa-key"></i>
                  <input type="password" name="password" class="w-full py-2.5 font-semibold"
                    placeholder="Leave empty to keep current password">
                </div>
                @error('password')
                  <p class="text-[10px] font-bold text-rose-500 mt-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- User Type --}}
              <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">System User
                  Type</label>
                <select name="user_type" class="w-full py-2.5 font-semibold">
                  <option value="ADMIN" {{ old('user_type', $user->user_type) === 'ADMIN' ? 'selected' : '' }}>ADMIN
                  </option>
                  <option value="SUPER_ADMIN"
                    {{ old('user_type', $user->user_type) === 'SUPER_ADMIN' ? 'selected' : '' }}>SUPER_ADMIN</option>
                </select>
              </div>

              {{-- Status Toggle Inline --}}
              <div class="space-y-1.5 flex flex-col justify-end">
                <div class="bg-slate-50 border border-slate-100 rounded-xl px-4 py-2 flex items-center justify-between">
                  <span class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Account Active</span>
                  <label class="relative inline-flex items-center cursor-pointer scale-90">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                      {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                    <div
                      class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-4 ring-transparent peer-focus:ring-brand-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500">
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          {{-- Action Buttons --}}
          <div class="flex items-center justify-end gap-3">
            <a href="{{ route('users.index') }}" class="btn btn-white min-w-[120px]">
              Cancel
            </a>
            <button type="submit" class="btn btn-primary min-w-[160px] shadow-lg shadow-brand-500/30">
              <i class="fas fa-save mr-2 text-sm opacity-50"></i> Update Account
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    $(function() {
      $('#photoInput').on('change', function() {
        const file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
          $('#previewImage').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
      });
    });
  </script>
@endpush
