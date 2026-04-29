{{-- Manage Plan — AJAX Form Partial (Aligned with Contract Modal) --}}
<style>
/* ───────────────────────────────────────────
   Subscription Modal — Contract Utility Styles
   (Matching manageContractForm.blade.php)
──────────────────────────────────────────── */
.cf-wrap * { box-sizing: border-box; font-family: inherit; }

.cf-section-title {
    font-size: .72rem; font-weight: 700; color: #006666;
    text-transform: uppercase; letter-spacing: .07em;
    margin: 18px 0 12px; padding-bottom: 4px;
    border-bottom: 1.5px solid rgba(0,102,102,.12);
}
.cf-section-title:first-child { margin-top: 0; }

.cf-field { display: flex; flex-direction: column; }
.cf-field label { font-size: .78rem; color: #5f6368; font-weight: 400; margin-bottom: 5px; }
.cf-field label .req { color: #ea4335; }

.cf-input-box {
    display: flex; align-items: center; border: 1.5px solid #d1d5db;
    border-radius: 8px; background: #fff; overflow: hidden;
    transition: border-color .15s, box-shadow .15s; height: 42px;
}
.cf-input-box:focus-within { border-color: #006666; box-shadow: 0 0 0 3px rgba(0,102,102,.08); }
.cf-input-box .cf-icon {
    display: flex; align-items: center; justify-content: center;
    width: 38px; height: 100%; flex-shrink: 0; color: #006666;
    font-size: 1.05rem; border-right: 1.5px solid #e8eaed; background: #f8fdfd;
}
.cf-input-box input, .cf-input-box textarea {
    flex: 1; border: none !important; outline: none !important;
    background: transparent; font-size: .875rem; color: #202124; padding: 0 12px; height: 100%;
}
.cf-input-box.cf-textarea-box { height: auto; align-items: flex-start; }
.cf-input-box.cf-textarea-box textarea { height: auto; padding: 10px; resize: none; width: 100%; }

/* Modal Header */
.cf-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; background: linear-gradient(135deg, #005757, #007e7e);
    border-radius: 16px 16px 0 0;
}
.cf-modal-header-title { font-size: .975rem; font-weight: 700; color: #fff; margin: 0; }
.cf-modal-header-sub { font-size: .73rem; color: rgba(255,255,255,.72); margin: 0; }
.cf-modal-header .btn-close { filter: invert(1); opacity:.8; }

/* Modal Footer */
.cf-modal-footer {
    padding: 12px 20px; border-top: 1px solid #e8eaed;
    display: flex; justify-content: flex-end; gap: 8px;
    background: #fff; border-radius: 0 0 16px 16px;
}
.cf-btn-cancel {
    font-size: .85rem; padding: 8px 20px; border-radius: 8px;
    border: 1.5px solid #d1d5db; background: #fff; color: #5f6368;
    cursor: pointer; transition: background .15s;
}
.cf-btn-cancel:hover { background: #f5f5f5; }
.cf-btn-save {
    font-size: .85rem; font-weight: 600; padding: 8px 22px; border-radius: 8px;
    border: none; background: #006666; color: #fff;
    cursor: pointer; transition: background .15s;
    display: flex; align-items: center; gap: 5px;
}
.cf-btn-save:hover { background: #004e4e; }
</style>

@php $isEdit = !empty($plan->id); @endphp

{{-- ── Header ── --}}
<div class="cf-modal-header">
    <div>
        <p class="cf-modal-header-title">
            <i class="bx {{ $isEdit ? 'bx-edit-alt' : 'bx-plus-circle' }} me-1"></i>
            {{ $isEdit ? 'Modify Subscription Tier' : 'Create New Tier' }}
        </p>
        <p class="cf-modal-header-sub">Define pricing and feature availability for this package</p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

{{-- ── Body ── --}}
<div class="modal-body px-4 py-3 cf-wrap" style="max-height:68vh; overflow-y:auto; background:#f4fbfb;">
    <form id="managePlanForm" action="{{ route('managePlan') }}" method="post">
        @csrf
        <input type="hidden" name="id" value="{{ $plan->id ?? '' }}">

        {{-- ─ PLAN CONFIGURATION ─ --}}
        <div class="cf-section-title">Plan Configuration</div>
        <div class="row g-3">
            {{-- Tier Name --}}
            <div class="col-md-8 cf-field">
                <label>Tier Name <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-purchase-tag"></i></span>
                    <input type="text" name="name" value="{{ $plan->name ?? '' }}" placeholder="e.g. Platinum Plus" required>
                </div>
            </div>

            {{-- Price --}}
            <div class="col-md-4 cf-field">
                <label>Monthly Price (₹) <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-rupee"></i></span>
                    <input type="number" step="0.01" name="price" value="{{ $plan->price ?? '' }}" placeholder="0.00" required>
                </div>
            </div>

            {{-- Description --}}
            <div class="col-12 cf-field">
                <label>Value Proposition / Description</label>
                <div class="cf-input-box cf-textarea-box">
                    <textarea name="description" rows="3" placeholder="Explain the key value of this plan...">{{ $plan->description ?? '' }}</textarea>
                </div>
            </div>
        </div>

        {{-- ─ INCLUSIONS & BENEFITS ─ --}}
        <div class="cf-section-title">Inclusions &amp; Benefits</div>
        <div class="cf-field">
            <div id="feature_list">
                @php $features = $plan->features ?? []; @endphp
                @forelse($features as $feat)
                    <div class="d-flex gap-2 mb-2 feature-item">
                        <div class="cf-input-box flex-grow-1">
                            <span class="cf-icon"><i class="bx bx-check-double text-success"></i></span>
                            <input type="text" name="features[]" value="{{ $feat }}" placeholder="e.g. 50 leads per month">
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm px-2 border-0 remove-feature" style="background:rgba(234,67,53,0.08);"><i class="bx bx-trash"></i></button>
                    </div>
                @empty
                    <div class="d-flex gap-2 mb-2 feature-item">
                        <div class="cf-input-box flex-grow-1">
                            <span class="cf-icon"><i class="bx bx-check-double text-success"></i></span>
                            <input type="text" name="features[]" value="" placeholder="e.g. 24/7 Priority Support">
                        </div>
                    </div>
                @endforelse
            </div>
            <button type="button" class="btn btn-sm mt-1 border-0 fw-600" id="add_feature_btn" style="color:#006666; background:rgba(0,102,102,0.05); font-size:0.75rem;">
                <i class="bx bx-plus me-1"></i> Add Another Benefit
            </button>
        </div>

    </form>
</div>

{{-- ── Footer ── --}}
<div class="cf-modal-footer">
    <button type="button" class="cf-btn-cancel" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" form="managePlanForm" class="cf-btn-save">
        <i class="bx bx-check"></i>
        {{ $isEdit ? 'Update Package' : 'Publish Plan' }}
    </button>
</div>

<script>
    (function() {
        const list = document.getElementById('feature_list');
        const addBtn = document.getElementById('add_feature_btn');

        if(addBtn) {
            addBtn.addEventListener('click', function() {
                const div = document.createElement('div');
                div.className = 'd-flex gap-2 mb-2 feature-item';
                div.innerHTML = `
                    <div class="cf-input-box flex-grow-1">
                        <span class="cf-icon"><i class="bx bx-check-double text-success"></i></span>
                        <input type="text" name="features[]" value="" placeholder="Enter feature description...">
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm px-2 border-0 remove-feature" style="background:rgba(234,67,53,0.08);"><i class="bx bx-trash"></i></button>
                `;
                list.appendChild(div);
                div.querySelector('input').focus();
            });
        }

        list.addEventListener('click', function(e) {
            if(e.target.closest('.remove-feature')) {
                e.target.closest('.feature-item').remove();
            }
        });
    })();
</script>
