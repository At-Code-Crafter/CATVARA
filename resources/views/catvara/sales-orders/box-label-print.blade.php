@extends('catvara.layouts.print')

@section('title', 'Box ' . $boxNumber . ' — ' . $order->order_number)

@php
    $company = $order->company;
@endphp

@section('content')
    <div class="print-container" id="label-content" style="max-width: 210mm; margin: 0 auto; padding: 20mm 15mm;">

        {{-- Company Logo / Name --}}
        <div style="text-align: center; margin-bottom: 8px;">
            @if ($company->logo)
                <img src="{{ storage_url($company->logo) }}" alt="{{ $company->name }}"
                    style="max-height: 70px; margin-bottom: 6px;">
            @else
                <div style="font-size: 32px; font-weight: 900; color: #333; text-transform: uppercase;">
                    {{ $company->name }}
                </div>
            @endif
        </div>

        {{-- Legal Name --}}
        @if ($company->legal_name)
            <div
                style="text-align: center; font-size: 12px; font-weight: 600; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                {{ $company->legal_name }}
            </div>
        @endif

        {{-- Company Address --}}
        @if ($company->detail?->address)
            <div style="text-align: center; font-size: 11px; color: #888; line-height: 1.5; margin-bottom: 40px;">
                {{ $company->detail->address }}
            </div>
        @else
            <div style="margin-bottom: 40px;"></div>
        @endif

        {{-- Order Number --}}
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="font-size: 48px; font-weight: 900; color: #222; letter-spacing: 1px;">
                {{ $order->order_number }}
            </div>
        </div>

        {{-- Box Number --}}
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="font-size: 20px; font-weight: 800; color: #333; letter-spacing: 2px;">
                BOX &ndash; {{ $boxNumber }}
            </div>
        </div>

        {{-- Items Table --}}
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #999;">
            <thead>
                <tr>
                    <th
                        style="padding: 8px 12px; text-align: left; font-weight: 800; font-size: 11px; text-transform: uppercase; color: #333; border: 1px solid #999;">
                        Description
                    </th>
                    <th
                        style="padding: 8px 12px; text-align: center; font-weight: 800; font-size: 11px; text-transform: uppercase; color: #333; border: 1px solid #999; width: 80px;">
                        Qty
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($boxItems as $bi)
                    @php
                        $item = $bi->orderItem;
                        $desc = $item->product_name;
                        if ($item->variant_description) {
                            $desc .= ' — ' . $item->variant_description;
                        }
                    @endphp
                    <tr>
                        <td
                            style="padding: 10px 12px; font-weight: 600; font-size: 13px; color: #333; border: 1px solid #999;">
                            {{ $desc }}
                            @if ($item->productVariant?->sku)
                                <br><span style="font-size: 11px; color: #888;">[{{ $item->productVariant->sku }}]</span>
                            @endif
                        </td>
                        <td
                            style="padding: 10px 12px; text-align: center; font-weight: 800; font-size: 15px; color: #333; border: 1px solid #999;">
                            {{ (float) $bi->quantity }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection

@section('scripts')
    <script>
        function generatePDF(mode) {
            mode = mode || 'save';
            const el = document.getElementById('label-content');
            if (!el) {
                alert('Nothing to export');
                return;
            }

            const filename = 'Label_Box{{ $boxNumber }}_{{ $order->order_number }}.pdf';
            const worker = html2pdf().set({
                margin: [10, 0, 10, 0],
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1.0
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    scrollY: 0
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            }).from(el);

            if (mode === 'print') {
                worker.toPdf().get('pdf').then(function(pdf) {
                    const blob = pdf.output('blob');
                    const url = URL.createObjectURL(blob);
                    window.open(url, '_self');
                });
            } else {
                worker.save();
            }
        }
    </script>
@endsection
