<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>CATVARA - Reset Password</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'ui-sans-serif', 'system-ui', 'Segoe UI', 'Roboto', 'Helvetica', 'Arial'],
          },
          colors: {
            ink: {
              950: '#0B1220',
              900: '#0F172A'
            }
          }
        }
      }
    }
  </script>
</head>

<body class="min-h-screen bg-white text-slate-900 antialiased">
  <div class="relative min-h-screen overflow-hidden">
    <!-- Background (same as welcome/login) -->
    <div class="absolute inset-0 bg-gradient-to-b from-slate-50 via-white to-slate-50"></div>
    <div class="absolute -top-40 -left-40 h-[520px] w-[520px] rounded-full bg-emerald-200/40 blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 h-[520px] w-[520px] rounded-full bg-sky-200/40 blur-3xl"></div>

    <div class="relative mx-auto flex min-h-screen max-w-6xl items-center px-6 py-14">
      <div class="w-full">
        <!-- Header -->
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-ink-950 text-white shadow-sm">
            <span class="text-sm font-semibold tracking-widest">CV</span>
          </div>
          <div>
            <div class="text-sm font-semibold tracking-wide text-slate-900">CATVARA</div>
            <div class="text-xs text-slate-500">Catalog & Inventory Core</div>
          </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-10 lg:grid-cols-12 lg:items-center">
          <!-- Left: Copy -->
          <div class="lg:col-span-6">
            <h1 class="text-4xl font-semibold tracking-tight text-slate-900 md:text-5xl">
              Reset your password
              <span class="text-slate-500">securely.</span>
            </h1>

            <p class="mt-5 text-base leading-relaxed text-slate-600 md:text-lg">
              Forgot your password? No problem. Enter your email address and we’ll send a reset link so you can choose a
              new one.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3 text-xs text-slate-500">
              <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/70 px-3 py-1">
                Secure email link
              </span>
              <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/70 px-3 py-1">
                Fast recovery
              </span>
              <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/70 px-3 py-1">
                Authorized access only
              </span>
            </div>
          </div>

          <!-- Right: Reset Card -->
          <div class="lg:col-span-6">
            <div class="mx-auto w-full max-w-md">
              <div class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur md:p-8">
                <div class="mb-6">
                  <h2 class="text-lg font-semibold text-slate-900">Forgot Password</h2>
                  <p class="mt-1 text-sm text-slate-600">We will email you a password reset link.</p>
                  @if (session('status'))
                    <div class="mb-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700 border border-emerald-100">
                      {{ session('status') }}
                    </div>
                  @endif

                  @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700 border border-red-100">
                      <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif

                  <form method="POST" action="/forgot-password" class="space-y-5">
                    @csrf
                    <!-- Email -->
                    <div>
                      <label for="email" class="block text-sm font-medium text-slate-900">Email</label>
                      <div class="mt-2">
                        <input id="email" name="email" type="email" required autofocus
                          value="{{ old('email') }}" placeholder="you@company.com"
                          class="block w-full rounded-xl border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-200' }} bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none
                                placeholder:text-slate-400 focus:border-slate-400 focus:ring-4 focus:ring-slate-100" />
                      </div>
                      @error('email')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                      @enderror
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                      class="inline-flex w-full items-center justify-center rounded-xl bg-ink-950 px-4 py-3 text-sm font-semibold text-white shadow-sm
                           transition hover:bg-ink-900 focus:outline-none focus:ring-4 focus:ring-slate-200">
                      Email Password Reset Link
                    </button>

                    <div class="pt-2 text-center text-xs text-slate-500">
                      <a href="/login" class="font-medium text-slate-600 hover:text-slate-900">Back to Login</a>
                      <span class="mx-2 text-slate-300">•</span>
                      © <span id="year"></span> CATVARA
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div> <!-- /grid -->
        </div>
      </div>
    </div>

    <script>
      document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>

</html>
