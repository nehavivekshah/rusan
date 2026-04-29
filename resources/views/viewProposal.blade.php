<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="shortcut icon" href="https://esecrm.com/assets/images/favicon.ico" type="image/x-icon">
        <title>Proposal: {{ $proposal->subject ?? 'Details' }}</title>
        {{-- Bootstrap CSS --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        {{-- Boxicons CSS --}}
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        {{-- Google Fonts (Optional - for a cleaner look) --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
        <style>
            body {
                background-color: #f4f7fc;
                font-family: "Poppins", sans-serif !important;
                color: #333;
                font-size: 14px; /* Base font size similar to example */
            }
            body, h2, h3, h4, h5, p, span, label {
                font-family: "Poppins", sans-serif !important;
            }
            .text-right{
                text-align: right;
            }
            .font-weight-bold{
                font-weight: 600;
            }
            .proposal-page-container {
                 max-width: 960px;
                 margin: 15px auto;
            }
            .proposal-document {
                background-color: #fff;
                padding: 30px 30px;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                border: 1px solid #e9ecef;
                margin-bottom: 30px; /* Space before action buttons */
            }
    
            /* Header Styles */
            .proposal-header .logo {
                max-height: 55px; /* Adjust logo size */
                margin-bottom: 0px;
            }
            .company-address p, .client-address p {
                margin-bottom: 0.1rem;
                line-height: normal;
                font-size: 0.88rem; /* Slightly smaller address font */
                color: #4a5568; /* Softer text color */
            }
            .company-address h5, .client-address h6 {
                font-weight: 600;
                margin-bottom: 0.5rem;
                font-size: 1rem;
            }
            .client-address h6 {
                color: #6c757d; /* Muted "To" label */
                margin-bottom: 0.3rem;
                font-size: 0.9rem;
                text-transform: uppercase;
            }
            .client-address strong {
                font-weight: 600;
                color: #2d3748;
            }
            .proposal-meta-info {
                margin-top: 25px;
                /*padding-bottom: 20px;
                border-bottom: 1px solid #dee2e6;*/
                margin-bottom: 25px;
            }
            .proposal-meta-info h3 {
                font-size: 1.3rem;
                font-weight: 600;
                margin-bottom: 0.25rem;
            }
             .proposal-meta-info p {
                 font-size: 0.9rem;
                 color: #4a5568;
                 margin-bottom: 0.15rem;
             }
             .proposal-meta-info p strong {
                  color: #2d3748;
                  font-weight: 500;
                  /*min-width: 80px; *//* Align labels */
                  display: inline-block;
             }
    
            /* Table Styles */
            .table {
                border: 1px solid #e9ecef;
            }
            .table thead th {
                background-color: #f1f5f9;
                color: #495057;
                font-weight: 600;
                font-size: 0.8rem; /* Smaller header font */
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-bottom: 2px solid #dee2e6 !important; /* Stronger bottom border */
                border-top: none;
                padding: 10px 12px;
            }
            .table td {
                padding: 12px 12px;
                vertical-align: top; /* Align top like example */
                font-size: 0.88rem;
                border-color: #e9ecef;
                border-top-width: 0; /* Remove default top border */
                border-bottom: 1px solid #e9ecef; /* Add bottom border */
            }
            .table tbody tr:last-child td {
                border-bottom: none; /* No border on last item row */
            }
            .table td strong {
                font-weight: 600;
                color: #2d3748;
                display: block; /* Ensure it takes its line */
                margin-bottom: 3px;
            }
            .item-description {
                font-size: 0.85rem;
                color: #6c757d;
                line-height: normal;
                padding-left: 5px; /* Slight indent for description */
            }
    
            /* Totals Styles */
            /*.totals-section {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid #dee2e6;
            }*/
            .totals-table {
                width: 100%;
                max-width: 280px; /* Narrower totals block */
                margin-left: auto; /* Align right */
                font-size: 0.9rem;
            }
            .totals-table td {
                padding: 6px 0px; /* Less vertical padding, no horizontal */
                border: none;
            }
            .totals-table td:first-child {
                text-align: left;
                color: #4a5568;
                font-weight: 500;
            }
             .totals-table td:last-child {
                 text-align: right;
                 font-weight: 600;
                 color: #2d3748;
             }
             .totals-table tr.grand-total td {
                 font-size: 1rem; /* Slightly larger grand total */
                 padding-top: 8px;
             }
    
            /* Text Sections Styles */
            .text-section {
                margin-top: 25px;
            }
            .text-section h4 {
                font-weight: 600;
                font-size: 1.1rem;
                color: #343a40;
                margin-bottom: 15px;
                padding-bottom: 5px;
                border-bottom: 1px solid #eee; /* Subtle separator below heading */
            }
            .text-section p {
                font-size: 0.88rem;
                line-height: normal;
                color: #4a5568;
            }
    
            /* Signature Styles */
            .signature-section {
                margin-top: 50px;
                padding-top: 30px;
                border-top: 1px solid #e9ecef;
            }
            .signature-line {
                border-bottom: 1px solid #555;
                height: 1px;
                width: 150px; /* Width of signature line */
            }
             .signature-section p {
                 font-size: 0.9rem;
                 color: #4a5568;
                 margin-bottom: 0;
             }
    
            /* ── Action Buttons Bar ── */
            .proposal-actions-container {
                position: sticky;
                top: 0;
                z-index: 100;
                background: #fff;
                border-bottom: 1px solid #e2e8f0;
                padding: 10px 16px;
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 8px;
                flex-wrap: wrap;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                margin-bottom: 16px;
            }
            .proposal-actions-container .btn {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 7px 16px;
                font-weight: 500;
                font-size: 0.88rem;
                border-radius: 6px;
                white-space: nowrap;
            }
            .action-feedback p {
                 font-weight: 500;
                 font-size: 0.9rem;
                 margin: 0;
            }
            .action-feedback i {
                margin-right: 4px;
                font-size: 1rem;
                vertical-align: middle;
            }
            .company-address, .client-address {
                width: 50%;
            }
            .signature-container {
              width: 100%;
              max-width: 600px; /* Adjust as needed */
              margin: auto;
            }
            
            #signature-pad {
              width: 100%;
              height: auto;
              border: 1px solid #ccc;
              display: block;
            }

            /* Responsive Adjustments */
            @media (max-width: 768px) {
                .proposal-document {
                    padding: 25px 20px;
                }
                .company-address, .client-address {
                    margin-bottom: 15px;
                }
                .client-address {
                    text-align: left !important; /* Align left on mobile */
                }
                .totals-table {
                    max-width: 100%;
                }
            }
            @media (max-width: 576px) {
                .proposal-actions-container {
                    justify-content: center;
                    padding: 8px 10px;
                    gap: 6px;
                }
                .proposal-actions-container .btn {
                    flex: 1 1 auto;
                    justify-content: center;
                    min-width: 100px;
                }
            }
        </style>
</head>
<body>
@php
    $symbol = match($proposal->currency_symbol ?? 'INR') {
        'INR' => '₹',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        default => $proposal->currency_symbol ?? 'INR'
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
<div class="proposal-page-container"> {{-- Overall page wrapper --}}

    {{-- ── Sticky Action Bar ── --}}
    <div class="proposal-actions-container">
        @php $action_token = md5($proposal->client_email); @endphp

        @if(!empty($action_token))
            {{-- Download PDF --}}
            <a href="{{ route('proposal.download', ['id' => $proposal->id, 'token' => $action_token]) }}"
               class="btn btn-dark btn-sm">
                <i class='bx bxs-download'></i> Download PDF
            </a>

            {{-- Status-dependent buttons --}}
            @if($proposal->status == 'Sent')
                <form action="{{ route('proposal.decline', ['id' => $proposal->id, 'token' => $action_token]) }}"
                      method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class='bx bx-x-circle'></i> Decline
                    </button>
                </form>
                <button type="button" class="btn btn-success btn-sm"
                        data-bs-toggle="modal" data-bs-target="#signatureModal">
                    <i class='bx bx-check-circle'></i> Accept
                </button>

            @elseif($proposal->status == 'Accepted')
                <span class="btn btn-success btn-sm" style="pointer-events:none;">
                    <i class='bx bx-check-circle'></i> Accepted
                </span>

            @elseif($proposal->status == 'Declined')
                <span class="btn btn-secondary btn-sm" style="pointer-events:none;">
                    <i class='bx bx-x-circle'></i> Declined
                </span>

            @elseif($proposal->status == 'Expired')
                <span class="btn btn-danger btn-sm" style="pointer-events:none;">
                    <i class='bx bx-time'></i> Expired
                </span>
            @endif

        @else
            <p class="text-danger mb-0">Action links are currently unavailable.</p>
        @endif
    </div>

    <div class="proposal-document"> {{-- The visual proposal paper --}}

        {{-- Header: Logo, From/To Addresses --}}
        <div class="proposal-header row">
            <div class="col-md-7">
                @isset($proposal->companyImg)
                    <img src="{{ asset('assets/images/company/'.$proposal->companyImg) }}" alt="{{ $proposal->companyName ?? 'Company Logo' }}" class="logo"><br><br>
                @endisset
            </div>
            <div class="col-md-5 text-right">
                <h1 class="font-weight-bold h3 mt-3">QUOTATION</h1>
            </div>
            <div class="col-md-7 company-address">
                <h5>{{ $proposal->companyName ?? 'Your Company Name' }}</h5>
                <p>
                @if(!empty($proposal->companyAddress))
                    {!! nl2br(e($proposal->companyAddress)) !!}, 
                @endif
                
                @if(!empty($proposal->companyCity) || !empty($proposal->companyState) || !empty($proposal->companyZipCode) || !empty($proposal->companyCountry))
                    
                    {{ $proposal->companyCity ?? '' }}{{ !empty($proposal->companyCity) && (!empty($proposal->companyState) || !empty($proposal->companyZipCode) || !empty($proposal->companyCountry)) ? ',' : '' }}
                    {{ $proposal->companyState ?? '' }}{{ !empty($proposal->companyState) && (!empty($proposal->companyZipCode) || !empty($proposal->companyCountry)) ? ',' : '' }}<br>
                    {{ $proposal->companyZipCode ?? '' }}{{ !empty($proposal->companyZipCode) && !empty($proposal->companyCountry) ? ',' : '' }}
                    {{ $proposal->companyCountry ?? '' }}
                    
                @endif
                </p>
                
                @if(!empty($proposal->companyMob))
                    <p>+91-{{ $proposal->companyMob }}</p>
                @endif
                
                @if(!empty($proposal->companyEmail))
                    <p>{{ $proposal->companyEmail }}</p>
                @endif
                
                @if(!empty($proposal->gst))
                    <p>GST No.: {{ $proposal->gst }}</p>
                @endif
                
                @if(!empty($proposal->vat))
                    <p>Vat NO.: {{ $proposal->vat }}</p>
                @endif

            </div>
            <div class="col-md-5 client-address text-md-end">
                <h6>To</h6>
                <p><strong>{{ $proposal->client_name ?? '' }}</strong></p>
                @if(!empty($proposal->client_address) || !empty($proposal->client_city) || !empty($proposal->client_state) || !empty($proposal->client_zip) || !empty($proposal->client_country))
                    <p>
                        {!! nl2br(e($proposal->client_address)) !!},<br>
                        @if(!empty($proposal->client_city)){!! nl2br(e($proposal->client_city)) !!} @endif
                        @if(!empty($proposal->client_state)), {!! nl2br(e($proposal->client_state)) !!} @endif
                        @if(!empty($proposal->client_zip)), {!! nl2br(e($proposal->client_zip)) !!} @endif
                        @if(!empty($proposal->client_country)), {!! nl2br(e($proposal->client_country)) !!} @endif
                    </p>
                @endif

                <p>{{ $proposal->client_phone ?? '' }}
                <p>{{ $proposal->client_email ?? '' }}</p>
            </div>
        </div>

        <div class="row">
            <div class="proposal-meta-info col-md-7">
                <h3># PRO-{{ sprintf('%06d', $proposal->id ?? '') }}</h3> {{-- Format ID like example --}}
                <p>{{ $proposal->subject ?? 'Proposal Subject' }}</p>
            </div>
            
            <div class="proposal-meta-info col-md-5 text-md-end">
                <p><strong>Date:</strong> {{ $proposal->proposal_date ? date_format(date_create($proposal->proposal_date ?? null), 'd M, Y') : 'N/A' }}</p>
                <p><strong>Open Till:</strong> {{ $proposal->open_till ? date_format(date_create($proposal->open_till ?? null), 'd M, Y') : 'N/A' }}</p>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="table-responsive">
            <table class="table" style="width:100%;border-radius:5px;overflow:hidden;">
                <thead>
                    <tr>
                        <th scope="col" style="width: 5%;">#</th>
                        <th scope="col" style="width: 35%;">Item</th>
                        <th scope="col" style="width: 10%;" class="text-center">HSN</th>
                        <th scope="col" style="width: 10%;" class="text-end">Qty</th>
                        <th scope="col" style="width: 15%;" class="text-end">Rate</th>
                        <th scope="col" style="width: 10%;" class="text-end">Tax</th>
                        <th scope="col" style="width: 15%;" class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proposalItems ?? [] as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ optional($item)->item_name ?? 'Item Name' }}</strong>
                            <div class="item-description">{!! nl2br(e(optional($item)->description ?? '')) !!}</div>
                        </td>
                        <td class="text-center">{{ optional($item)->hsn ?? '--' }}</td>
                        <td class="text-end">{{ optional($item)->quantity ?? 1 }}</td>
                        <td class="text-end">{{ number_format(optional($item)->rate ?? 0, 2) }}</td>
                        <td class="text-end">
                            @if(!empty((optional($item)->cgst_percent ?? 0)*100)) CGST {{ (optional($item)->cgst_percent ?? 0)*100 }}%<br> @endif
                            @if(!empty((optional($item)->sgst_percent ?? 0)*100)) SGST {{ (optional($item)->sgst_percent ?? 0)*100 }}%<br> @endif
                            @if(!empty((optional($item)->igst_percent ?? 0)*100)) IGST {{ (optional($item)->igst_percent ?? 0)*100 }}%<br> @endif
                            @if(!empty((optional($item)->vat_percent ?? 0)*100)) VAT {{ (optional($item)->vat_percent ?? 0)*100 }}% @endif
                        </td>
                        <td class="text-end">{{ number_format(optional($item)->amount ?? (optional($item)->quantity ?? 1) * (optional($item)->rate ?? 0), 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No items listed.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Totals Section --}}
        <div class="row totals-section">
            <div class="col-12">
                <table class="totals-table">
                    <tbody>
                        <tr>
                            <td>Sub Total:</td>
                            <td>{{ $symbol }}{{ number_format($proposal->sub_total ?? 0, 2) }}</td>
                        </tr>
                    
                        @if(isset($proposal->discount_percentage) && $proposal->discount_percentage > 0 && $proposal->discount_type == 'before-tax')
                        <tr>
                            <td>Discount:</td>
                            <td class="text-danger">-{{ $symbol }}{{ number_format($proposal->discount_amount_calculated, 2) }}</td>
                        </tr>
                        @endif
                    
                        @if(isset($proposal->cgst_total) && $proposal->cgst_total > 0)
                        <tr>
                            <td>CGST:</td>
                            <td>{{ $symbol }}{{ number_format($proposal->cgst_total, 2) }}</td>
                        </tr>
                        @endif
                    
                        @if(isset($proposal->sgst_total) && $proposal->sgst_total > 0)
                        <tr>
                            <td>SGST:</td>
                            <td>{{ $symbol }}{{ number_format($proposal->sgst_total, 2) }}</td>
                        </tr>
                        @endif
                    
                        @if(isset($proposal->igst_total) && $proposal->igst_total > 0)
                        <tr>
                            <td>IGST:</td>
                            <td>{{ $symbol }}{{ number_format($proposal->igst_total, 2) }}</td>
                        </tr>
                        @endif
                    
                        @if(isset($proposal->vat_total) && $proposal->vat_total > 0)
                        <tr>
                            <td>Vat:</td>
                            <td>{{ $symbol }}{{ number_format($proposal->vat_total, 2) }}</td>
                        </tr>
                        @endif
                    
                        @if(isset($proposal->discount_percentage) && $proposal->discount_percentage > 0 && $proposal->discount_type == 'after-tax')
                        <tr>
                            <td>Discount:</td>
                            <td class="text-danger">-{{ $symbol }}{{ number_format($proposal->discount_amount_calculated, 2) }}</td>
                        </tr>
                        @endif
                    
                        @if(isset($proposal->adjustment_amount) && $proposal->adjustment_amount > 0)
                        <tr>
                            <td>Adjustment:</td>
                            <td class="text-danger">-{{ $symbol }}{{ number_format($proposal->adjustment_amount, 2) }}</td>
                        </tr>
                        @endif
                    
                        <tr class="grand-total">
                            <td>Total:</td>
                            <td>{{ $symbol }}{{ number_format($proposal->grand_total ?? 0, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
                <h3 class="text-center h6 font-weight-bold mt-3">{{ ucwords(amountToWords($proposal->grand_total ?? 0)) }}</h3>
            </div>
        </div>

        <div class="text-section" id="company-info">
            <p>{!! $proposal->notes ?? 'Provide details about your company here. This could come from proposal settings or a specific field.' !!}</p>
        </div>

        {{-- Signature Section --}}
        <div class="signature-section">
            <p>Authorized Signature</p>
            <img src="{{ asset('assets/images/signs/'.(Auth::User()->imgsign ?? 'default.png')) }}" style="height: 90px;" />
            <div class="signature-line"></div>
        </div>

    </div> {{-- End proposal-document --}}

</div> {{-- End proposal-page-container --}}

<!-- Signature Modal -->
<div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="signatureForm" action="{{ route('proposal.accept', ['id' => $proposal->id, 'token' => $action_token]) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="signatureModalLabel">Signature & Confirmation Of Identity</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>First Name</label>
            <input type="text" class="form-control" name="first_name" required>
          </div>
          <div class="mb-3">
            <label>Last Name</label>
            <input type="text" class="form-control" name="last_name" required>
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label>Signature</label>
            <div class="border p-2">
              <canvas id="signature-pad" width="400" height="150" style="border:1px solid #ccc;"></canvas>
            </div>
            <input type="hidden" name="signature_data" id="signature-data">
            <div class="mt-2">
              <button type="button" class="btn btn-sm btn-secondary" id="clear-signature">Clear</button>
            </div>
          </div>
          <p class="text-muted small">
            By clicking on "Sign", I consent to be legally bound by this electronic representation of my signature.
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Sign</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Bootstrap JS Bundle --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>
<script>
  // Reference the canvas element
  const canvas = document.getElementById('signature-pad');

  // Function to adjust canvas size based on its CSS width
  function resizeCanvas() {
    // Get the device pixel ratio for crisp drawing on high-res screens.
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    // Get the current canvas CSS width
    const width = canvas.offsetWidth;
    // Define a fixed height in CSS pixels (150px in this example)
    const height = 150;
    // Set the canvas width and height attributes (these affect the drawing surface)
    canvas.width = width * ratio;
    canvas.height = height * ratio;
    // Scale the drawing context so that 1 unit in the canvas corresponds to 1 CSS pixel.
    canvas.getContext("2d").scale(ratio, ratio);
  }

  // Initially resize the canvas when the modal shows up.
  const signatureModal = document.getElementById('signatureModal');
  signatureModal.addEventListener('shown.bs.modal', resizeCanvas);

  // Also call resizeCanvas on window resize for responsiveness.
  window.addEventListener('resize', resizeCanvas);

  // Initialize SignaturePad
  const signaturePad = new SignaturePad(canvas);

  // Clear button functionality
  document.getElementById('clear-signature').addEventListener('click', function () {
    signaturePad.clear();
  });

  // On form submit, capture signature as base64 image
  document.getElementById('signatureForm').addEventListener('submit', function (e) {
    if (signaturePad.isEmpty()) {
      alert("Please provide your signature.");
      e.preventDefault();
      return;
    }
    // Convert the signature to a base64 PNG image string
    const signatureData = signaturePad.toDataURL('image/png');
    document.getElementById('signature-data').value = signatureData;
  });
</script>

</body>
</html>
