@extends('layout')
@section('title', Request::segment(1) === 'my-company' ? 'My Company - eseCRM' : 'Manage Company - eseCRM')

@section('content')
@php
    $sessionroles = session('roles');
    $roleArray    = explode(',', ($sessionroles->permissions ?? ''));

    $rates = !empty($company->tax) ? explode(',', $company->tax) : [];
    $rates = array_pad($rates, 4, '');

    $bank = json_decode(($company->bank_details ?? ''), true) ?? [];
    $bank = array_pad($bank, 5, '');

    $isMyCompany = Request::segment(1) === 'my-company';
    $isEdit      = !empty($company->id);
    $formAction  = $isMyCompany ? '/my-company' : '/manage-company';
@endphp

<section class="task__section">
    @include('inc.header', ['title' => $isMyCompany ? 'My Company' : ($isEdit ? 'Edit Company' : 'Add Company')])

    <div class="dash-container">

        {{-- ── Mini Info Banner (edit mode only) ── --}}
        @if($isEdit)
        <div class="mc-banner mb-4">
            <div class="mc-banner-logo">
                @if(!empty($company->logo))
                    <img src="{{ asset('assets/images/company/logos/' . $company->logo) }}"
                         alt="{{ $company->name }}"
                         style="width:100%; height:100%; object-fit:contain; border-radius:12px;">
                @else
                    {{ strtoupper(substr($company->name ?? 'C', 0, 1)) }}
                @endif
            </div>
            <div class="mc-banner-info">
                <div class="mc-banner-name">{{ $company->name }}</div>
                <div class="mc-banner-meta">
                    @if($company->email)<span><i class="bx bx-envelope"></i> {{ $company->email }}</span>@endif
                    @if($company->mob)<span><i class="bx bx-phone"></i> {{ $company->mob }}</span>@endif
                    @if($company->city)<span><i class="bx bx-map"></i> {{ $company->city }}{{ $company->state ? ', '.$company->state : '' }}</span>@endif
                </div>
            </div>
            <div class="mc-banner-badges">
                @if($company->gst)
                    <span class="mc-badge"><i class="bx bx-file-blank"></i> GST Registered</span>
                @endif
                @if($company->plan)
                    <span class="mc-badge mc-badge-blue"><i class="bx bx-crown"></i> {{ ucfirst($company->plan) }} Plan</span>
                @endif
            </div>
        </div>
        @endif

        {{-- ── Form Card ── --}}
        <div class="dash-card mc-form-card">

            {{-- Card Header --}}
            <div class="mc-form-header">
                <div>
                    <p class="mc-form-header-title">
                        <i class="bx {{ $isEdit ? 'bx-edit-alt' : 'bx-plus-circle' }} me-1"></i>
                        {{ $isMyCompany ? 'Edit My Company Details' : ($isEdit ? 'Edit Company' : 'Add New Company') }}
                    </p>
                    <p class="mc-form-header-sub">
                        {{ $isMyCompany ? 'Update your organisation profile, tax info, and banking details' : 'Fill in the details below to manage the company record' }}
                    </p>
                </div>
                @if(!$isMyCompany)
                    <a href="/companies" class="mc-back-btn">
                        <i class="bx bx-arrow-back"></i> Back
                    </a>
                @endif
            </div>

            {{-- Card Body --}}
            <div class="mc-form-body">
                <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" id="mcForm">
                    @csrf
                    @if($isEdit && !$isMyCompany)
                        <input type="hidden" name="id" value="{{ $company->id }}">
                    @endif

                    {{-- ── LOGOS ── --}}
                    <div class="mc-section-title">Branding &amp; Logos</div>
                    <div class="row g-3">
                        <div class="col-md-6 mc-field">
                            <label>Main Logo <small class="text-muted">(used in app header)</small></label>
                            <div class="mc-file-box">
                                @if(!empty($company->logo))
                                    <div class="mc-file-preview">
                                        <img src="{{ asset('assets/images/company/logos/'.$company->logo) }}"
                                             alt="logo" style="height:36px; object-fit:contain; border-radius:6px;">
                                    </div>
                                @else
                                    <span class="mc-file-icon"><i class="bx bx-image"></i></span>
                                @endif
                                <input type="file" name="logo" accept="image/*" class="mc-file-input">
                                <span class="mc-file-label">{{ !empty($company->logo) ? 'Change logo…' : 'Choose logo…' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mc-field">
                            <label>PDF / Proposal Logo <small class="text-muted">(appears on invoices &amp; quotations)</small></label>
                            <div class="mc-file-box">
                                @if(!empty($company->img))
                                    <div class="mc-file-preview">
                                        <img src="{{ asset('assets/images/company/'.$company->img) }}"
                                             alt="pdf_logo" style="height:36px; object-fit:contain; border-radius:6px;">
                                    </div>
                                @else
                                    <span class="mc-file-icon"><i class="bx bx-file-blank"></i></span>
                                @endif
                                <input type="file" name="img" accept="image/*" class="mc-file-input">
                                <span class="mc-file-label">{{ !empty($company->img) ? 'Change PDF logo…' : 'Choose PDF logo…' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- ── COMPANY INFORMATION ── --}}
                    <div class="mc-section-title">Company Information</div>
                    <div class="row g-3">
                        <div class="col-md-6 mc-field">
                            <label>Company Name <span class="req">*</span></label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx bx-building"></i></span>
                                <input type="text" name="name" required placeholder="e.g. Acme Private Limited"
                                       value="{{ old('name', $company->name ?? '') }}">
                            </div>
                            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-3 mc-field">
                            <label>Mobile No.</label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx bx-phone"></i></span>
                                <input type="tel" name="mob" placeholder="10-digit number"
                                       value="{{ old('mob', $company->mob ?? '') }}">
                            </div>
                        </div>

                        <div class="col-md-3 mc-field">
                            <label>Email ID</label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx bx-envelope"></i></span>
                                <input type="email" name="email" placeholder="company@example.com"
                                       value="{{ old('email', $company->email ?? '') }}">
                            </div>
                        </div>

                        <div class="col-md-6 mc-field">
                            <label>Industry / Sector</label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx bx-briefcase"></i></span>
                                <input type="text" name="industry" placeholder="e.g. IT Services, Manufacturing…"
                                       value="{{ old('industry', $company->industry ?? '') }}">
                            </div>
                        </div>

                        <div class="col-md-6 mc-field">
                            <label>Website</label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx bx-globe"></i></span>
                                <input type="url" name="website" placeholder="https://yourcompany.com"
                                       value="{{ old('website', $company->website ?? '') }}">
                            </div>
                        </div>
                    </div>

                    {{-- ── ADDRESS ── --}}
                    <div class="mc-section-title">Address</div>
                    <div class="row g-3">
                        <div class="col-12 mc-field">
                            <label>Street Address</label>
                            <div class="mc-input-box mc-textarea-box">
                                <span class="mc-icon" style="padding-top:10px; align-self:flex-start;"><i class="bx bx-map"></i></span>
                                <textarea name="address" rows="3"
                                          placeholder="Building, Street, Area…">{{ old('address', $company->address ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-3 mc-field">
                            <label>City</label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx bx-building-house"></i></span>
                                <input type="text" name="city" placeholder="City"
                                       value="{{ old('city', $company->city ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-3 mc-field">
                            <label>State</label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx bx-map-pin"></i></span>
                                <input type="text" name="state" placeholder="State"
                                       value="{{ old('state', $company->state ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-3 mc-field">
                            <label>Zip / PIN Code</label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx bx-hash"></i></span>
                                <input type="text" name="zipcode" placeholder="e.g. 400001"
                                       value="{{ old('zipcode', $company->zipcode ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-3 mc-field">
                            <label>Country</label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx bx-flag"></i></span>
                                <input type="text" name="country" placeholder="e.g. India"
                                       value="{{ old('country', $company->country ?? '') }}">
                            </div>
                        </div>
                    </div>

                    {{-- ── TAX INFORMATION ── --}}
                    <div class="mc-section-title">Tax &amp; Registration</div>
                    <div class="row g-3">
                        <div class="col-md-4 mc-field">
                            <label>GST Number</label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx bx-receipt"></i></span>
                                <input type="text" id="gst" name="gst" placeholder="e.g. 22AAAAA0000A1Z5"
                                       value="{{ old('gst', $company->gst ?? '') }}"
                                       style="text-transform:uppercase;">
                            </div>
                        </div>

                        @foreach(['CGST','SGST','IGST'] as $i => $label)
                        <div class="col-md-2 mc-field" data-tax="{{ $label }}">
                            <label>{{ $label }} Rate (%)</label>
                            <div class="mc-input-box">
                                <span class="mc-icon" style="font-size:0.75rem; font-weight:700; color:#006666;">%</span>
                                <input type="number" step="0.01" min="0" max="100"
                                       name="tax_rates[]"
                                       value="{{ $rates[$i] }}"
                                       placeholder="0.00">
                            </div>
                        </div>
                        @endforeach

                        <div class="col-md-2 mc-field">
                            <label>VAT Number</label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx bx-file-blank"></i></span>
                                <input type="text" id="vat" name="vat" placeholder="VAT No."
                                       value="{{ old('vat', $company->vat ?? '') }}">
                            </div>
                        </div>

                        <div class="col-md-2 mc-field" data-tax="VAT">
                            <label>VAT Rate (%)</label>
                            <div class="mc-input-box">
                                <span class="mc-icon" style="font-size:0.75rem; font-weight:700; color:#006666;">%</span>
                                <input type="number" step="0.01" min="0" max="100"
                                       name="tax_rates[]"
                                       value="{{ $rates[3] }}"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    {{-- ── BANKING DETAILS ── --}}
                    <div class="mc-section-title">Banking Details</div>
                    <div class="row g-3">
                        @php
                            $bankFields = [
                                ['Bank Name',    'bank_name',    'bx-bank',          'e.g. State Bank of India', $bank[0]],
                                ['Account Name', 'account_name', 'bx-user',          'Account holder name',       $bank[1]],
                                ['Account No.',  'account_no',   'bx-credit-card',   'e.g. 1234567890',           $bank[2]],
                                ['IFSC Code',    'ifsc',         'bx-code-alt',      'e.g. SBIN0001234',          $bank[3]],
                                ['UPI ID',       'upi',          'bx-qr',            'e.g. name@upi',             $bank[4]],
                            ];
                        @endphp
                        @foreach($bankFields as [$lbl, $id, $icon, $ph, $val])
                        <div class="col-md-4 mc-field">
                            <label>{{ $lbl }}</label>
                            <div class="mc-input-box">
                                <span class="mc-icon"><i class="bx {{ $icon }}"></i></span>
                                <input type="text" name="bank_details[]" id="{{ $id }}"
                                       placeholder="{{ $ph }}" value="{{ $val }}">
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- ── SUBSCRIPTION (master only) ── --}}
                    @if(Auth::user()->role == 'master')
                    @php $subscriptions = ['standard', 'premium', 'pro']; @endphp
                    <div class="mc-section-title">Subscription Plan</div>
                    <div class="row g-3 align-items-center">
                        @foreach($subscriptions as $sub)
                        <div class="col-auto">
                            <label class="mc-plan-option {{ ($sub == ($company->plan ?? '')) ? 'mc-plan-active' : '' }}">
                                <input type="radio" name="subscription" value="{{ $sub }}"
                                       {{ ($sub == ($company->plan ?? '')) ? 'checked' : '' }}>
                                <i class="bx {{ $sub === 'pro' ? 'bx-diamond' : ($sub === 'premium' ? 'bx-crown' : 'bx-star') }}"></i>
                                {{ ucfirst($sub) }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- ── FOOTER ── --}}
                    <div class="mc-form-footer mt-4">
                        @if(!$isMyCompany)
                            <a href="/companies" class="mc-btn-cancel">Cancel</a>
                        @endif
                        <button type="reset" class="mc-btn-cancel">Reset</button>
                        <button type="submit" class="mc-btn-save">
                            <i class="bx bx-check"></i>
                            {{ $isEdit ? 'Update Company' : 'Save Company' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</section>

<style>
/* ═══════════════════════════════════════
   My Company — Premium Form Page Styles
═══════════════════════════════════════ */
.dash-container { padding: 24px; }

/* ── Banner ── */
.mc-banner {
    display: flex; align-items: center; gap: 18px;
    background: linear-gradient(135deg, #005757, #007e7e);
    border-radius: 18px; padding: 20px 24px;
    color: #fff; flex-wrap: wrap;
}
.mc-banner-logo {
    width: 64px; height: 64px; border-radius: 16px;
    background: rgba(255,255,255,0.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; font-weight: 800; color: #fff;
    flex-shrink: 0; overflow: hidden;
}
.mc-banner-info { flex: 1; min-width: 0; }
.mc-banner-name { font-size: 1.2rem; font-weight: 800; }
.mc-banner-meta { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 5px; }
.mc-banner-meta span { font-size: 0.78rem; opacity: .85; display: flex; align-items: center; gap: 4px; }
.mc-banner-badges { display: flex; flex-wrap: wrap; gap: 8px; }
.mc-badge {
    background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);
    color: #fff; border-radius: 20px; padding: 4px 12px;
    font-size: 0.72rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 4px;
}
.mc-badge-blue { background: rgba(26,115,232,0.3); border-color: rgba(26,115,232,0.4); }

/* ── Form Card ── */
.mc-form-card { border-radius: 18px; border: 1px solid #e8eaed; overflow: hidden; }

/* ── Card Header ── */
.mc-form-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 28px;
    background: linear-gradient(135deg, #005757, #007e7e);
}
.mc-form-header-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
.mc-form-header-sub   { font-size: 0.74rem; color: rgba(255,255,255,.72); margin: 4px 0 0; }
.mc-back-btn {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
    color: #fff; border-radius: 10px; padding: 7px 14px;
    font-size: 0.82rem; font-weight: 600; text-decoration: none;
    transition: background 0.15s;
}
.mc-back-btn:hover { background: rgba(255,255,255,0.25); color: #fff; }

/* ── Body ── */
.mc-form-body { padding: 28px; background: #f4fbfb; }

/* ── Section Title ── */
.mc-section-title {
    font-size: 0.72rem; font-weight: 700; color: #006666;
    text-transform: uppercase; letter-spacing: .07em;
    margin: 24px 0 14px; padding-bottom: 5px;
    border-bottom: 1.5px solid rgba(0,102,102,.12);
}
.mc-section-title:first-child { margin-top: 0; }

/* ── Field ── */
.mc-field { display: flex; flex-direction: column; }
.mc-field label { font-size: 0.78rem; color: #5f6368; margin-bottom: 5px; font-weight: 500; }
.mc-field label .req { color: #ea4335; }

/* ── Input Box ── */
.mc-input-box {
    display: flex; align-items: center;
    border: 1.5px solid #d1d5db; border-radius: 10px;
    background: #fff; overflow: hidden;
    transition: border-color .15s, box-shadow .15s; height: 44px;
}
.mc-input-box:focus-within {
    border-color: #006666;
    box-shadow: 0 0 0 3px rgba(0,102,102,.08);
}
.mc-icon {
    display: flex; align-items: center; justify-content: center;
    width: 40px; height: 100%; flex-shrink: 0;
    color: #006666; font-size: 1.05rem;
    border-right: 1.5px solid #e8eaed; background: #f8fdfd;
}
.mc-input-box input,
.mc-input-box textarea {
    flex: 1; border: none !important; outline: none !important;
    box-shadow: none !important; background: transparent;
    font-size: 0.875rem; color: #202124; padding: 0 12px;
    height: 100%;
}
.mc-input-box input::placeholder,
.mc-input-box textarea::placeholder { color: #9aa0a6; }

.mc-textarea-box { height: auto; align-items: flex-start; }
.mc-textarea-box textarea { height: auto; padding: 10px 12px; resize: none; width: 100%; }

/* ── File Box ── */
.mc-file-box {
    display: flex; align-items: center; gap: 10px;
    border: 1.5px dashed #c8d0d6; border-radius: 10px;
    background: #fff; padding: 10px 14px; cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
    position: relative;
}
.mc-file-box:hover { border-color: #006666; box-shadow: 0 0 0 3px rgba(0,102,102,.06); }
.mc-file-icon { color: #006666; font-size: 1.4rem; }
.mc-file-preview { display: flex; align-items: center; }
.mc-file-label { font-size: 0.8rem; color: #80868b; }
.mc-file-input {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}

/* ── Plan Options ── */
.mc-plan-option {
    display: inline-flex; align-items: center; gap: 7px;
    border: 1.5px solid #e0e0e0; border-radius: 10px;
    padding: 8px 18px; cursor: pointer; font-size: 0.85rem; font-weight: 600;
    color: #5f6368; transition: all .15s; background: #fff;
    text-transform: capitalize;
}
.mc-plan-option input[type="radio"] { display: none; }
.mc-plan-option:hover { border-color: #006666; color: #006666; background: rgba(0,102,102,0.04); }
.mc-plan-active { border-color: #006666; color: #006666; background: rgba(0,102,102,0.06) !important; }

/* ── Footer ── */
.mc-form-footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding-top: 20px; border-top: 1px solid #e8eaed; flex-wrap: wrap;
}
.mc-btn-cancel {
    display: inline-flex; align-items: center;
    font-size: 0.85rem; padding: 9px 20px; border-radius: 10px;
    border: 1.5px solid #d1d5db; background: #fff; color: #5f6368;
    cursor: pointer; text-decoration: none; transition: background .15s;
}
.mc-btn-cancel:hover { background: #f5f5f5; color: #444; }
.mc-btn-save {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.85rem; font-weight: 700; padding: 9px 24px; border-radius: 10px;
    border: none; background: #006666; color: #fff;
    cursor: pointer; transition: background .15s;
}
.mc-btn-save:hover { background: #004e4e; }

/* ── Flash messages ── */
.mc-alert {
    display: flex; align-items: center; gap: 10px;
    border-radius: 10px; padding: 12px 16px;
    font-size: 0.85rem; font-weight: 500; margin-bottom: 20px;
}
.mc-alert-success { background: rgba(52,168,83,0.08); border:1px solid rgba(52,168,83,0.25); color: #34a853; }
.mc-alert-error   { background: rgba(234,67,53,0.08); border:1px solid rgba(234,67,53,0.25); color: #ea4335; }

@media (max-width: 768px) {
    .mc-form-body  { padding: 16px; }
    .mc-form-header { padding: 16px 18px; }
    .mc-banner     { flex-direction: column; align-items: flex-start; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Tax visibility (GST / VAT) ── */
    const gstInput = document.getElementById('gst');
    const vatInput = document.getElementById('vat');
    const taxRows  = {
        CGST : document.querySelector('[data-tax="CGST"]'),
        SGST : document.querySelector('[data-tax="SGST"]'),
        IGST : document.querySelector('[data-tax="IGST"]'),
        VAT  : document.querySelector('[data-tax="VAT"]')
    };

    function setVisible(row, visible) {
        if (!row) return;
        row.style.display = visible ? '' : 'none';
        const inp = row.querySelector('input');
        if (inp) inp.disabled = !visible;
    }

    function refreshVisibility() {
        const hasGST = gstInput && gstInput.value.trim() !== '';
        const hasVAT = vatInput && vatInput.value.trim() !== '';
        ['CGST','SGST','IGST'].forEach(k => setVisible(taxRows[k], hasGST));
        setVisible(taxRows.VAT, hasVAT);
    }

    refreshVisibility();
    if (gstInput) gstInput.addEventListener('input', refreshVisibility);
    if (vatInput) vatInput.addEventListener('input', refreshVisibility);

    /* ── Plan option active class toggle ── */
    document.querySelectorAll('.mc-plan-option input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.mc-plan-option').forEach(el => el.classList.remove('mc-plan-active'));
            radio.closest('.mc-plan-option').classList.add('mc-plan-active');
        });
    });

    /* ── File input label update ── */
    document.querySelectorAll('.mc-file-input').forEach(inp => {
        inp.addEventListener('change', function () {
            const label = this.closest('.mc-file-box').querySelector('.mc-file-label');
            if (label && this.files[0]) label.textContent = this.files[0].name;
        });
    });
});
</script>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof swal !== 'undefined') {
        swal("Saved!", "{{ session('success') }}", "success");
    }
});
</script>
@endif
@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof swal !== 'undefined') {
        swal("Error", "{{ session('error') }}", "error");
    }
});
</script>
@endif

@endsection
