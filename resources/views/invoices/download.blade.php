<!DOCTYPE html>
<html>
<head>
    <title>Invoice # INV-{{ $invoice->invoice_number }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Remove default margins for PDF output */
        html, body {
            margin: 0;
            padding: 0;
        }
        body { 
            font-family: Arial, sans-serif !important; 
            padding: 10px; /* Adjust as needed to remove extra PDF space */
            color: #333;
            font-size: 14px;
            line-height: 1.5; /* Consistent line height for readability */
            -webkit-print-color-adjust: exact;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px 20px !important;
        }

        .company-info {
            position: relative;
            margin-bottom: 15px;
            padding-top: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .status {
            position: absolute;
            top: 0;
            right: 0;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: right;
            line-height: 1.3;
        }

        /* Status Color Variations */
        .text-danger { color: #dc2626; }
        .text-success { color: #16a34a; }
        .text-warning { color: #ca8a04; }
        .text-secondary { color: #4b5563; }
        .text-info { color: #2563eb; }

        /* Tables with borders for itemized sections */
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            page-break-inside: avoid;
        }
        table.items-table th, 
        table.items-table td {
            padding: 5px 8px 10px;
            border: 0px solid #e2e8f0;
            vertical-align: top;
            line-height: 1.5; /* Consistent row line height */
            font-weight: 400 !important;
        }
        .bg-gray td {
            background-color: #e5e7eb;
            border-radius: 5px;
        }
        table.items-table thead th {
            background-color: #002d22;
            color: #fff;
        }
        table.items-table thead tr{
            border-radius: 5px;
            overflow: hidden;
        }

        /* Totals table */
        table.total-table {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
            border-collapse: collapse;
            page-break-inside: avoid;
            padding: 5px 0px;
        }
        table.total-table td {
            padding: 5px;
            border: 0px solid #e2e8f0;
            line-height: 1.2;
        }

        /* Totals table */
        table.total-table22 {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
            padding: 5px 0px;
        }

        /* Tables with no borders for layout sections */
        table.layout-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table.layout-table td {
            border: none;
            vertical-align: top;
            padding: 0;
            line-height: 1.5;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }

        .notes-section {
            margin-top: 20px;
        }
        
        h4, strong {
            margin-bottom: 0px !important;
            color: #000;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    @php
        $symbol = match($invoice->currency ?? 'INR') {
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $invoice->currency ?? 'INR'
        };
        
        function amountToWords($amount, string $locale = 'en_IN'): string
        {
            $amount = (float) str_replace([',', ' '], '', $amount);
            $rupees = (int) $amount;
            $paise  = (int) round(($amount - $rupees) * 100);
        
            if (class_exists('NumberFormatter')) {
                $fmt = new NumberFormatter($locale, NumberFormatter::SPELLOUT);
                $words = ucfirst($fmt->format($rupees)) . ' rupees';
                if ($paise) {
                    $words .= ' and ' . $fmt->format($paise) . ' paise';
                }
                return $words . ' only';
            }
        
            $units  = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven',
                       'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen',
                       'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen',
                       'nineteen'];
            $tens   = ['', '', 'twenty', 'thirty', 'forty', 'fifty',
                       'sixty', 'seventy', 'eighty', 'ninety'];
        
            $twoDigits = function ($n) use ($units, $tens) {
                if ($n < 20) return $units[$n];
                $t   = (int) ($n / 10);
                $u   =  $n % 10;
                return $tens[$t] . ($u ? '-' . $units[$u] : '');
            };
        
            $threeDigits = function ($n) use ($twoDigits, $units) {
                $h = (int) ($n / 100);
                $r = $n % 100;
                return ($h ? $units[$h] . ' hundred' . ($r ? ' ' : '') : '')
                     . ($r ? $twoDigits($r) : '');
            };
        
            $parts = [
                'crore'   => (int) ($rupees / 10000000),
                'lakh'    => (int) ($rupees / 100000) % 100,
                'thousand'=> (int) ($rupees / 1000)  % 100,
                'hundred' => (int) ($rupees / 100)   % 10,
                'rest'    =>  $rupees % 100,
            ];
        
            $inWords = [];
            if ($parts['crore'])    $inWords[] = $threeDigits($parts['crore'])    . ' crore';
            if ($parts['lakh'])     $inWords[] = $threeDigits($parts['lakh'])     . ' lakh';
            if ($parts['thousand']) $inWords[] = $threeDigits($parts['thousand']) . ' thousand';
            if ($parts['hundred'])  $inWords[] = $units[$parts['hundred']] . ' hundred';
            if ($parts['rest'])     $inWords[] = $twoDigits($parts['rest']);
        
            $words = ucfirst(implode(' ', $inWords)) . ' rupees';
            if ($paise) {
                $words .= ' and ' . $twoDigits($paise) . ' paise';
            }
            return $words . ' only';
        }
    @endphp

    <div class="container">
        <!-- Company Info -->
        <div class="company-info">
            @if(!empty($invoice->img))
                <img src="{{ $base64 }}" 
                     style="max-height:60px; filter: drop-shadow(0px 0px 0px black);">
            @endif
            
            <div class="status">
                <!--<label style="font-size: 23px;">Invoice</label><br>
                <label style="color: #737373;"># INV-{{ $invoice->invoice_number }}</label><br>-->
                <label style="font-size: 23px;text-transform:uppercase;">{{ $invoice->invoice }} @if(($invoice->invoice ?? '') != 'invoice') INVOICE @endif</label><br>
                <label style="color: #737373;">
                    #{{ ($invoice->invoice ?? '') != 'tax' ? strtoupper(substr($invoice->invoice, 0, 3)) : 'INV' }}-{{ $invoice->invoice_number }}
                </label><br>
                {{-- Payment Status Badge --}}
                @php
                    $status = strtolower($invoice->status);
                    $badgeClass = match($status) {
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

        <!-- Billing & Shipping (Layout Table without borders) -->
        <table class="layout-table">
            <tr>
                <td style="width: 50%; text-align: left;">
                    <h4 style="margin: 0 0 5px 0;">{{ $invoice->cn ?? '' }}</h4>
                    @php
                        $addressParts = array_filter([ 
                            $invoice->city ?? null,
                            $invoice->state ?? null,
                            " - ".$invoice->zipcode ?? null,
                            $invoice->country ?? null
                        ]);
                    @endphp
                    
                    @if(!empty($addressParts))
                        <div style="white-space: pre-line;">{{ $invoice->address ?? '' }}<br>{{ implode(', ', $addressParts) }}</div>
                    @endif
                    <label>Phone: +91-{{ $invoice->cm ?? '' }}</label><br>
                    <label>Email: {{ $invoice->ce ?? '' }}</label><br>
                    
                    <!--GST Number / Vat Number-->
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
                    
                    <!--GST Number / Vat Number-->
                    @if(!empty($invoice->client_gstno))
                    <div style="white-space: pre-line;"><b>GST NO.:</b>{{ $invoice->client_gstno }}</div>
                    @endif
                    
                    @if(!empty($invoice->vat))
                    <div style="white-space: pre-line;"><b>VAT NO.:</b>{{ $invoice->vat }}</div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Dates & Reference (Layout Table without borders) -->
        <table class="layout-table">
            <tr>
                <td style="width: 50%;">
                    <div><strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($invoice->date)->format('M d, Y') }}</div>
                    <div><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</div>
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
                        $lineTax = $lineTotal * (($cgst + $sgst + $igst + $vat) / 100);
                        
                        $subTotal += $lineTotal;
                        $cgstTotal += ($lineTotal * $cgst / 100);
                        $sgstTotal += ($lineTotal * $sgst / 100);
                        $igstTotal += ($lineTotal * $igst / 100);
                        $vatTotal += ($lineTotal * $vat / 100);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $k + 1 }}</td>
                        <td>
                            <strong>{{ $item->short_description }}</strong><br>
                            {{ $item->long_description }}
                        </td>
                        <td class="text-center">{{ $item->sac_code ?? '' }}</td>
                        <td class="text-center">{{ $quantity }}</td>
                        <td class="text-right">{{ number_format($price, 2) }}</td>
                        <td class="text-right">
                            @if($cgst > 0)
                            CGST {{ number_format($cgst, 2) }}%<br>
                            @endif
                             @if($sgst > 0)
                            SGST {{ number_format($sgst, 2) }}%<br>
                            @endif
                            @if($igst > 0)
                            IGST {{ number_format($igst, 2) }}%<br>
                            @endif
                            @if($vat > 0)
                            VAT {{ number_format($vat, 2) }}%<br>
                            @endif
                        </td>
                        <td class="text-right" style="max-width: 120px;padding-right: 21px;">{{ number_format($lineTotal, 2) }}</td>
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
            <table class="total-table">
                <tr>
                    <td class="text-right"><strong>Grand Total</strong></td>
                    <td class="text-right" style="padding-right: 21px;"><strong>{{ number_format($grandTotal, 2) }}</strong></td>
                </tr>
            </table>
        </div>
        <h3 class="text-center" style="font-size: 14px;">{{ ucwords(amountToWords($grandTotal ?? 0)) }}</h3>
        
        <!-- Notes -->
        <div class="notes-section">
             @php $company = session('companies'); $companyBankDetails = json_decode($invoice->bank_details ?? $company->bank_details ?? ''); @endphp
            @if(!empty($companyBankDetails[0]))
                <h4>Payment & Account Info:</h4>
                <table class="total-table22">
                    <tr>
                        <td>
                            @if(!empty($companyBankDetails[1]))<div style="white-space: pre-line;">Name: {{ $companyBankDetails[1] ?? '' }}</div>@endif
                            @if(!empty($companyBankDetails[0]))<div style="white-space: pre-line;">Bank Name: {{ $companyBankDetails[0] ?? '' }}</div>@endif
                            @if(!empty($companyBankDetails[3]))<div style="white-space: pre-line;">IFSC Code: {{ $companyBankDetails[3] ?? '' }}</div>@endif
                            @if(!empty($companyBankDetails[2]))<div style="white-space: pre-line;">Ac No: {{ $companyBankDetails[2] ?? '' }}</div>@endif
                        </td>
                        <td class="text-right">
                            @if(!empty($companyBankDetails[4]))<div style="white-space: pre-line;">Upi: {{ $companyBankDetails[4] ?? '' }}</div>@endif
                            <br><br><br>
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
            <!--@php
            $company = json_decode(($invoice->bank_details ?? ''),true);
            @endphp
            {{-- BANK DETAILS --}}
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4 class="font-weight-bold">Company Bank Details</h4>
                    <p>
                        <strong>Bank Name:</strong> {{ $company[0] ?? 'N/A' }}<br>
                        <strong>Account Number:</strong> {{ $company[2] ?? 'N/A' }}<br>
                        <strong>IFSC Code:</strong> {{ $company[3] ?? 'N/A' }}<br>
                        <strong>Account Holder:</strong> {{ $company[1] ?? 'N/A' }}
                    </p>
                </div>
            </div>-->
            <br><br>
            <h4>Authorized Signature</h4>
            @if(!empty($signBase64))
                <img src="{{ $signBase64 }}" style="height: 90px;" />
            @endif
            <div style="border-top: 1px solid #000; width: 200px;"></div>
        </div>

        <!--<div class="footer">
            <p>Thank You For Your Business!</p>
        </div>-->
    </div>
</body>
</html>

