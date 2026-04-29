{{-- manageRecoveryForm.blade.php — Pure partial for AJAX modal injection --}}
@once
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
/* ── Reuse contract modal style ── */
.cf-wrap * { box-sizing: border-box; font-family: inherit; }
.cf-section-title {
    font-size: .72rem; font-weight: 700; color: #006666;
    text-transform: uppercase; letter-spacing: .07em;
    margin: 16px 0 10px; padding-bottom: 4px;
    border-bottom: 1.5px solid rgba(0,102,102,.12);
}
.cf-section-title:first-child { margin-top: 0; }
.cf-field { display: flex; flex-direction: column; }
.cf-field label { font-size: .78rem; color: #5f6368; font-weight: 400; margin-bottom: 5px; }
.cf-field label .req { color: #ea4335; }
.cf-input-box {
    display: flex; align-items: center;
    border: 1.5px solid #d1d5db; border-radius: 8px;
    background: #fff; overflow: hidden; height: 42px;
    transition: border-color .15s, box-shadow .15s;
}
.cf-input-box:focus-within { border-color: #006666; box-shadow: 0 0 0 3px rgba(0,102,102,.08); }
.cf-icon {
    display: flex; align-items: center; justify-content: center;
    width: 38px; height: 100%; flex-shrink: 0;
    color: #006666; font-size: 1.05rem;
    border-right: 1.5px solid #e8eaed; background: #f8fdfd;
}
.cf-input-box input,
.cf-input-box select {
    flex: 1; border: none !important; outline: none !important;
    box-shadow: none !important; background: transparent;
    font-size: .875rem; color: #202124; padding: 0 10px; height: 100%;
    appearance: none; -webkit-appearance: none;
}
.cf-input-box input::placeholder { color: #9aa0a6; }
.cf-input-box select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%235f6368'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
}
.cf-input-box.cf-textarea-box { height: auto; align-items: flex-start; }
.cf-input-box.cf-textarea-box .cf-icon { height: 42px; align-self: flex-start; }
.cf-input-box.cf-textarea-box textarea {
    flex: 1; border: none !important; outline: none !important;
    box-shadow: none !important; background: transparent;
    font-size: .875rem; color: #202124; padding: 10px; resize: none; width: 100%;
}
/* Select2 */
.cf-select2-wrap {
    position: relative; display: flex; align-items: center;
    border: 1.5px solid #d1d5db; border-radius: 8px;
    background: #fff; height: 42px; overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
}
.cf-select2-wrap:focus-within { border-color: #006666; box-shadow: 0 0 0 3px rgba(0,102,102,.08); }
.cf-select2-wrap .cf-icon-abs {
    display: flex; align-items: center; justify-content: center;
    width: 38px; height: 100%; flex-shrink: 0;
    color: #006666; font-size: 1.05rem;
    border-right: 1.5px solid #e8eaed; background: #f8fdfd; pointer-events: none; z-index: 2;
}
.cf-select2-wrap .select2-container { flex: 1; min-width: 0; }
.cf-select2-wrap .select2-container--default .select2-selection--single {
    height: 42px; border: none !important; border-radius: 0; padding-left: 10px;
    display: flex; align-items: center; background: transparent; box-shadow: none !important;
}
.cf-select2-wrap .select2-selection--single .select2-selection__rendered { line-height: normal; font-size: .875rem; color: #202124; padding: 0; }
.cf-select2-wrap .select2-selection--single .select2-selection__placeholder { color: #9aa0a6; }
.cf-select2-wrap .select2-selection--single .select2-selection__arrow { height: 40px; right: 6px; }
.select2-dropdown { border: 1.5px solid #d1d5db; border-radius: 8px; box-shadow: 0 6px 24px rgba(0,0,0,.1); z-index: 99999 !important; }
.select2-search--dropdown .select2-search__field { border: 1px solid #e0e0e0; border-radius: 6px; font-size: .85rem; padding: 6px 10px; }
.select2-results__option { font-size: .85rem; padding: 8px 12px; }
.select2-results__option--highlighted { background: #006666 !important; }
/* Native selects inside Select2 wrapper — no border */
.cf-select2-wrap select,
#rf_client,
#rf_project { border: 0 !important; outline: none !important; box-shadow: none !important; }

/* Modal header */
.cf-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; background: linear-gradient(135deg, #005757, #007e7e);
    border-radius: 16px 16px 0 0;
}
.cf-modal-header-title { font-size: .975rem; font-weight: 700; color: #fff; margin: 0; }
.cf-modal-header-sub   { font-size: .73rem; color: rgba(255,255,255,.72); margin: 0; }
.cf-modal-header .btn-close { filter: invert(1); opacity: .8; }
/* Footer */
.cf-modal-footer {
    padding: 12px 20px; border-top: 1px solid #e8eaed;
    display: flex; justify-content: flex-end; gap: 8px;
    background: #fff; border-radius: 0 0 16px 16px;
}
.cf-btn-cancel { font-size: .85rem; padding: 8px 20px; border-radius: 8px; border: 1.5px solid #d1d5db; background: #fff; color: #5f6368; cursor: pointer; }
.cf-btn-cancel:hover { background: #f5f5f5; }
.cf-btn-save { font-size: .85rem; font-weight: 600; padding: 8px 22px; border-radius: 8px; border: none; background: #006666; color: #fff; cursor: pointer; display: flex; align-items: center; gap: 5px; }
.cf-btn-save:hover { background: #004e4e; }
</style>
@endonce

@php
    $isEdit = !empty($recoveries->id ?? null);
@endphp

{{-- Header --}}
<div class="cf-modal-header">
    <div>
        <p class="cf-modal-header-title">
            <i class="bx {{ $isEdit ? 'bx-edit-alt' : 'bx-plus-circle' }} me-1"></i>
            {{ $isEdit ? 'Edit Recovery' : 'Add Recovery' }}
        </p>
        <p class="cf-modal-header-sub">{{ $isEdit ? 'Update recovery details below' : 'Fill in details to record a new recovery' }}</p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

{{-- Body --}}
<div class="modal-body px-4 py-3 cf-wrap" style="max-height:68vh; overflow-y:auto; background:#f4fbfb;">
    <form action="/manage-recovery" method="post" id="recoveryModalForm">
        @csrf
        <input type="hidden" name="id" value="{{ $recoveries->id ?? '' }}">

        {{-- CLIENT & PROJECT --}}
        <div class="cf-section-title">Context</div>
        <div class="row g-3">

            <div class="col-md-6 cf-field">
                <label>Customer <span class="req">*</span></label>
                <div class="cf-select2-wrap">
                    <span class="cf-icon-abs"><i class="bx bx-user-pin"></i></span>
                    <select id="rf_client" name="clientId" required>
                        <option value="">Select a customer...</option>
                        <option value="new">+ New Customer</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ ($recoveries->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} {{ $client->company ? "({$client->company})" : "" }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6 cf-field">
                <label>Project <span class="req">*</span></label>
                <div class="cf-select2-wrap">
                    <span class="cf-icon-abs"><i class="bx bx-briefcase"></i></span>
                    <select id="rf_project" name="projectId" required>
                        <option value="">Select a project...</option>
                        <option value="new">+ New Project</option>
                        @if(!empty($projects))
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ ($recoveries->project_id ?? '') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }} — ₹{{ number_format($project->amount, 0) }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            {{-- Custom project name --}}
            <div class="col-md-6 cf-field" id="rf_custom_project_wrap"
                 style="{{ (empty($recoveries->project_id) && !empty($recoveries->project_name)) ? '' : 'display:none;' }}">
                <label>Custom Project Name</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-box"></i></span>
                    <input type="text" name="project" id="rf_project_name"
                           placeholder="Enter custom project name"
                           value="{{ $recoveries->project_name ?? '' }}">
                </div>
            </div>

        </div>

        {{-- BASIC INFO --}}
        <div class="cf-section-title">Basic Information</div>
        <div class="row g-3">

            <div class="col-md-4 cf-field">
                <label>Batch No. <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-barcode-reader"></i></span>
                    <input type="text" id="btno" name="btno" placeholder="Batch Number"
                           value="{{ $recoveries->batchNo ?? '' }}" required>
                </div>
            </div>

            <div class="col-md-4 cf-field">
                <label>Client Name <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-user"></i></span>
                    <input type="text" name="name" placeholder="Full Name"
                           value="{{ $recoveries->client_name ?? '' }}" required>
                </div>
            </div>

            <div class="col-md-4 cf-field">
                <label>Company <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-buildings"></i></span>
                    <input type="text" name="company" placeholder="Company Name"
                           value="{{ $recoveries->client_company ?? '' }}" required>
                </div>
            </div>

        </div>

        {{-- FINANCIAL --}}
        <div class="cf-section-title">Financial</div>
        <div class="row g-3">

            <div class="col-md-4 cf-field">
                <label>Total Amount <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon" style="font-size:.9rem; font-weight:700;">₹</span>
                    <input type="number" step="0.01" min="0" name="amount" placeholder="0.00"
                           value="{{ $recoveries->project_amount ?? '' }}" required>
                </div>
            </div>

            @if(!$isEdit)
            <div class="col-md-4 cf-field">
                <label>Amount Received</label>
                <div class="cf-input-box" style="border-color:#34a853;">
                    <span class="cf-icon" style="font-size:.9rem; font-weight:700; color:#34a853; background:rgba(52,168,83,.05);">₹</span>
                    <input type="number" step="0.01" min="0" name="received" placeholder="0.00"
                           value="{{ $recoveries->paid ?? '0' }}">
                </div>
            </div>

            <div class="col-md-4 cf-field">
                <label>Next Reminder</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-bell"></i></span>
                    <input type="datetime-local" name="reminder"
                           value="{{ !empty($recoveries->reminder) ? \Carbon\Carbon::parse($recoveries->reminder)->format('Y-m-d\TH:i') : '' }}">
                </div>
            </div>
            @endif

        </div>

        {{-- CONTACT --}}
        <div class="cf-section-title">Contact Details</div>
        <div class="row g-3">

            <div class="col-md-3 cf-field">
                <label>Mobile <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-phone"></i></span>
                    <input type="tel" name="phone" placeholder="+91"
                           value="{{ $recoveries->client_mob ?? '91' }}" required>
                </div>
            </div>

            <div class="col-md-3 cf-field">
                <label>WhatsApp <span class="req">*</span></label>
                <div class="cf-input-box">
                    <a href="#" class="cf-icon waClickIcon" style="color:#25D366; text-decoration:none;" title="Click to message">
                        <i class="bx bxl-whatsapp"></i>
                    </a>
                    <input type="tel" name="whatsapp" placeholder="+91"
                           value="{{ $recoveries->client_whatsapp ?? '91' }}" required>
                </div>
            </div>

            <div class="col-md-3 cf-field">
                <label>Executive (POC)</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-user-check"></i></span>
                    <input type="text" name="executive" placeholder="POC Name"
                           value="{{ $recoveries->client_poc ?? '' }}">
                </div>
            </div>

            <div class="col-md-3 cf-field">
                <label>Industry</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-cog"></i></span>
                    <input type="text" name="industry" placeholder="e.g. IT"
                           value="{{ $recoveries->client_industry ?? '' }}">
                </div>
            </div>

            <div class="col-md-6 cf-field">
                <label>Email</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-envelope"></i></span>
                    <input type="email" name="email" placeholder="client@example.com"
                           value="{{ $recoveries->client_email ?? '' }}">
                </div>
            </div>

            <div class="col-md-6 cf-field">
                <label>Website</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-globe"></i></span>
                    <input type="url" name="website" placeholder="https://..."
                           value="{{ $recoveries->website ?? '' }}">
                </div>
            </div>

        </div>

        {{-- NOTES --}}
        <div class="cf-section-title">Notes</div>
        <div class="cf-field">
            <div class="cf-input-box cf-textarea-box">
                <textarea name="note" rows="3"
                          placeholder="Add any collection notes or comments...">{{ $recoveries->recovery_note ?? '' }}</textarea>
            </div>
        </div>

    </form>
</div>

{{-- Footer --}}
<div class="cf-modal-footer">
    <button type="button" class="cf-btn-cancel" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" form="recoveryModalForm" class="cf-btn-save">
        <i class="bx bx-check"></i>
        {{ $isEdit ? 'Save Changes' : 'Record Recovery' }}
    </button>
</div>

<script>
(function () {
    function initSelect2() {
        if (typeof $.fn.select2 === 'undefined') return;

        var $client  = $('#rf_client');
        var $project = $('#rf_project');

        // ── Init Client Select2 ──
        $client.select2({
            placeholder       : 'Search customer...',
            allowClear        : true,
            minimumInputLength: 0,
            dropdownParent    : $('#manageRecoveryModal'),
            width             : '100%'
        });

        // ── Init Project Select2 ──
        $project.select2({
            placeholder       : 'Search project...',
            allowClear        : true,
            minimumInputLength: 0,
            dropdownParent    : $('#manageRecoveryModal'),
            width             : '100%'
        });

        // ── Cascade: Client → Projects ──
        $client.on('change', function () {
            var clientId = $(this).val();
            var currentProjectId = '{{ $recoveries->project_id ?? "" }}';

            // Reset project dropdown
            $project.empty().append('<option value="">Loading projects...</option>').trigger('change');

            if (!clientId || clientId === 'new') {
                $project.empty()
                    .append('<option value="">Select a project...</option>')
                    .append('<option value="new">+ New Project</option>')
                    .trigger('change');
                toggleCustomProject();
                return;
            }

            // Fetch projects for selected client
            fetch('/get-projects/' + clientId)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    $project.empty();
                    $project.append('<option value="">Select a project...</option>');
                    $project.append('<option value="new">+ New Project</option>');

                    if (data.projects && data.projects.length > 0) {
                        data.projects.forEach(function (p) {
                            var selected = (String(p.id) === String(currentProjectId)) ? ' selected' : '';
                            $project.append(
                                '<option value="' + p.id + '"' + selected + '>' +
                                p.name + ' — ₹' + Number(p.amount).toLocaleString('en-IN') +
                                '</option>'
                            );
                        });
                    }
                    $project.trigger('change');
                    toggleCustomProject();
                })
                .catch(function () {
                    $project.empty()
                        .append('<option value="">Failed to load projects</option>')
                        .append('<option value="new">+ New Project</option>')
                        .trigger('change');
                });
        });

        // ── Project "new" toggle ──
        var projEl   = document.getElementById('rf_project');
        var projWrap = document.getElementById('rf_custom_project_wrap');
        var projName = document.getElementById('rf_project_name');

        function toggleCustomProject() {
            if (!projEl || !projWrap) return;
            var show = projEl.value === 'new';
            projWrap.style.display = show ? '' : 'none';
            if (projName) projName.required = show;
        }

        if (projEl) {
            $project.on('change', toggleCustomProject);
            toggleCustomProject();
        }

        // ── Trigger cascade on page load if client is pre-selected ──
        if ($client.val()) {
            $client.trigger('change');
        }

        // ── Handle WA click icon ──
        $('.waClickIcon').on('click', function(e) {
            e.preventDefault();
            var val = $('input[name="whatsapp"]').val() || $('input[name="phone"]').val();
            if(!val) return;
            var waNum = val.replace(/[^0-9]/g, '');
            if(waNum.length === 10) { waNum = '91' + waNum; }
            if(waNum) { window.open('https://wa.me/' + waNum, '_blank'); }
        });
    }

    // Ensure Select2 JS is loaded before init
    if (typeof $.fn.select2 !== 'undefined') {
        initSelect2();
    } else {
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
        s.onload = initSelect2;
        document.head.appendChild(s);
    }
})();
</script>

