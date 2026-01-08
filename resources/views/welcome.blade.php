<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>CATVARA - Catalog & Inventory Core</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'ui-sans-serif', 'system-ui', 'Segoe UI', 'Roboto', 'Helvetica', 'Arial'],
            serif: ['ui-serif', 'Georgia']
          },
          colors: {
            // Shopify-like clean palette
            ink: {
              950: '#0B1220',
              900: '#0F172A',
              800: '#111C33'
            }
          }
        }
      }
    }
  </script>

  <style>
    /* Optional: smoother rendering */
    html, body { height: 100%; }
  </style>
</head>

<body class="min-h-screen bg-white text-slate-900 antialiased">
  <!-- Background -->
  <div class="relative min-h-screen overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-slate-50 via-white to-slate-50"></div>
    <div class="absolute -top-40 -left-40 h-[520px] w-[520px] rounded-full bg-emerald-200/40 blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 h-[520px] w-[520px] rounded-full bg-sky-200/40 blur-3xl"></div>

    <!-- Content -->
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

        <!-- Hero -->
        <div class="mt-10 max-w-3xl">
          <h1 class="text-4xl font-semibold tracking-tight text-slate-900 md:text-5xl">
            Modern, modular catalog and inventory core
            <span class="text-slate-500">built for variant-driven products.</span>
          </h1>

          <p class="mt-5 text-base leading-relaxed text-slate-600 md:text-lg">
            CATVARA provides a clean foundation for managing catalogs, product variants, warehouses,
            stock availability, and related assets using a scalable and extensible architecture.
          </p>

          <!-- Single CTA -->
          <div class="mt-10">
            <a
              href="/login"
              class="inline-flex items-center justify-center rounded-xl bg-ink-950 px-6 py-3 text-sm font-semibold text-white shadow-sm transition
                     hover:bg-ink-900 focus:outline-none focus:ring-4 focus:ring-slate-200"
            >
              Login
            </a>
          </div>
        </div>

        <!-- Footer -->
        <div class="mt-14 text-xs text-slate-500">
          © <span id="year"></span> CATVARA. All rights reserved.
        </div>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
