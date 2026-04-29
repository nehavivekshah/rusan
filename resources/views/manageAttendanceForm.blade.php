@once
<style>
/* ───────────────────────────────────────
   Attendance Modal — cf-style form
──────────────────────────────────────── */
.af-wrap * { box-sizing: border-box; font-family: inherit; }

.af-section-title {
    font-size: .72rem;
    font-weight: 700;
    color: #006666;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin: 18px 0 12px;
    padding-bottom: 4px;
    border-bottom: 1.5px solid rgba(0,102,102,.12);
}
.af-section-title:first-child { margin-top: 0; }

.af-field { display: flex; flex-direction: column; }
.af-field label {
    font-size: .78rem;
    color: #5f6368;
    font-weight: 400;
    margin-bottom: 5px;
}
.af-field label .req { color: #ea4335; }

.af-input-box {
    display: flex;
    align-items: center;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
    height: 42px;
}
.af-input-box:focus-within {
    border-color: #006666;
    box-shadow: 0 0 0 3px rgba(0,102,102,.08);
}
.af-input-box .af-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 100%;
    flex-shrink: 0;
    color: #006666;
    font-size: 1.05rem;
    border-right: 1.5px solid #e8eaed;
    background: #f8fdfd;
}
.af-input-box input,
.af-input-box select,
.af-input-box textarea {
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
.af-input-box input::placeholder { color: #9aa0a6; }
.af-input-box select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%235f6368'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 28px;
}

.af-input-box.af-textarea-box {
    height: auto;
    align-items: flex-start;
}
.af-input-box.af-textarea-box textarea {
    height: auto;
    padding: 10px;
    resize: none;
    width: 100%;
}

/* Modal header */
.af-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: linear-gradient(135deg, #005757, #007e7e);
    border-radius: 16px 16px 0 0;
}
.af-modal-header-title { font-size: .975rem; font-weight: 700; color: #fff; margin: 0; }
.af-modal-header-sub   { font-size: .73rem; color: rgba(255,255,255,.72); margin: 0; }
.af-modal-header .btn-close { filter: invert(1); opacity: .8; }

/* Modal footer */
.af-modal-footer {
    padding: 12px 20px;
    border-top: 1px solid #e8eaed;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    background: #fff;
    border-radius: 0 0 16px 16px;
}
.af-btn-cancel {
    font-size: .85rem; padding: 8px 20px; border-radius: 8px;
    border: 1.5px solid #d1d5db; background: #fff; color: #5f6368;
    cursor: pointer; transition: background .15s;
}
.af-btn-cancel:hover { background: #f5f5f5; }
.af-btn-save {
    font-size: .85rem; font-weight: 600; padding: 8px 22px; border-radius: 8px;
    border: none; background: #006666; color: #fff;
    cursor: pointer; transition: background .15s;
    display: flex; align-items: center; gap: 5px;
}
.af-btn-save:hover { background: #004e4e; }

/* Status pill preview inside form */
.af-status-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 10px; border-radius: 20px; font-size:.72rem; font-weight:700;
}
</style>
@endonce

@php
    $isEdit = !empty($attendance?->id);
@endphp

{{-- ── Header ── --}}
<div class="af-modal-header">
    <div>
        <p class="af-modal-header-title">
            <i class="bx {{ $isEdit ? 'bx-edit-alt' : 'bx-plus-circle' }} me-1"></i>
            {{ $isEdit ? 'Edit Attendance' : 'Add Attendance' }}
        </p>
        <p class="af-modal-header-sub">{{ $isEdit ? 'Update the attendance record below' : 'Manually log attendance for a team member' }}</p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

{{-- ── Body ── --}}
<div class="modal-body px-4 py-3 af-wrap" style="max-height:68vh; overflow-y:auto; background:#f4fbfb;">
    <form action="/manage-attendance" method="POST" id="attendanceModalForm">
        @csrf
        @if($isEdit)
            <input type="hidden" name="id" value="{{ $attendance->id }}">
        @endif

        {{-- ─ WHO & WHEN ─ --}}
        <div class="af-section-title">Who &amp; When</div>
        <div class="row g-3">

            {{-- Team Member --}}
            @if($isAdmin)
            <div class="col-12 af-field">
                <label>Team Member <span class="req">*</span></label>
                <div class="af-input-box">
                    <span class="af-icon"><i class="bx bx-user"></i></span>
                    <select name="user_id" required>
                        <option value="">— Select member —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}"
                                {{ ($attendance?->user_id == $u->id) ? 'selected'
                                    : ((!$attendance && isset($prefillUser) && $prefillUser == $u->id) ? 'selected' : '') }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @else
                <input type="hidden" name="user_id" value="{{ $authUser->id }}">
            @endif

            {{-- Date --}}
            <div class="col-md-6 af-field">
                <label>Date <span class="req">*</span></label>
                <div class="af-input-box">
                    <span class="af-icon"><i class="bx bx-calendar"></i></span>
                    <input type="date" name="date" required
                           value="{{ $attendance?->date ?? ($prefillDate ?? now()->toDateString()) }}"
                           max="{{ now()->toDateString() }}">
                </div>
            </div>

            {{-- Status --}}
            <div class="col-md-6 af-field">
                <label>Status <span class="req">*</span></label>
                <div class="af-input-box">
                    <span class="af-icon"><i class="bx bx-info-circle"></i></span>
                    <select name="status" id="af_statusSelect" required>
                        @foreach(['Present','Absent','Leave','Holiday'] as $st)
                            <option value="{{ $st }}"
                                {{ ($attendance?->status == $st) ? 'selected'
                                   : ($st == 'Present' && !$attendance ? 'selected' : '') }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ─ TIMING ─ --}}
        <div id="af_timingSection">
            <div class="af-section-title">Timing</div>
            <div class="row g-3">
                <div class="col-md-6 af-field">
                    <label><i class="bx bx-log-in-circle text-success me-1"></i>Check-In</label>
                    <div class="af-input-box">
                        <span class="af-icon" style="color:#34a853;"><i class="bx bx-time-five"></i></span>
                        <input type="time" name="check_in"
                               value="{{ $attendance?->check_in ?? '09:30' }}">
                    </div>
                </div>
                <div class="col-md-6 af-field">
                    <label><i class="bx bx-log-out-circle text-danger me-1"></i>Check-Out</label>
                    <div class="af-input-box">
                        <span class="af-icon" style="color:#ea4335;"><i class="bx bx-time-five"></i></span>
                        <input type="time" name="check_out"
                               value="{{ $attendance?->check_out ?? '18:30' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ─ METHOD & NOTES ─ --}}
        <div class="af-section-title">Method &amp; Notes</div>
        <div class="row g-3">
            <div class="col-md-6 af-field">
                <label>Check-In Method</label>
                <div class="af-input-box">
                    <span class="af-icon"><i class="bx bx-fingerprint"></i></span>
                    <select name="method">
                        <option value="">— None / Auto —</option>
                        @foreach(['Manual','Biometric','App','Card','GPS'] as $m)
                            <option value="{{ $m }}" {{ ($attendance?->method == $m) ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 af-field">
                <label>Remarks</label>
                <div class="af-input-box af-textarea-box">
                    <span class="af-icon" style="padding-top:10px; align-self:flex-start;"><i class="bx bx-message-detail"></i></span>
                    <textarea name="remarks" rows="3"
                              placeholder="Optional notes about this record…">{{ $attendance?->remarks ?? '' }}</textarea>
                </div>
            </div>
        </div>

    </form>
</div>

{{-- ── Footer ── --}}
<div class="af-modal-footer">
    <button type="button" class="af-btn-cancel" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" form="attendanceModalForm" class="af-btn-save">
        <i class="bx bx-check"></i>
        {{ $isEdit ? 'Update Record' : 'Save Attendance' }}
    </button>
</div>

<script>
(function () {
    const statusSel     = document.getElementById('af_statusSelect');
    const timingSection = document.getElementById('af_timingSection');

    function toggleTiming() {
        const hide = ['Absent', 'Leave', 'Holiday'];
        if (timingSection) {
            timingSection.style.display = hide.includes(statusSel.value) ? 'none' : '';
        }
    }

    if (statusSel) {
        toggleTiming();
        statusSel.addEventListener('change', toggleTiming);
    }
})();
</script>
