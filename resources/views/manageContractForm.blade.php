{{-- Select2 --}}
@once
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
/* ───────────────────────────────────────────
   Contract Modal — Matching Screenshot Style
──────────────────────────────────────────── */
.cf-wrap * { box-sizing: border-box; font-family: inherit; }

/* Section heading */
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

/* Field group */
.cf-field { display: flex; flex-direction: column; }
.cf-field label {
    font-size: .78rem;
    color: #5f6368;
    font-weight: 400;
    margin-bottom: 5px;
    text-transform: none;
}
.cf-field label .req { color: #ea4335; }

/* Icon-inside input wrapper */
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
.cf-select2-wrap select,
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
    appearance: none;
    -webkit-appearance: none;
}
.cf-input-box input::placeholder,
.cf-input-box select:invalid,
.cf-input-box select option[value=""] {
    color: #9aa0a6;
}
.cf-input-box select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%235f6368'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 28px;
}

/* Textarea box */
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

/* Select2 wrapper — matches cf-input-box style */
.cf-select2-wrap {
    position: relative;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    display: flex;
    align-items: center;
    height: 42px;
    transition: border-color .15s, box-shadow .15s;
}
.cf-select2-wrap:focus-within {
    border-color: #163f7a;
    box-shadow: 0 0 0 3px rgba(22, 63, 122,.08);
}
.cf-select2-wrap .cf-icon-abs {
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
    pointer-events: none;
    z-index: 2;
}
/* Select2 container fills remaining space */
.cf-select2-wrap .select2-container { flex: 1; min-width: 0; }
.cf-select2-wrap .select2-container--default .select2-selection--single {
    height: 42px;
    border: none !important;
    border-radius: 0;
    padding-left: 10px;
    display: flex;
    align-items: center;
    background: transparent;
    box-shadow: none !important;
}
.cf-select2-wrap .select2-container--default.select2-container--focus .select2-selection--single,
.cf-select2-wrap .select2-container--default.select2-container--open .select2-selection--single {
    border: none !important;
    box-shadow: none !important;
}
.cf-select2-wrap .select2-selection--single .select2-selection__rendered {
    line-height: normal;
    font-size: .875rem;
    color: #202124;
    padding: 0;
}
.cf-select2-wrap .select2-selection--single .select2-selection__placeholder { color: #9aa0a6; }
.cf-select2-wrap .select2-selection--single .select2-selection__arrow { height: 40px; right: 6px; }
.select2-dropdown {
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    box-shadow: 0 6px 24px rgba(0,0,0,.1);
    z-index: 99999 !important;
    overflow: hidden;
}
.select2-search--dropdown .select2-search__field {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: .85rem;
    padding: 6px 10px;
}
.select2-results__option { font-size: .85rem; padding: 8px 12px; }
.select2-results__option--highlighted { background: #163f7a !important; }
/* Native select inside Select2 wrapper — no border */
.cf-select2-wrap select,
#cf_client { border: 0 !important; outline: none !important; box-shadow: none !important; }

/* Modal header */
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

/* Modal footer */
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
</style>
@endonce

@php
    $isEdit     = !empty($contract->id);
    $showCustom = old('contract_type', $contract->contract_type ?? '') === 'new';
@endphp

{{-- ── Header ── --}}
<div class="cf-modal-header">
    <div>
        <p class="cf-modal-header-title">
            <i class="bx {{ $isEdit ? 'bx-edit-alt' : 'bx-plus-circle' }} me-1"></i>
            {{ $isEdit ? 'Edit Contract' : 'New Contract' }}
        </p>
        <p class="cf-modal-header-sub">{{ $isEdit ? 'Update contract details below' : 'Fill in details to create a new contract' }}</p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

{{-- ── Body ── --}}
<div class="modal-body px-4 py-3 cf-wrap" style="max-height:68vh; overflow-y:auto; background:#f4fbfb;">
    <form action="/manage-contract" method="post" id="contractModalForm">
        @csrf
        @if($isEdit)
            <input type="hidden" name="id" value="{{ $contract->id }}">
        @endif

        {{-- ─ CONTRACT INFORMATION ─ --}}
        <div class="cf-section-title">Contract Information</div>
        <div class="row g-3">

            {{-- Client --}}
            <div class="col-12 cf-field">
                <label>Client <span class="req">*</span></label>
                <div class="cf-select2-wrap">
                    <span class="cf-icon-abs"><i class="bx bx-user"></i></span>
                    <select id="cf_client" name="client_id" required>
                        <option value="">Search or select a client...</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ ($contract->client_id ?? '') == $c->id ? 'selected' : '' }}>
                                {{ $c->name ?? 'Unnamed' }}{{ $c->company ? ' — '.$c->company : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('client_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Subject --}}
            <div class="col-12 cf-field">
                <label>Subject <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-text"></i></span>
                    <input type="text" name="subject"
                           placeholder="e.g. Domain + Hosting Renewal 2025"
                           value="{{ old('subject', $contract->subject ?? '') }}" required>
                </div>
                @error('subject')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Contract Type --}}
            <div class="{{ $showCustom ? 'col-md-6' : 'col-12' }} cf-field" id="cf_type_col">
                <label>Contract Type <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-category"></i></span>
                    <select id="cf_contract_type" name="contract_type" required>
                        <option value="">Select type...</option>
                        @foreach([
                            'domain'               => 'Domain Renewal',
                            'hosting'              => 'Hosting Renewal',
                            'domain-hosting'       => 'Domain + Hosting Renewal',
                            'hosting-email'        => 'Hosting + Email Renewal',
                            'hosting-webmail'      => 'Hosting + Webmail Renewal',
                            'domain-hosting-email' => 'Domain + Hosting + Email',
                            'seo'                  => 'SEO',
                            'new'                  => 'New (Custom)...',
                        ] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('contract_type', $contract->contract_type ?? '') === $val ? 'selected' : '' }}>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('contract_type')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Custom type --}}
            <div class="col-md-6 cf-field" id="cf_custom_wrap" style="{{ $showCustom ? '' : 'display:none;' }}">
                <label>Custom Type Name <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-pencil"></i></span>
                    <input type="text" name="custom_contract_type" id="cf_custom_type"
                           placeholder="Enter custom type"
                           value="{{ old('custom_contract_type', '') }}">
                </div>
                @error('custom_contract_type')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

        </div>

        {{-- ─ VALUE & PERIOD ─ --}}
        <div class="cf-section-title">Value &amp; Period</div>
        <div class="row g-3">

            <div class="col-md-4 cf-field">
                <label>Contract Value (₹)</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-rupee"></i></span>
                    <input type="number" step="0.01" min="0" name="value"
                           placeholder="0.00"
                           value="{{ old('value', $contract->value ?? '') }}">
                </div>
                @error('value')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-md-4 cf-field">
                <label>Start Date <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-calendar"></i></span>
                    <input type="date" name="start_date"
                           value="{{ old('start_date', !empty($contract->start_date) ? \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') : '') }}" required>
                </div>
                @error('start_date')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="col-md-4 cf-field">
                <label>End Date</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-calendar-x"></i></span>
                    <input type="date" name="end_date"
                           value="{{ old('end_date', !empty($contract->end_date) ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') : '') }}">
                </div>
                @error('end_date')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

        </div>

        {{-- ─ NOTES ─ --}}
        <div class="cf-section-title">Notes / Scope</div>
        <div class="cf-field">
            <div class="cf-input-box cf-textarea-box">
                <textarea name="description" rows="5"
                          placeholder="Add any notes, scope of work, or terms (optional)...">{{ old('description', $contract->des ?? '') }}</textarea>
            </div>
            @error('description')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

    </form>
</div>

{{-- ── Footer ── --}}
<div class="cf-modal-footer">
    <button type="button" class="cf-btn-cancel" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" form="contractModalForm" class="cf-btn-save">
        <i class="bx bx-check"></i>
        {{ $isEdit ? 'Update Contract' : 'Save Contract' }}
    </button>
</div>

<script>
(function () {
    // Select2 on client
    if (typeof $.fn.select2 !== 'undefined') {
        $('#cf_client').select2({
            placeholder: 'Search or select a client...',
            allowClear: true,
            dropdownParent: $('#manageContractModal'),
            width: '100%'
        });
    }

    // Contract type custom toggle
    const typeEl     = document.getElementById('cf_contract_type');
    const customWrap = document.getElementById('cf_custom_wrap');
    const customEl   = document.getElementById('cf_custom_type');
    const typeCol    = document.getElementById('cf_type_col');

    function toggleCustom() {
        if (!typeEl) return;
        const isNew = typeEl.value === 'new';
        if (customWrap) customWrap.style.display = isNew ? '' : 'none';
        if (customEl)   customEl.required         = isNew;
        if (typeCol)    typeCol.className          = 'cf-field ' + (isNew ? 'col-md-6' : 'col-12');
    }

    if (typeEl) { toggleCustom(); typeEl.addEventListener('change', toggleCustom); }
})();
</script>
