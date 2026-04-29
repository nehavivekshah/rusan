{{-- Company View — Read-only Partial --}}
<style>
.rv-wrap { box-sizing: border-box; font-family: inherit; }
.rv-section-title {
    font-size: .72rem;
    font-weight: 700;
    color: #163f7a;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin: 20px 0 10px;
    padding-bottom: 4px;
    border-bottom: 1.5px solid rgba(22, 63, 122,.1);
}
.rv-section-title:first-child { margin-top: 0; }

.rv-data-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f1f3f4;
}
.rv-data-row:last-child { border-bottom: none; }
.rv-label { font-size: 0.82rem; color: #80868b; }
.rv-value { font-size: 0.85rem; color: #202124; font-weight: 500; text-align: right; }

.rv-header-gradient {
    background: linear-gradient(135deg, #005757, #163f7a);
    padding: 24px 20px;
    border-radius: 16px 16px 0 0;
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
}
.rv-close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    color: rgba(255,255,255,0.8);
    font-size: 1.5rem;
    cursor: pointer;
    background: transparent;
    border: none;
}
.rv-close-btn:hover { color: #fff; }

.rv-profile-img {
    width: 64px; height: 64px; border-radius: 14px;
    background: #fff; display: flex; align-items: center; justify-content: center;
    overflow: hidden; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.rv-profile-img img { width: 100%; height: 100%; object-fit: contain; padding: 6px; }

.rv-title-group { flex: 1; }
.rv-company-name { font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0; }
.rv-company-sub { font-size: 0.75rem; color: rgba(255,255,255,0.7); margin-top: 2px; }

.rv-footer {
    padding: 16px 20px;
    background: #fff;
    border-top: 1px solid #e8eaed;
    border-radius: 0 0 16px 16px;
    display: flex;
    justify-content: flex-end;
}
</style>

@php
    $bank_details = json_decode(($company->bank_details ?? ''), true);
    $rates = !empty($company->tax) ? explode(',', $company->tax) : [];
@endphp

<div class="rv-header-gradient">
    <button type="button" class="rv-close-btn" data-bs-dismiss="modal">&times;</button>
    <div class="rv-profile-img">
        @if($company->logo)
            <img src="{{ asset('assets/images/company/logos/' . $company->logo) }}" alt="">
        @else
            <i class="bx bx-building" style="font-size:2rem; color:#163f7a;"></i>
        @endif
    </div>
    <div class="rv-title-group">
        <h3 class="rv-company-name">{{ $company->name }}</h3>
        <div class="rv-company-sub">
            <i class="bx bx-purchase-tag-alt me-1"></i> {{ $company->industry ?? 'Business Services' }}
            <span class="mx-2">|</span>
            <i class="bx bx-map me-1"></i> {{ $company->city }}, {{ $company->state }}
        </div>
    </div>
</div>

<div class="modal-body p-4 rv-wrap" style="background:#f8f9fa;">
    
    <div class="row g-4">
        {{-- Contact & Account --}}
        <div class="col-md-6">
            <div class="dash-card h-100 p-3" style="background:#fff; border-radius:12px; border:1px solid #e8eaed;">
                <div class="rv-section-title">Contact Details</div>
                <div class="rv-data-row">
                    <span class="rv-label">Email</span>
                    <span class="rv-value text-primary">{{ $company->email ?? '—' }}</span>
                </div>
                <div class="rv-data-row">
                    <span class="rv-label">Phone</span>
                    <span class="rv-value">{{ $company->mob ?? '—' }}</span>
                </div>
                <div class="rv-data-row">
                    <span class="rv-label">Subscription</span>
                    <span class="rv-value">
                        <span class="badge" style="background:rgba(22, 63, 122,0.1); color:#163f7a; text-transform:capitalize;">{{ $company->plan ?? 'Standard' }}</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Tax & Registration --}}
        <div class="col-md-6">
            <div class="dash-card h-100 p-3" style="background:#fff; border-radius:12px; border:1px solid #e8eaed;">
                <div class="rv-section-title">Taxation</div>
                <div class="rv-data-row">
                    <span class="rv-label">GSTIN</span>
                    <span class="rv-value">{{ $company->gst ?? '—' }}</span>
                </div>
                <div class="rv-data-row">
                    <span class="rv-label">VAT No.</span>
                    <span class="rv-value">{{ $company->vat ?? '—' }}</span>
                </div>
                @if(!empty($company->gst))
                <div class="mt-2 p-2 bg-light rounded" style="font-size:0.75rem;">
                    <span class="text-muted">Rates:</span> 
                    CGST: <b>{{ $rates[0] ?? 0 }}%</b>, 
                    SGST: <b>{{ $rates[1] ?? 0 }}%</b>, 
                    IGST: <b>{{ $rates[2] ?? 0 }}%</b>
                </div>
                @endif
            </div>
        </div>

        {{-- Bank Account --}}
        <div class="col-12">
            <div class="dash-card p-3" style="background:#fff; border-radius:12px; border:1px solid #e8eaed;">
                <div class="rv-section-title">Bank Account Info</div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="rv-data-row">
                            <span class="rv-label">Bank Name</span>
                            <span class="rv-value">{{ $bank_details[0] ?? '—' }}</span>
                        </div>
                        <div class="rv-data-row">
                            <span class="rv-label">Account No.</span>
                            <span class="rv-value">{{ $bank_details[2] ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="rv-data-row">
                            <span class="rv-label">Account Name</span>
                            <span class="rv-value">{{ $bank_details[1] ?? '—' }}</span>
                        </div>
                        <div class="rv-data-row">
                            <span class="rv-label">IFSC Code</span>
                            <span class="rv-value">{{ $bank_details[3] ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                @if(!empty($bank_details[4]))
                <div class="rv-data-row border-top mt-1 pt-2">
                    <span class="rv-label">UPI ID</span>
                    <span class="rv-value"><i class="bx bx-mobile-alt me-1"></i>{{ $bank_details[4] }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Address --}}
        <div class="col-12">
            <div class="dash-card p-3" style="background:#fff; border-radius:12px; border:1px solid #e8eaed;">
                <div class="rv-section-title">Registered Address</div>
                <div class="d-flex align-items-start gap-3 p-2">
                    <div class="p-2 rounded bg-light" style="color:#163f7a;">
                        <i class="bx bx-map-pin" style="font-size:1.5rem;"></i>
                    </div>
                    <div>
                        <div class="fw-500">{{ $company->address ?? 'No address provided' }}</div>
                        <div class="text-muted small">
                            {{ $company->city }}{{ $company->state ? ', '.$company->state : '' }} {{ $company->zipcode }} <br>
                            {{ $company->country }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="rv-footer">
    <button type="button" class="lb-btn lb-btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
