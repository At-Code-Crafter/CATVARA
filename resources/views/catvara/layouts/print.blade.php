<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title') | Catvara Print</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

  <style>
    /* 1. Global Reset & Print Fixes */
    html, body {
      margin: 0;
      padding: 0;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
      background-color: #f0f0f0;
      font-size: 12px;
    }

    p {
      margin: 0;
    }

    /* 2. Main Container (A4 Settings) */
    .invoice-container,
    .print-container {
      width: 210mm;
      margin: 0 auto;
      background: white;
      box-sizing: border-box;
      position: relative;
      display: block;
      color: #333;
      padding: 2mm 0 20mm 0;
    }

    .print-container {
      padding: 15mm;
    }

    /* 3. Header Section */
    .header-top {
      display: none; /* Hidden - rendered by html2pdf.js on every page */
      justify-content: space-between;
      align-items: flex-start;
      padding: 0 10mm;
      margin-bottom: 20px;
    }

    .invoice-title {
      font-size: 32px;
      font-weight: bold;
      color: #333;
      flex: 1;
    }

    .label {
      color: #000;
      font-weight: bold;
      margin-bottom: 3px;
    }

    .value {
      font-size: 12px;
      font-weight: 500;
    }

    /* 4. Brand Row (Gray Box Area) */
    .brand-row {
      display: grid;
      grid-template-columns: 280px 1fr 1fr;
      border-top: 1px solid #e0e0e0;
      border-bottom: 1px solid #e0e0e0;
      margin-bottom: 35px;
    }

    .brand-block,
    .dates-block,
    .additional-details-block {
      padding: 15px;
    }

    .brand-block {
      padding-left: 10mm;
    }

    .brand-block img {
      width: 140px;
    }

    .dates-block {
      border-left: 0.5px solid #e0e0e0;
      border-right: 0.5px solid #e0e0e0;
    }

    .dates-block .date-item {
      margin-bottom: 10px;
    }

    .additional-details-block {
      background-color: #f8f8f8 !important;
      padding-right: 10mm;
    }

    /* 5. Addresses & Table */
    .address-grid {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 5px;
      margin-bottom: 40px;
      line-height: 1.5;
      padding: 0 15mm;
    }

    .table-grid {
      padding: 0 15mm;
    }

    .items-table {
      width: 100%;
      border-collapse: collapse;
    }

    .items-table th {
      border-bottom: 1px solid #d9d9d9;
      padding: 8px;
      font-weight: bold;
      font-size: 12px;
      color: #444;
      text-align: left;
    }

    .items-table tr {
      page-break-inside: avoid !important;
      break-inside: avoid !important;
    }

    .items-table td {
      padding: 8px 18px 8px 8px;
      border-bottom: 1px solid #d9d9d9;
      vertical-align: top;
    }

    .discount {
      font-style: italic;
      font-weight: bold;
      margin-top: 2px;
      display: block;
    }

    /* 6. Totals & Alignment */
    .text-right {
      text-align: right;
    }

    .text-left {
      text-align: left;
    }

    .totals-container {
      display: flex;
      justify-content: flex-end;
      margin-top: 30px;
      padding: 0 15mm;
      page-break-inside: avoid;
    }

    .totals-box {
      width: 260px;
    }

    .total-line {
      display: flex;
      justify-content: space-between;
      padding: 6px 0;
      border-bottom: 0.5px solid #d9d9d9;
    }

    .grand-total {
      border-top: 1.5px solid #333;
      border-bottom: 1.5px solid #333;
      font-weight: bold;
      margin-top: 10px;
      padding: 10px 0;
    }

    /* 7. Bank Details */
    .bank-details {
      padding: 0 15mm;
      margin-top: 30px;
      page-break-inside: avoid;
    }

    /* 8. Footer - hidden, rendered by JS */
    .footer {
      display: none;
    }

    /* 9. Toolbar */
    .no-print {
      background: #f1f5f9;
      padding: 16px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      border-bottom: 1px solid #e2e8f0;
    }

    .no-print .btn-row {
      display: flex;
      justify-content: center;
      gap: 16px;
    }

    .no-print .btn {
      height: 40px;
      padding: 0 24px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      cursor: pointer;
      border: none;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background 0.2s;
    }

    .no-print .btn-primary {
      background: #ff9f43;
      color: white;
    }

    .no-print .btn-primary:hover {
      background: #e68a33;
    }

    .no-print .btn-secondary {
      background: white;
      color: #64748b;
      border: 1px solid #e2e8f0;
    }

    .no-print .btn-secondary:hover {
      background: #f8fafc;
    }

    .no-print .tip {
      font-size: 10px;
      color: #64748b;
    }

    /* 10. Print Settings */
    @media print {
      @page {
        margin: 0;
        size: A4;
      }
      body {
        background: none;
      }
      .invoice-container {
        margin: 0;
        box-shadow: none;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>

<body>
  <div class="no-print">
    <div class="btn-row">
      <button onclick="generatePDF()" class="btn btn-primary">
        <i class="fas fa-download"></i> Download PDF
      </button>
      <button onclick="printPDF()" class="btn btn-secondary">
        <i class="fas fa-print"></i> Print
      </button>
      <button onclick="handleClosePreview()" class="btn btn-secondary">
        Close Preview
      </button>
    </div>
    <p class="tip">
      <strong>Tip:</strong> Use "Download PDF" for best results with repeating headers, footers, and page numbers.
    </p>
  </div>

  @yield('content')

  <script>
    function handleClosePreview() {
      if (window.history.length > 1) {
        window.history.back();
      } else {
        window.close();
        setTimeout(() => {
          window.location.href = "{{ url()->previous() }}";
        }, 500);
      }
    }

    // Default generatePDF — can be overridden by child templates
    if (typeof generatePDF === 'undefined') {
      function generatePDF(mode) {
        mode = mode || 'save';
        const el = document.querySelector('.invoice-container') || document.querySelector('.print-container');
        if (!el) { alert('Nothing to export'); return; }

        const title = document.title.split('|')[0].trim().replace(/\s+/g, '_');

        const worker = html2pdf().set({
          margin: [10, 0, 10, 0],
          filename: title + '.pdf',
          image: { type: 'jpeg', quality: 1.0 },
          html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
          jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        }).from(el);

        if (mode === 'print') {
          worker.toPdf().get('pdf').then(function(pdf) {
            const blob = pdf.output('blob');
            const url = URL.createObjectURL(blob);
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = url;
            document.body.appendChild(iframe);
            iframe.onload = function() {
              iframe.contentWindow.print();
            };
          });
        } else {
          worker.save();
        }
      }
    }

    // Print uses the same PDF pipeline
    function printPDF() {
      generatePDF('print');
    }
  </script>

  @yield('scripts')

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>

</html>
