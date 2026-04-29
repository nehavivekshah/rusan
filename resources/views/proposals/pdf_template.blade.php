<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>Proposal #PRO-{{ sprintf('%06d', $proposal->id ?? 0) }}</title>
    <!--<link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">-->
    <style>
        @page {
            margin: 0cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 14px;
            color: #2e2e2e;
            line-height: 1.2;
            margin: 0;
            padding: 40px;
        }
        
        body, h2, h3, h4, h5, p, span, label, .fontstyle, * {
            font-family: Arial, Helvetica, sans-serif !important;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
            padding: 0 0px;
        }

        .logo {
            max-height: 50px;
            max-width: 180px;
            margin-bottom: 10px;
        }
        
        .company-details{
            padding-right: 20%;
        }
        
        .client-details{
            padding-left: 20%;
        }

        .company-details,
        .client-details {
            font-size: 14px;
            color: #333;
            line-height: 1.2;
        }

        .company-details strong,
        .client-details strong {
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
        }

        .text-right { text-align: right!important; }
        .text-left { text-align: left!important; }
        .text-center{ text-align: center!important; }

        .proposal-id {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .proposal-subject {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .meta-info {
            width: 50%;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .meta-info div {
            margin-bottom: 5px;
        }

        .items-table {
            margin-bottom: 25px;
        }

        .items-table th {
            background-color: #e5e7eb;
            font-size: 14px;
            text-transform: capitalize;
            padding: 10px 8px;
            /*border-bottom: 2px solid #cccccc;
            border-top: 1px solid #cccccc;*/
            text-align: left;
        }

        .items-table td {
            padding: 10px 8px;
            vertical-align: top;
            border-bottom: 0px solid #e5e7eb;
            font-size: 14px;
        }

        .items-table td strong {
            display: block;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .items-table small {
            color: #666;
            display: block;
            line-height: 1.2;
        }

        .totals-wrapper {
            width: 100%;
            margin-top: 25px;
        }

        .totals-table {
            width: auto;
            min-width: 250px;
            max-width: 300px;
            margin-left: auto;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 6px 6px;
            font-size: 14px;
            border-bottom: 0px dotted #ccc;
        }

        .totals-table tr:last-child td {
            border-bottom: none;
        }

        .totals-table td:first-child {
            text-align: left;
            padding-right: 15px;
        }

        .totals-table td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .totals-table tr.grand-total td {
            font-weight: bold;
            font-size: 14px;
            padding-top: 10px;
            border-top: 1px solid #555;
            border-bottom: none;
        }

        .signature-section {
            margin-top: 20px;
            padding-top: 20px;
        }

        .signature-line {
            width: 135px;
            border-bottom: 1px solid #333;
            height: 1px;
        }

        .signature-label {
            margin-top: 6px;
            font-size: 14px;
            font-weight: bold;
        }

        .notes-section {
            margin-top: 20px;
            font-size: 14px;
            color: #444;
        }

        .notes-section h4 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .symbol{
            height: 11px;
        }
        .font-weight-bold{
            font-weight: 600;
        }

    </style>
</head>
<body>
    
    {{-- Use a table for the main header layout --}}
    <table class="header-table">
        <tr>
            <td>
                @if(!empty($proposal->companyImg))
                    {{-- Make sure the path is absolute for dompdf --}}
                    @php $logoPath = public_path('assets/images/company/' . $proposal->companyImg); @endphp
                    @if(file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo" class="logo" style="margin-bottom: 20px;">
                    @endif
                @endif
            </td>
            <td>
                <h2 class="fontstyle" style="text-align: right;">QUOTATION</h2>
            </td>
        </tr>
        <tr>
            {{-- Company Info Column --}}
            <td style="width: 50%;">
                <div class="company-details">
                    <strong style="color:#000;">{{ $proposal->companyName ?? 'Your Company Name' }}</strong>
                    @if(!empty($proposal->companyAddress)) {!! nl2br(e($proposal->companyAddress)) !!} @endif
                    @if(!empty($proposal->companyCity)) {{ $proposal->companyCity }}, @endif
                    @if(!empty($proposal->companyState)) {{ $proposal->companyState }}, @endif
                    @if(!empty($proposal->companyCountry)) {{ $proposal->companyCountry }} - @endif
                    @if(!empty($proposal->companyZipCode)) {{ $proposal->companyZipCode }}<br> @endif
                    @if(!empty($proposal->companyMob)) +91-{{ $proposal->companyMob }}<br> @endif
                    @if(!empty($proposal->companyEmail)) {{ $proposal->companyEmail }}<br> @endif
                    @if(!empty($proposal->gst)) GST No.: {{ $proposal->gst }}<br> @endif
                    @if(!empty($proposal->vat)) VAT No.: {{ $proposal->vat }}<br> @endif
                </div>
            </td>
            {{-- Proposal Heading & Client Info Column --}}
            <td style="width: 50%;">
                <div class="client-details text-right">
                    <strong style="color:#000;">To,</strong>
                    <strong>{{ $proposal->client_name ?? 'Client Name' }}</strong>
                    @if(!empty($proposal->client_address)) {!! nl2br(e($proposal->client_address)) !!} @endif
                    @if(!empty($proposal->client_city)) {{ $proposal->client_city }}, @endif
                    @if(!empty($proposal->client_state)) {{ $proposal->client_state }}, @endif
                    @if(!empty($proposal->client_country)) {{ $proposal->client_country }} - @endif
                    @if(!empty($proposal->client_zip)) {{ $proposal->client_zip }} @endif
                    @if(!empty($proposal->client_phone)) <br>{{ $proposal->client_phone }}<br> @endif
                    @if(!empty($proposal->client_email)) {{ $proposal->client_email }} @endif
                </div>
            </td>
        </tr>
        <tr><td><br></td></tr>
        <tr>
            <td class="meta-info">
                <div class="proposal-id"># PRO-{{ sprintf('%06d', $proposal->id ?? 0) }}</div>
                <div class="proposal-subject">{{ $proposal->subject ?? 'Proposal Subject' }}</div>
            </td>
            <td class="meta-info text-right">
                <div>Date: {{ $proposal->proposal_date ? date_format(date_create($proposal->proposal_date ?? null),'d M, Y') : 'N/A' }}</div>
                <div>Open Till: {{ $proposal->open_till ? date_format(date_create($proposal->open_till ?? null),'d M, Y') : 'N/A' }}</div>
            </td>
        </tr>
    </table>
    
    {{-- Items Table --}}
    <table class="items-table" style="width:100%;border-radius:5px;overflow:hidden;">
        <thead>
            <tr>
                <th>#</th>
                <th class="text-left" style="width: 35%;">Item</th>
                <th style="width: 10%;" class="text-center">Hsn</th>
                <th style="width: 10%;" class="text-right">Qty</th>
                <th style="width: 15%;" class="text-right">Rate</th>
                <th style="width: 15%;" class="text-right">Tax</th>
                <th style="width: 16%;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($proposalItems as $k=>$item)
            <tr>
                <td>{!! $k+1 !!}</td>
                <td class="text-left" style="width: 45%;">
                    <strong style="color:#000;">{{ $item->item_name ?? 'N/A' }}</strong>
                    @if(!empty($item->description))
                    <small>{!! nl2br(e($item->description)) !!}</small>
                    @endif
                </td>
                <td style="width: 10%;" class="text-center">{{ $item->hsn ?? '--' }}</td>
                <td style="width: 10%;" class="text-right">{{ $item->quantity ?? 1 }}</td>
                <td style="width: 15%;" class="text-right">{{ number_format($item->rate ?? 0, 2) }}</td>
                <td style="width: 15%;" class="text-right">
                    @if(!empty(($item->cgst_percent ?? 0)*100)) CGST {{ ($item->cgst_percent ?? 0)*100 }}%<br> @endif
                    @if(!empty(($item->sgst_percent ?? 0)*100)) SGST {{ ($item->sgst_percent ?? 0)*100 }}%<br> @endif
                    @if(!empty(($item->igst_percent ?? 0)*100)) IGST {{ ($item->igst_percent ?? 0)*100 }}%<br> @endif
                    @if(!empty(($item->vat_percent ?? 0)*100)) VAT {{ ($item->vat_percent ?? 0)*100 }}% @endif
                </td>
                <td style="width: 16%;" class="text-right">{{ number_format($item->amount ?? ($item->quantity ?? 1) * ($item->rate ?? 0), 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="padding: 20px 0;">No items listed for this proposal.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Totals Section --}}
    @php
        // Determine currency symbol
        $symbol = match(strtoupper($proposal->currency_symbol ?? 'INR')) {
            'INR' => public_path('assets/icons/rupee.png'), 'USD' => public_path('assets/icons/dollar.png'), 'EUR' => public_path('assets/icons/euro.png'), 'GBP' => public_path('assets/icons/money.png'),
            default => ($proposal->currency_symbol ?? public_path('assets/icons/rupee.png')) // Fallback to symbol or default
        };
        
        function amountToWords($amount, string $locale = 'en_IN'): string
        {
            // normalise
            $amount   = (float) str_replace([',', ' '], '', $amount);
            $rupees   = (int) $amount;
            $paise    = (int) round(($amount - $rupees) * 100);
        
            if (class_exists('NumberFormatter')) {
                $fmt = new NumberFormatter($locale, NumberFormatter::SPELLOUT);
                $words  = ucfirst($fmt->format($rupees)) . ' rupees';
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
        
            // helper for 1‑ or 2‑digit chunks
            $twoDigits = function ($n) use ($units, $tens) {
                if ($n < 20) return $units[$n];
                $t   = (int) ($n / 10);
                $u   =  $n % 10;
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
        
            $words  = ucfirst(implode(' ', $inWords)) . ' rupees';
            if ($paise) {
                $words .= ' and ' . $twoDigits($paise) . ' paise';
            }
            return $words . ' only';
        }
    @endphp

    {{-- Wrapper table/div to align totals table to the right --}}
    <div class="totals-wrapper">
        <table class="totals-table">
            <tbody>
                <tr>
                    <td>Sub Total:</td>
                    <td><img src="{{ $symbol }}" class="symbol">{{ number_format($proposal->sub_total ?? 0, 2) }}</td>
                </tr>
            
                @if(isset($proposal->discount_percentage) && $proposal->discount_percentage > 0 && $proposal->discount_type == 'before-tax')
                <tr>
                    <td>Discount:</td>
                    <td class="text-danger">-<img src="{{ $symbol }}" class="symbol">{{ number_format($proposal->discount_amount_calculated, 2) }}</td>
                </tr>
                @endif
            
                @if(isset($proposal->cgst_total) && $proposal->cgst_total > 0)
                <tr>
                    <td>CGST:</td>
                    <td><img src="{{ $symbol }}" class="symbol">{{ number_format($proposal->cgst_total, 2) }}</td>
                </tr>
                @endif
            
                @if(isset($proposal->sgst_total) && $proposal->sgst_total > 0)
                <tr>
                    <td>SGST:</td>
                    <td><img src="{{ $symbol }}" class="symbol">{{ number_format($proposal->sgst_total, 2) }}</td>
                </tr>
                @endif
            
                @if(isset($proposal->igst_total) && $proposal->igst_total > 0)
                <tr>
                    <td>IGST:</td>
                    <td><img src="{{ $symbol }}" class="symbol">{{ number_format($proposal->igst_total, 2) }}</td>
                </tr>
                @endif
            
                @if(isset($proposal->vat_total) && $proposal->vat_total > 0)
                <tr>
                    <td>Tax:</td>
                    <td><img src="{{ $symbol }}" class="symbol">{{ number_format($proposal->vat_total, 2) }}</td>
                </tr>
                @endif
            
                @if(isset($proposal->discount_percentage) && $proposal->discount_percentage > 0 && $proposal->discount_type == 'after-tax')
                <tr>
                    <td>Discount:</td>
                    <td class="text-danger">-<img src="{{ $symbol }}" class="symbol">{{ number_format($proposal->discount_amount_calculated, 2) }}</td>
                </tr>
                @endif
            
                @if(isset($proposal->adjustment_amount) && $proposal->adjustment_amount > 0)
                <tr>
                    <td>Adjustment:</td>
                    <td class="text-danger">-<img src="{{ $symbol }}" class="symbol">{{ number_format($proposal->adjustment_amount, 2) }}</td>
                </tr>
                @endif
            
                <tr class="grand-total">
                    <td>Total:</td>
                    <td><img src="{{ $symbol }}" class="symbol">{{ number_format($proposal->grand_total ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <div class="fontstyle" style="font-size: 15px;text-align: center;margin-top:10px;">{{ ucwords(amountToWords($proposal->grand_total ?? 0)) }}</div>
    </div>
    <div style="clear: both;"></div> {{-- Clear float if needed, though margin should handle it --}}


    {{-- Notes / Terms Section --}}
    @if(!empty($proposal->notes))
    <div class="notes-section">
        {{-- <h4>Notes & Terms</h4>  Optional Heading --}}
        {!! ($proposal->notes) !!}
    </div>
    @endif

    {{-- Signature Section --}}
    <div class="signature-section">
        <div class="signature-label">Authorized Signature</div>
        <img src="{!! public_path('assets/images/signs/'.(Auth::User()->imgsign ?? 'default.png')) !!}" style="height: 110px;padding-left:15px;" />
        <div class="signature-line"></div>
    </div>

</body>
</html>
