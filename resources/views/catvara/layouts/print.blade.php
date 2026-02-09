<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title') | Catvara Print</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Nunito', 'sans-serif'],
            mono: ['JetBrains Mono', 'monospace'],
          },
          colors: {
            brand: {
              50: '#fff3e6',
              100: '#ffe6cc',
              200: '#ffcc99',
              300: '#ffb366',
              400: '#ff9f43',
              500: '#e68a33',
              600: '#cc7a29',
              700: '#b36b24',
              800: '#995c1f',
              900: '#804d1a',
            }
          }
        }
      }
    }
  </script>

  <style>
    body {
      background: white;
      -webkit-print-color-adjust: exact;
    }

    .print-container {
      max-width: 210mm;
      margin: 0 auto;
      padding: 20mm;
    }

    @media print {
      .no-print {
        display: none !important;
      }

      body {
        padding: 0;
      }

      .print-container {
        padding: 0;
      }

      @page {
        margin: 1cm;
        /* Hide URL, date and page numbers in print header/footer */
      }

      /* Hide browser's default headers and footers */
      @page :first {
        margin-top: 0;
      }
    }

    /* Force browsers to not show URL in print */
    @media print {
      html {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }
  </style>
</head>

<body class="bg-white font-sans text-slate-900">
  <div class="no-print p-4 bg-slate-100 flex justify-center gap-4 border-b border-slate-200">
    <button onclick="window.print()"
      class="h-10 px-6 rounded-xl bg-brand-600 text-white text-[11px] font-black uppercase tracking-widest hover:bg-brand-700 transition flex items-center gap-2">
      <i class="fas fa-print"></i> Print Document
    </button>
    <button onclick="handleClosePreview()"
      class="h-10 px-6 rounded-xl bg-white border border-slate-200 text-slate-600 text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 transition">
      Close Preview
    </button>
  </div>

  @yield('content')

  <script>
    function handleClosePreview() {
      if (window.history.length > 1) {
        window.history.back();
      } else {
        window.close();
        // Fallback if window.close() is blocked
        setTimeout(() => {
          window.location.href = "{{ url()->previous() }}";
        }, 500);
      }
    }
  </script>

  <script src="https://kit.fontawesome.com/your-kit-id.js" crossorigin="anonymous"></script>
  {{-- Using logic to load fontawesome if not provided --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>

</html>
