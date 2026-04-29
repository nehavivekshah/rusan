@extends('layout')
@section('title', 'Preview Invoice # INV-' . ($invoice->invoice_number ?? ''))

@section('content')

    @php
        $company = session('companies');

        function amountToWords($amount, string $locale = 'en_IN'): string
        {
            // normalise
            $amount = (float) str_replace([',', ' '], '', $amount);
            $rupees = (int) $amount;
            $paise = (int) round(($amount - $rupees) * 100);

            if (class_exists('NumberFormatter')) {
                $fmt = new NumberFormatter($locale, NumberFormatter::SPELLOUT);
                $words = ucfirst($fmt->format($rupees)) . ' rupees';
                if ($paise) {
                    $words .= ' and ' . $fmt->format($paise) . ' paise';
                }
                return $words . ' only';
            }

            $units = [
                '',
                'one',
                'two',
                'three',
                'four',
                'five',
                'six',
                'seven',
                'eight',
                'nine',
                'ten',
                'eleven',
                'twelve',
                'thirteen',
                'fourteen',
                'fifteen',
                'sixteen',
                'seventeen',
                'eighteen',
                'nineteen'
            ];
            $tens = [
                '',
                '',
                'twenty',
                'thirty',
                'forty',
                'fifty',
                'sixty',
                'seventy',
                'eighty',
                'ninety'
            ];

            // helper for 1‑ or 2‑digit chunks
            $twoDigits = function ($n) use ($units, $tens) {
                if ($n < 20)
                    return $units[$n];
                $t = (int) ($n / 10);
                $u = $n % 10;
                return $tens[$t] . ($u ? '-' . $units[$u] : '');
            };

            // helper for 3‑digit chunk
            $threeDigits = function ($n) use ($twoDigits, $units) {
                $h = (int) ($n / 100);
                $r = $n % 100;
                return ($h ? $units[$h] . ' hundred' . ($r ? ' ' : '') : '')
                    . ($r ? $twoDigits($r) : '');
            };

            $parts = [
                'crore' => (int) ($rupees / 10000000),
                'lakh' => (int) ($rupees / 100000) % 100,
                'thousand' => (int) ($rupees / 1000) % 100,
                'hundred' => (int) ($rupees / 100) % 10,
                'rest' => $rupees % 100,
            ];

            $inWords = [];
            if ($parts['crore'])
                $inWords[] = $threeDigits($parts['crore']) . ' crore';
            if ($parts['lakh'])
                $inWords[] = $threeDigits($parts['lakh']) . ' lakh';
            if ($parts['thousand'])
                $inWords[] = $threeDigits($parts['thousand']) . ' thousand';
            if ($parts['hundred'])
                $inWords[] = $units[$parts['hundred']] . ' hundred';
            if ($parts['rest'])
                $inWords[] = $twoDigits($parts['rest']);

            $words = ucfirst(implode(' ', $inWords)) . ' rupees';
            if ($paise) {
                $words .= ' and ' . $twoDigits($paise) . ' paise';
            }
            return $words . ' only';
        }
    @endphp

    <style>
        /* Exact match to PDF CSS */
        .pdf-preview-canvas {
            font-family: Arial, sans-serif !important;
            padding: 40px !important;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            max-width: 1000px;
            margin: 20px auto;
            background: #fff;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
        }

        .pdf-preview-canvas .company-info {
            position: relative;
            margin-bottom: 15px;
            padding-top: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .pdf-preview-canvas .status {
            position: absolute;
            top: 0;
            right: 0;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: right;
            line-height: 1.3;
        }

        .pdf-preview-canvas table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .pdf-preview-canvas table.items-table th,
        .pdf-preview-canvas table.items-table td {
            padding: 5px 8px 10px;
            vertical-align: top;
            line-height: 1.5;
            font-weight: 400 !important;
        }

        .pdf-preview-canvas table.items-table thead th {
            background-color: #002d22;
            color: #fff;
        }

        .pdf-preview-canvas table.total-table {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
            border-collapse: collapse;
            padding: 5px 0px;
        }

        .pdf-preview-canvas table.total-table td {
            padding: 5px;
            line-height: 1.2;
        }

        .pdf-preview-canvas table.total-table22 {
            width: 100%;
            border-collapse: collapse;
            padding: 5px 0px;
        }

        .pdf-preview-canvas table.layout-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .pdf-preview-canvas table.layout-table td {
            border: none;
            vertical-align: top;
            padding: 0;
            line-height: 1.5;
        }

        .pdf-preview-canvas .text-right {
            text-align: right;
        }

        .pdf-preview-canvas .text-center {
            text-align: center;
        }

        .pdf-preview-canvas .text-left {
            text-align: left;
        }

        .pdf-preview-canvas h4,
        .pdf-preview-canvas strong {
            margin-bottom: 0px !important;
            color: #000;
        }
    </style>

    <section class="task__section" style="background-color: #f1f3f4; min-height: 100vh; padding-bottom: 50px;">
        <div class="text"
            style="background: #fff; border-bottom: 1px solid #e8eaed; padding: 15px 20px; margin-bottom: 20px;">
            <i class="bx bx-menu" id="mbtn"></i>
            Invoice Preview
            <a href="/signout" class="logoutbtn"><i class='bx bx-log-out'></i></a>
        </div>

        <div class="container-fluid" style="max-width: 1000px; margin: 0 auto;">

            <div class="d-flex align-items-center justify-content-between bg-white px-4 py-3 mb-4 shadow-sm"
                style="border-radius: 12px; top: 85px; z-index: 100; box-shadow: 0 4px 15px rgba(0,0,0,0.03)!important; border: 1px solid #f1f3f4;">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ url()->previous() == url()->current() ? '/invoices' : url()->previous() }}"
                        class="btn btn-light" style="width: 40px; height: 40px; border-radius: 50%;" title="Back">
                        <i class="bx bx-arrow-back" style="font-size: 1.2rem; margin-top: 4px;"></i>
                    </a>
                    <div>
                        <h5 class="mb-0" style="font-weight: 700; color: #202124;">Invoice:
                            INV-{{ $invoice->invoice_number }}</h5>
                    </div>
                </div>
                <a href="/invoices/download/{{ $invoice->id ?? 0 }}" class="btn text-white px-4 py-2"
                    style="background: linear-gradient(135deg, #dc3545, #b02a37); border-radius: 8px; font-weight: 600;">
                    <i class='bx bxs-file-pdf'></i> Download PDF
                </a>
            </div>

            <div class="pdf-preview-canvas rounded">
                <!-- Company Info -->
                <div class="company-info">
                    @if(!empty($invoice->img))
                        <img src="{{ asset('assets/images/company/' . ($invoice->img ?? '')) }}"
                            style="max-height:80px; filter: drop-shadow(0px 0px 0px black);">
                    @endif

                    <div class="status">
                        <label style="font-size: 23px;text-transform:uppercase;">{{ $invoice->invoice }}
                            @if(($invoice->invoice ?? '') != 'invoice') INVOICE @endif</label><br>
                        <label style="color: #737373;">
                            #{{ ($invoice->invoice ?? '') != 'tax' ? strtoupper(substr($invoice->invoice, 0, 3)) : 'INV' }}-{{ $invoice->invoice_number }}
                        </label><br>
                        @php
                            $status = strtolower($invoice->status);
                            $badgeClass = match ($status) {
                                'unpaid' => 'text-danger',
                                'paid' => 'text-success',
                                'partial' => 'text-warning',
                                'cancelled' => 'text-secondary',
                                default => 'text-info'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </div>

                <!-- Billing & Shipping -->
                <table class="layout-table">
                    <tr>
                        <td style="width: 50%; text-align: left;">
                            <h4 style="margin: 0 0 5px 0;">{{ $invoice->cn ?? '' }}</h4>
                            @php
                                $addressParts = array_filter([
                                    $invoice->city ?? null,
                                    $invoice->state ?? null,
                                    " - " . $invoice->zipcode ?? null,
                                    $invoice->country ?? null
                                ]);
                            @endphp

                            @if(!empty($addressParts))
                                <div style="white-space: pre-line;">
                                    {{ $invoice->address ?? '' }}<br>{{ implode(', ', $addressParts) }}</div>
                            @endif
                            <label>Phone: +91-{{ $invoice->cm ?? '' }}</label><br>
                            <label>Email: {{ $invoice->ce ?? '' }}</label><br>

                            @if(!empty($invoice->cgst) && (($invoice->invoice ?? '') != 'invoice'))
                                <div style="white-space: pre-line;"><b>GST NO.:</b>{{ $invoice->cgst }}</div>
                            @endif

                            @if(!empty($invoice->cvat && (($invoice->invoice ?? '') != 'invoice')))
                                <div style="white-space: pre-line;"><b>VAT NO.:</b>{{ $invoice->cvat }}</div>
                            @endif
                        </td>
                        <td style="width: 50%; text-align: right;">
                            <h4 style="margin: 0 0 8px 0;">Bill To:</h4>
                            <h4 style="margin: 0 0 5px 0;">{{ $invoice->company }}</h4>
                            <div style="white-space: pre-line;">{{ $invoice->billing_address }}</div>

                            @if(!empty($invoice->shipping_address))
                                <br>
                                <h4 style="margin: 0 0 8px 0;">Ship To:</h4>
                                <div style="white-space: pre-line;">{{ $invoice->shipping_address }}</div>
                            @endif

                            @if(!empty($invoice->client_gstno))
                                <div style="white-space: pre-line;"><b>GST NO.:</b>{{ $invoice->client_gstno }}</div>
                            @endif

                            @if(!empty($invoice->vat))
                                <div style="white-space: pre-line;"><b>VAT NO.:</b>{{ $invoice->vat }}</div>
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Dates & Reference -->
                <table class="layout-table">
                    <tr>
                        <td style="width: 50%;">
                            <div><strong>Invoice Date:</strong>
                                {{ \Carbon\Carbon::parse($invoice->date)->format('M d, Y') }}</div>
                            <div><strong>Due Date:</strong>
                                {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}
                            </div>
                        </td>
                        <td style="width: 50%; text-align: right;">
                            @if(!empty($invoice->reference))
                                <div><strong>Reference / PO #:</strong> {{ $invoice->reference ?? 'N/A' }}</div>
                            @endif
                            @if(!empty($invoice->payment_mode))
                                <div><strong>Payment Mode:</strong> {{ ucfirst($invoice->payment_mode) }}</div>
                            @endif
                            @if(!empty($invoice->sales_agent))
                                <div><strong>Sale Agent:</strong> {{ $invoice->sales_agent ?? '' }}</div>
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Items Table -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 30px;">#</th>
                            <th class="text-left">Item</th>
                            <th class="text-center" style="width: 100px;">SAC Code</th>
                            <th class="text-center" style="width: 80px;">Qty/Hours</th>
                            <th class="text-right" style="width: 65px;">Rate</th>
                            <th class="text-right" style="width: 100px;">Tax (%)</th>
                            <th class="text-right" style="max-width: 120px;padding-right: 21px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $subTotal = 0;
                            $totalTax = 0;
                            $cgstTotal = 0;
                            $sgstTotal = 0;
                            $igstTotal = 0;
                            $vatTotal = 0;
                        @endphp
                        @foreach($invoice_items as $k => $item)
                            @php
                                $quantity = $item->quantity;
                                $price = $item->price;
                                $cgst = $item->cgst_percent;
                                $sgst = $item->sgst_percent;
                                $igst = $item->igst_percent;
                                $vat = $item->vat_percent;
                                $lineTotal = $quantity * $price;

                                $subTotal += $lineTotal;
                                $cgstTotal += ($lineTotal * $cgst / 100);
                                $sgstTotal += ($lineTotal * $sgst / 100);
                                $igstTotal += ($lineTotal * $igst / 100);
                                $vatTotal += ($lineTotal * $vat / 100);
                            @endphp
                            <tr>
                                <td class="text-center">{{ $k + 1 }}</td>
                                <td class="text-left">
                                    <strong>{{ $item->short_description }}</strong><br>
                                    {{ $item->long_description }}
                                </td>
                                <td class="text-center">{{ $item->sac_code ?? '--' }}</td>
                                <td class="text-center">{{ $quantity }}</td>
                                <td class="text-right">{{ number_format($price, 2) }}</td>
                                <td class="text-right">
                                    @if($cgst > 0) CGST {{ number_format($cgst, 2) }}%<br> @endif
                                    @if($sgst > 0) SGST {{ number_format($sgst, 2) }}%<br> @endif
                                    @if($igst > 0) IGST {{ number_format($igst, 2) }}%<br> @endif
                                    @if($vat > 0) VAT {{ number_format($vat, 2) }}%<br> @endif
                                </td>
                                <td class="text-right" style="max-width: 120px;padding-right: 21px;">
                                    {{ number_format($lineTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Totals -->
                <table class="total-table">
                    <tr>
                        <td class="text-right"><strong>Subtotal</strong></td>
                        <td class="text-right" style="padding-right: 21px;">{{ number_format($subTotal, 2) }}</td>
                    </tr>

                    @php
                        $discountType = $invoice->discount_mode ?? 'flat';
                        $discountVal = floatval($invoice->discount ?? 0);
                        $discount = ($discountType === 'percentage')
                            ? ($subTotal + $totalTax) * ($discountVal / 100)
                            : $discountVal;

                        $adjustment = floatval($invoice->adjustment ?? 0);
                        $grandTotal = $subTotal + $cgstTotal + $sgstTotal + $igstTotal + $vatTotal - $discount - $adjustment;
                    @endphp

                    @if($discount > 0 && $invoice->discount_type == 'before-tax')
                        <tr>
                            <td class="text-right"><strong>Discount</strong></td>
                            <td class="text-right" style="padding-right: 21px;">{{ number_format($discount, 2) }}</td>
                        </tr>
                    @endif

                    @if($cgstTotal > 0)
                        <tr>
                            <td class="text-right"><strong>Total CGST</strong></td>
                            <td class="text-right" style="padding-right: 21px;">{{ number_format($cgstTotal, 2) }}</td>
                        </tr>
                    @endif
                    @if($sgstTotal > 0)
                        <tr>
                            <td class="text-right"><strong>Total SGST</strong></td>
                            <td class="text-right" style="padding-right: 21px;">{{ number_format($sgstTotal, 2) }}</td>
                        </tr>
                    @endif
                    @if($igstTotal > 0)
                        <tr>
                            <td class="text-right"><strong>Total IGST</strong></td>
                            <td class="text-right" style="padding-right: 21px;">{{ number_format($igstTotal, 2) }}</td>
                        </tr>
                    @endif
                    @if($vatTotal > 0)
                        <tr>
                            <td class="text-right"><strong>Total VAT</strong></td>
                            <td class="text-right" style="padding-right: 21px;">{{ number_format($vatTotal, 2) }}</td>
                        </tr>
                    @endif

                    @if($discount > 0 && $invoice->discount_type == 'after-tax')
                        <tr>
                            <td class="text-right"><strong>Discount</strong></td>
                            <td class="text-right" style="padding-right: 21px;">{{ number_format($discount, 2) }}</td>
                        </tr>
                    @endif

                    @if($adjustment > 0)
                        <tr>
                            <td class="text-right"><strong>Advance Payment</strong></td>
                            <td class="text-right" style="padding-right: 21px;">- {{ number_format($adjustment, 2) }}</td>
                        </tr>
                    @endif
                </table>
                <div class="w-100" style="background-color: #e5e7eb; border-radius: 5px;">
                    <table class="total-table" style="width: 100%;">
                        <tr>
                            <td class="text-right"><strong>Grand Total</strong></td>
                            <td class="text-right" style="padding-right: 21px; width: 120px;">
                                <strong>{{ number_format($grandTotal, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
                <h3 class="text-center" style="font-size: 14px;">{{ ucwords(amountToWords($grandTotal ?? 0)) }}</h3>

                <!-- Notes -->
                <div style="margin-top: 20px;">
                    @php $company = session('companies');
                    $companyBankDetails = json_decode($invoice->bank_details ?? $company->bank_details ?? ''); @endphp
                    @if(!empty($companyBankDetails[0]))
                        <h4>Payment & Account Info:</h4>
                        <table class="total-table22">
                            <tr>
                                <td>
                                    @if(!empty($companyBankDetails[1]))
                                    <div style="white-space: pre-line;">Name: {{ $companyBankDetails[1] ?? '' }}</div>@endif
                                    @if(!empty($companyBankDetails[0]))
                                        <div style="white-space: pre-line;">Bank Name: {{ $companyBankDetails[0] ?? '' }}</div>
                                    @endif
                                    @if(!empty($companyBankDetails[3]))
                                        <div style="white-space: pre-line;">IFSC Code: {{ $companyBankDetails[3] ?? '' }}</div>
                                    @endif
                                    @if(!empty($companyBankDetails[2]))
                                    <div style="white-space: pre-line;">Ac No: {{ $companyBankDetails[2] ?? '' }}</div>@endif
                                </td>
                                <td class="text-right">
                                    @if(!empty($companyBankDetails[4]))
                                    <div style="white-space: pre-line;">Upi: {{ $companyBankDetails[4] ?? '' }}</div>@endif
                                </td>
                            </tr>
                        </table>
                    @endif

                    @if(!empty($invoice->client_note))
                        <h4>Client Note:</h4>
                        <div style="white-space: pre-line;">{{ $invoice->client_note }}</div>
                    @endif

                    @if(!empty($invoice->terms))
                        <h4 style="margin-top: 20px;">Terms &amp; Conditions:</h4>
                        <div style="white-space: pre-line;">{{ $invoice->terms }}</div>
                    @endif

                    <br><br>
                    <h4>Authorized Signature</h4>
                    <img src="{{ asset('assets/images/signs/' . (Auth::User()->imgsign ?? 'default.png')) }}"
                        style="height: 90px;" />
                    <div style="border-top: 1px solid #000; width: 200px;"></div>
                </div>
            </div>
        </div>
    </section>
@endsection
