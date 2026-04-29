@php
    $roles = session('roles');
    $roleArray = explode(',', ($roles->permissions ?? ''));
    $amount = $project->amount ?? 0;
@endphp

<style>
.cf-wrap * { box-sizing: border-box; font-family: inherit; }
.cf-section-title {
    font-size:.72rem; font-weight:700; color:#163f7a; text-transform:uppercase;
    letter-spacing:.07em; margin:16px 0 10px; padding-bottom:4px;
    border-bottom:1.5px solid rgba(22, 63, 122,.12);
}
.cf-section-title:first-child { margin-top:0; }
.cf-field { display:flex; flex-direction:column; }
.cf-field label { font-size:.78rem; color:#5f6368; font-weight:400; margin-bottom:5px; }
.cf-input-box {
    display:flex; align-items:center; border:1.5px solid #d1d5db;
    border-radius:8px; background:#fff; overflow:hidden; height:42px;
    transition:border-color .15s, box-shadow .15s;
}
.cf-input-box:focus-within { border-color:#163f7a; box-shadow:0 0 0 3px rgba(22, 63, 122,.08); }
.cf-icon {
    display:flex; align-items:center; justify-content:center; width:38px; height:100%;
    flex-shrink:0; color:#163f7a; font-size:1.05rem;
    border-right:1.5px solid #e8eaed; background:#f8fdfd;
}
.cf-input-box input, .cf-input-box textarea {
    flex:1; border:none !important; outline:none !important; box-shadow:none !important;
    background:transparent; font-size:.875rem; color:#202124; padding:0 10px; height:100%;
}
.cf-input-box input[readonly] { background:#fafafa; color:#80868b; }
.cf-input-box.cf-textarea-box { height:auto; align-items:flex-start; }
.cf-input-box.cf-textarea-box .cf-icon { height:42px; align-self:flex-start; }
.cf-input-box.cf-textarea-box textarea { height:auto; padding:10px; resize:none; width:100%; }

.rv-summary-bar {
    display:flex; align-items:center; justify-content:space-between;
    background:linear-gradient(135deg,rgba(251,188,4,.06),rgba(251,188,4,.03));
    border:1px solid rgba(251,188,4,.2); border-radius:10px;
    padding:10px 14px; margin-bottom:14px;
}
.rv-summary-bar .rv-c { font-size:.875rem; font-weight:700; color:#202124; }
.rv-summary-bar .rv-b { font-size:.8rem; color:#5f6368; }
.rv-summary-bar .rv-b span { font-weight:700; color:#ea4335; }

.cf-modal-footer {
    padding:12px 20px; border-top:1px solid #e8eaed;
    display:flex; justify-content:flex-end; gap:8px;
    background:#fff; border-radius:0 0 16px 16px;
}
.cf-btn-cancel { font-size:.85rem; padding:8px 20px; border-radius:8px; border:1.5px solid #d1d5db; background:#fff; color:#5f6368; cursor:pointer; }
.cf-btn-cancel:hover { background:#f5f5f5; }
.cf-btn-save { font-size:.85rem; font-weight:600; padding:8px 22px; border-radius:8px; border:none; background:#163f7a; color:#fff; cursor:pointer; display:flex; align-items:center; gap:5px; }
.cf-btn-save:hover { background:#004e4e; }
</style>

{{-- ── Header ── --}}
<div class="cf-modal-header">
    <div>
        <p class="cf-modal-header-title"><i class="bx bx-bell me-1"></i> Set Reminder</p>
        <p class="cf-modal-header-sub">Schedule a follow-up reminder for this recovery</p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

{{-- ── Body ── --}}
<div class="modal-body px-4 py-3 cf-wrap" style="max-height:68vh; overflow-y:auto; background:#f4fbfb;">

    {{-- Summary bar --}}
    <div class="rv-summary-bar">
        <div class="rv-c">
            <i class="bx bx-buildings me-1" style="color:#f9a825;"></i>
            {{ $client->company ?? $client->name ?? '—' }}
        </div>
        <div class="rv-b">
            Outstanding: <span>₹{{ number_format($amount - $totalPaid, 0) }}</span>
        </div>
    </div>

    <div class="cf-section-title">Reminder Details</div>
    <form id="updatePayment" action="/recovery" method="post">
        @csrf
        <input type="hidden" name="received"   value="0">
        <input type="hidden" name="client_id"  value="{{ $client->id ?? '' }}">
        <input type="hidden" name="project_id" value="{{ $project->id ?? '' }}">

        <div class="row g-3">
            <div class="col-md-6 cf-field">
                <label>Reminder Date <span class="req" style="color:#ea4335;">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon" style="color:#f9a825;"><i class="bx bx-alarm"></i></span>
                    <input type="date" name="reminderDate" required>
                </div>
            </div>
            <div class="col-md-6 cf-field">
                <label>Remaining Amount</label>
                <div class="cf-input-box">
                    <span class="cf-icon" style="font-weight:700; font-size:.9rem;">₹</span>
                    <input type="number" name="bal" value="{{ $amount - $totalPaid }}" readonly>
                </div>
            </div>
            <div class="col-12 cf-field">
                <label>Note / Message</label>
                <div class="cf-input-box cf-textarea-box">
                    <textarea name="note" rows="3" placeholder="Add a reminder note or follow-up message..."></textarea>
                </div>
            </div>
        </div>
    </form>

</div>

{{-- ── Footer ── --}}
<div class="cf-modal-footer">
    <button type="button" class="cf-btn-cancel" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" form="updatePayment" class="cf-btn-save">
        <i class="bx bx-bell-plus"></i> Set Reminder
    </button>
</div>
