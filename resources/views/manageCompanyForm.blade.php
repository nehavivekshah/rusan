{{-- Select2 --}}
@once
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
/* ───────────────────────────────────────────
   Company Modal — Premium UI
   Reusing styles from manageContractForm.blade.php
──────────────────────────────────────────── */
.cf-wrap * { box-sizing: border-box; font-family: inherit; }

.cf-section-title {
    font-size: .72rem;
    font-weight: 700;
    color: #163f7a;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin: 18px 0 12px;
    padding-bottom: 4px;
    border-bottom: 1.5px solid rgba(22, 63, 122,.12);
}
.cf-section-title:first-child { margin-top: 0; }

.cf-field { display: flex; flex-direction: column; }
.cf-field label {
    font-size: .78rem;
    color: #5f6368;
    font-weight: 400;
    margin-bottom: 5px;
    text-transform: none;
}
.cf-field label .req { color: #ea4335; }

.cf-input-box {
    display: flex;
    align-items: center;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
    height: 42px;
}
.cf-input-box:focus-within {
    border-color: #163f7a;
    box-shadow: 0 0 0 3px rgba(22, 63, 122,.08);
}
.cf-input-box .cf-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 100%;
    flex-shrink: 0;
    color: #163f7a;
    font-size: 1.05rem;
    border-right: 1.5px solid #e8eaed;
    background: #f8fdfd;
}
.cf-input-box input,
.cf-input-box select,
.cf-input-box textarea {
    flex: 1;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent;
    font-size: .875rem;
    color: #202124;
    padding: 0 10px;
    height: 100%;
}
.cf-input-box.cf-textarea-box {
    height: auto;
    align-items: flex-start;
}
.cf-input-box.cf-textarea-box textarea {
    height: auto;
    padding: 10px;
    resize: none;
    width: 100%;
}

.cf-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: linear-gradient(135deg, #005757, #163f7a);
    border-radius: 16px 16px 0 0;
}
.cf-modal-header-title { font-size: .975rem; font-weight: 700; color: #fff; margin: 0; }
.cf-modal-header-sub   { font-size: .73rem; color: rgba(255,255,255,.72); margin: 0; }
.cf-modal-header .btn-close { filter: invert(1); opacity:.8; }

.cf-modal-footer {
    padding: 12px 20px;
    border-top: 1px solid #e8eaed;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    background: #fff;
    border-radius: 0 0 16px 16px;
}
.cf-btn-cancel {
    font-size: .85rem; padding: 8px 20px; border-radius: 8px;
    border: 1.5px solid #d1d5db; background: #fff; color: #5f6368;
    cursor: pointer; transition: background .15s;
}
.cf-btn-cancel:hover { background: #f5f5f5; }
.cf-btn-save {
    font-size: .85rem; font-weight: 600; padding: 8px 22px; border-radius: 8px;
    border: none; background: #163f7a; color: #fff;
    cursor: pointer; transition: background .15s;
    display: flex; align-items: center; gap: 5px;
}
.cf-btn-save:hover { background: #004e4e; }

.cf-logo-preview {
    width: 42px; height: 42px; border-radius: 10px;
    background: #fff; border: 1px solid #e8eaed;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; padding: 4px; flex-shrink: 0;
}
.cf-logo-preview img { width: 100%; height: 100%; object-fit: contain; }
</style>
@endonce

@php
    $isEdit = !empty($company->id);
    $rates = !empty($company->tax) ? explode(',', $company->tax) : [];
    $rates = array_pad($rates, 4, '');
    $bank_details = json_decode(($company->bank_details ?? ''), true);
@endphp

{{-- ── Header ── --}}
<div class="cf-modal-header">
    <div>
        <p class="cf-modal-header-title">
            <i class="bx {{ $isEdit ? 'bx-edit-alt' : 'bx-plus-circle' }} me-1"></i>
            {{ $isEdit ? 'Edit Company Details' : 'Add New Company' }}
        </p>
        <p class="cf-modal-header-sub">Manage organizational profile and settings</p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

{{-- ── Body ── --}}
<div class="modal-body px-4 py-3 cf-wrap" style="max-height:72vh; overflow-y:auto; background:#f4fbfb;">
    <form action="/manage-company" method="post" id="companyModalForm" enctype="multipart/form-data">
        @csrf
        @if($isEdit)
            <input type="hidden" name="id" value="{{ $company->id }}">
        @endif

        {{-- ─ BASIC INFORMATION ─ --}}
        <div class="cf-section-title">Basic Information</div>
        <div class="row g-3">
            <div class="col-md-7 col-sm-12 cf-field">
                <label>Company Name <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-building"></i></span>
                    <input type="text" name="name" value="{{ $company->name ?? '' }}" placeholder="Enter full company name" required>
                </div>
            </div>
            <div class="col-md-5 col-sm-12 cf-field">
                <label>Industry</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-category"></i></span>
                    <input type="text" name="industry" value="{{ $company->industry ?? '' }}" placeholder="e.g. IT Services">
                </div>
            </div>
            
            <div class="col-md-6 cf-field">
                <label>Email Address</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-envelope"></i></span>
                    <input type="email" name="email" value="{{ $company->email ?? '' }}" placeholder="company@example.com">
                </div>
            </div>
            <div class="col-md-6 cf-field">
                <label>Mobile No.</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-phone"></i></span>
                    <input type="tel" name="mob" value="{{ $company->mob ?? '' }}" placeholder="Enter phone number">
                </div>
            </div>
        </div>

        {{-- ─ LOGOS ─ --}}
        <div class="cf-section-title mt-4">Branding & Logos</div>
        <div class="row g-3">
            <div class="col-md-6 cf-field">
                <label>Main Logo</label>
                <div class="d-flex align-items-center gap-2">
                    <div class="cf-logo-preview">
                        @if(!empty($company->logo))
                            <img src="{{ asset('assets/images/company/logos/'.$company->logo) }}" id="mainLogoPreview">
                        @else
                            <img src="" id="mainLogoPreview" style="display:none;">
                            <i class="bx bx-image" id="mainLogoIcon" style="color:#dadce0;font-size:1.5rem;"></i>
                        @endif
                    </div>
                    <div class="cf-input-box flex-grow-1">
                        <input type="file" name="logo" id="logoInput" style="padding-top:8px;" onchange="previewImage(this, 'mainLogoPreview', 'mainLogoIcon')">
                    </div>
                </div>
            </div>
            <div class="col-md-6 cf-field">
                <label>PDF Logo</label>
                <div class="d-flex align-items-center gap-2">
                    <div class="cf-logo-preview">
                        @if(!empty($company->pdf_logo))
                            <img src="{{ asset('assets/images/company/'.$company->pdf_logo) }}" id="pdfLogoPreview">
                        @else
                            <img src="" id="pdfLogoPreview" style="display:none;">
                            <i class="bx bx-file-blank" id="pdfLogoIcon" style="color:#dadce0;font-size:1.5rem;"></i>
                        @endif
                    </div>
                    <div class="cf-input-box flex-grow-1">
                        <input type="file" name="pdf_logo" id="pdfLogoInput" style="padding-top:8px;" onchange="previewImage(this, 'pdfLogoPreview', 'pdfLogoIcon')">
                    </div>
                </div>
            </div>
        </div>

        {{-- ─ TAX & RATES ─ --}}
        <div class="cf-section-title mt-4">Taxation Details</div>
        <div class="row g-3">
            <div class="col-md-6 cf-field">
                <label>GST Number</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-hash"></i></span>
                    <input type="text" id="modal_gst" name="gst" value="{{ $company->gst ?? '' }}" placeholder="Enter GSTIN">
                </div>
            </div>
            <div class="col-md-6 cf-field">
                <label>VAT Number</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-hash"></i></span>
                    <input type="text" id="modal_vat" name="vat" value="{{ $company->vat ?? '' }}" placeholder="Enter VAT No.">
                </div>
            </div>

            <div id="modal_gst_rates" class="col-12" style="display:none;">
                <div class="row g-2">
                    @foreach(['CGST', 'SGST', 'IGST'] as $i => $label)
                        <div class="col-md-4 cf-field">
                            <label>{{ $label }} Rate (%)</label>
                            <div class="cf-input-box">
                                <input type="number" step="0.01" name="tax_rates[]" value="{{ $rates[$i] }}" placeholder="0.00">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div id="modal_vat_rate" class="col-md-4 cf-field" style="display:none;">
                <label>VAT Rate (%)</label>
                <div class="cf-input-box">
                    <input type="number" step="0.01" name="tax_rates[]" value="{{ $rates[3] }}" placeholder="0.00">
                </div>
            </div>
        </div>

        {{-- ─ ADDRESS ─ --}}
        <div class="cf-section-title mt-4">Address</div>
        <div class="row g-3">
            <div class="col-12 cf-field">
                <label>Street Address</label>
                <div class="cf-input-box cf-textarea-box">
                    <textarea name="address" rows="2" placeholder="Full address...">{{ $company->address ?? '' }}</textarea>
                </div>
            </div>
            <div class="col-md-6 cf-field">
                <label>City</label>
                <div class="cf-input-box"><input type="text" name="city" value="{{ $company->city ?? '' }}" placeholder="City"></div>
            </div>
            <div class="col-md-6 cf-field">
                <label>State</label>
                <div class="cf-input-box"><input type="text" name="state" value="{{ $company->state ?? '' }}" placeholder="State"></div>
            </div>
            <div class="col-md-6 cf-field">
                <label>Zip Code</label>
                <div class="cf-input-box"><input type="text" name="zipcode" value="{{ $company->zipcode ?? '' }}" placeholder="Zip Code"></div>
            </div>
            <div class="col-md-6 cf-field">
                <label>Country</label>
                <div class="cf-input-box"><input type="text" name="country" value="{{ $company->country ?? '' }}" placeholder="Country"></div>
            </div>
        </div>

        {{-- ─ BANK DETAILS ─ --}}
        <div class="cf-section-title mt-4">Bank Details</div>
        <div class="row g-3">
            <div class="col-md-6 cf-field">
                <label>Bank Name</label>
                <div class="cf-input-box"><input type="text" name="bank_details[]" value="{{ $bank_details[0] ?? '' }}" placeholder="Bank Name"></div>
            </div>
            <div class="col-md-6 cf-field">
                <label>Account Name</label>
                <div class="cf-input-box"><input type="text" name="bank_details[]" value="{{ $bank_details[1] ?? '' }}" placeholder="Account Name"></div>
            </div>
            <div class="col-md-6 cf-field">
                <label>Account No.</label>
                <div class="cf-input-box"><input type="text" name="bank_details[]" value="{{ $bank_details[2] ?? '' }}" placeholder="Account Number"></div>
            </div>
            <div class="col-md-6 cf-field">
                <label>IFSC Code</label>
                <div class="cf-input-box"><input type="text" name="bank_details[]" value="{{ $bank_details[3] ?? '' }}" placeholder="IFSC"></div>
            </div>
            <div class="col-12 cf-field">
                <label>UPI ID</label>
                <div class="cf-input-box"><input type="text" name="bank_details[]" value="{{ $bank_details[4] ?? '' }}" placeholder="e.g. company@upi"></div>
            </div>
        </div>

        @if(Auth::user()->role == 'master')
            <div class="cf-section-title mt-4">Subscription Plan</div>
            <div class="cf-field">
                <div class="d-flex flex-wrap gap-4">
                    @forelse($plans as $plan)
                        <label class="d-flex align-items-center gap-2 pointer-cursor" style="font-size:0.85rem;">
                            <input type="radio" name="subscription" value="{{ strtolower($plan->name) }}" {{ strtolower($company->plan ?? 'standard') == strtolower($plan->name) ? 'checked' : '' }} style="accent-color:#163f7a;">
                            <div>
                                <span class="fw-600">{{ $plan->name }}</span>
                                <span class="text-muted ms-1" style="font-size:0.75rem;">(₹{{ number_format($plan->price, 2) }}/mo)</span>
                            </div>
                        </label>
                    @empty
                        <div class="small text-muted border rounded p-2 px-3 bg-light">
                            <i class="bx bx-info-circle me-1"></i> No plans available. <a href="/subscriptions" class="text-indigo fw-bold">Create one now</a>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

    </form>
</div>

{{-- ── Footer ── --}}
<div class="cf-modal-footer">
    <button type="button" class="cf-btn-cancel" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" form="companyModalForm" class="cf-btn-save">
        <i class="bx bx-check"></i>
        {{ $isEdit ? 'Update Company' : 'Save Company' }}
    </button>
</div>

<script>
(function() {
    window.previewImage = function(input, previewId, iconId) {
        const preview = document.getElementById(previewId);
        const icon = document.getElementById(iconId);
        const file = input.files[0];
        const reader = new FileReader();

        reader.onloadend = function() {
            preview.src = reader.result;
            preview.style.display = 'block';
            if(icon) icon.style.display = 'none';
        }

        if (file) {
            reader.readAsDataURL(file);
        }
    };

    const gstInput = document.getElementById('modal_gst');
    const vatInput = document.getElementById('modal_vat');
    const gstRow   = document.getElementById('modal_gst_rates');
    const vatRow   = document.getElementById('modal_vat_rate');

    function refreshTaxVisibility() {
        if(gstRow) gstRow.style.display = (gstInput && gstInput.value.trim() !== '') ? 'block' : 'none';
        if(vatRow) vatRow.style.display = (vatInput && vatInput.value.trim() !== '') ? 'block' : 'none';
        
        // Disable inputs of hidden rows to keep POST clean
        if(gstRow) {
            gstRow.querySelectorAll('input').forEach(i => i.disabled = (gstRow.style.display === 'none'));
        }
        if(vatRow) {
            vatRow.querySelectorAll('input').forEach(i => i.disabled = (vatRow.style.display === 'none'));
        }
    }

    if(gstInput) gstInput.addEventListener('input', refreshTaxVisibility);
    if(vatInput) vatInput.addEventListener('input', refreshTaxVisibility);
    
    refreshTaxVisibility();
})();
</script>
