@php
    $company = session('companies');
    $roles = session('roles');
    $roleArray = explode(',', ($roles->permissions ?? ''));
    $amount = $project->amount ?? 0;
    $remaining = $amount;
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
.cf-field label .req { color:#ea4335; }
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

/* History table */
.rv-history-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.rv-history-table th { background:#f8f9fa; color:#5f6368; font-weight:600; font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; padding:8px 12px; border-bottom:2px solid #e8eaed; }
.rv-history-table td { padding:9px 12px; border-bottom:1px solid #f0f0f0; color:#202124; }
.rv-history-table tr:last-child td { border-bottom:none; }
.rv-history-table tr:hover td { background:#fafefe; }
.rv-editable { border:1px solid #e0e0e0; border-radius:6px; padding:3px 8px; width:110px; font-size:.82rem; color:#202124; background:#fff; cursor:pointer; }
.rv-editable:focus { border-color:#163f7a; outline:none; box-shadow:0 0 0 2px rgba(22, 63, 122,.1); }
.rv-status-dot { font-size:.7rem; font-weight:600; margin-left:6px; }

/* Summary bar */
.rv-summary-bar {
    display:flex; align-items:center; justify-content:space-between;
    background:linear-gradient(135deg,rgba(22, 63, 122,.06),rgba(22, 63, 122,.04));
    border:1px solid rgba(22, 63, 122,.12); border-radius:10px;
    padding:10px 14px; margin-bottom:14px;
}
.rv-summary-bar .rv-summary-company { font-size:.875rem; font-weight:700; color:#202124; }
.rv-summary-bar .rv-summary-bal { font-size:.8rem; color:#5f6368; }
.rv-summary-bar .rv-summary-bal span { font-weight:700; color:#ea4335; }

/* Footer */
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
        <p class="cf-modal-header-title"><i class="bx bx-rupee me-1"></i> Receive Payment</p>
        <p class="cf-modal-header-sub">Record a payment received from client</p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

{{-- ── Body ── --}}
<div class="modal-body px-4 py-3 cf-wrap" style="max-height:68vh; overflow-y:auto; background:#f4fbfb;">

    {{-- Summary bar --}}
    <div class="rv-summary-bar">
        <div class="rv-summary-company">
            <i class="bx bx-buildings me-1" style="color:#163f7a;"></i>
            {{ $client->company ?? $client->name ?? '—' }}
        </div>
        <div class="rv-summary-bal">
            Remaining Balance: <span>₹{{ number_format($amount - $totalPaid, 0) }}</span>
        </div>
    </div>

    {{-- Payment Form --}}
    <div class="cf-section-title">Payment Details</div>
    <form id="updatePayment" action="/recovery" method="post">
        @csrf
        <input type="hidden" name="client_id"  value="{{ $client->id ?? '' }}">
        <input type="hidden" name="project_id" value="{{ $project->id ?? '' }}">

        <div class="row g-3 mb-2">
            <div class="col-md-6 cf-field">
                <label>Amount Received (₹) <span class="req">*</span></label>
                <div class="cf-input-box" style="border-color:#163f7a;">
                    <span class="cf-icon" style="color:#163f7a; background:rgba(52,168,83,.05); font-weight:700; font-size:.9rem;">₹</span>
                    <input type="number" name="received" id="received" placeholder="0.00" required>
                </div>
            </div>
            <div class="col-md-6 cf-field">
                <label>Payment Date <span class="req">*</span></label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-calendar"></i></span>
                    <input type="date" name="receivedDate" value="{{ now()->format('Y-m-d') }}" readonly>
                </div>
            </div>
            <div class="col-12 cf-field">
                <label style="display:flex; align-items:center; gap:7px; cursor:pointer;">
                    <input type="checkbox" name="send" value="1" checked style="width:15px;height:15px;accent-color:#163f7a;">
                    Send Thank You Note
                </label>
                <div class="cf-input-box cf-textarea-box mt-1">
                    <textarea name="note" id="output" rows="2"
                        placeholder="Thank you message...">Thank you for payment of ₹{{ $amount - $totalPaid }} to {{ $company->name ?? '' }}.</textarea>
                </div>
            </div>
        </div>
    </form>

    {{-- Payment History --}}
    @if($recoveries->count() > 0)
    <div class="cf-section-title">Payment History</div>
    <div style="border:1px solid #e8eaed; border-radius:10px; overflow:hidden; background:#fff;">
        <table class="rv-history-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Remaining</th>
                    <th>Paid (₹)</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recoveries as $k => $recovery)
                    @php
                        $status = ($recovery->status == '1') ? 'Paid' : 'Partly Paid';
                        $paid = $recovery->paid ?? 0;
                        $remaining -= $paid;
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $k + 1 }}</td>
                        <td>{{ date('d M Y', strtotime($recovery->created_at ?? now())) }}</td>
                        <td style="color:#ea4335; font-weight:600;">₹{{ number_format($remaining, 0) }}</td>
                        <td>
                            <input type="text" class="rv-editable editableInputs"
                                value="{{ number_format($paid, 2) }}"
                                data-id="{{ $recovery->id ?? '' }}"
                                title="Double click to edit" readonly>
                            <span class="rv-status-dot status"></span>
                        </td>
                        <td>
                            <span style="font-size:.72rem; font-weight:600; padding:2px 8px; border-radius:20px;
                                background:rgba(52,168,83,.08); color:#163f7a;">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if(in_array('recoveries_delete', $roleArray) || in_array('All', $roleArray))
                            <a href="javascript:void(0)" class="btn kb-action-btn recoveryAmountDelete"
                                data-id="{{ $recovery->id ?? '' }}" data-page="recoveryAmountDelete"
                                title="Delete" style="background:rgba(234,67,53,.1);color:#ea4335;">
                                <i class="bx bx-trash"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>

{{-- ── Footer ── --}}
<div class="cf-modal-footer">
    <button type="button" class="cf-btn-cancel" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" form="updatePayment" class="cf-btn-save">
        <i class="bx bx-check"></i> Save Payment
    </button>
</div>

<script>
(function () {
    const receivedInput = document.getElementById('received');
    const output        = document.getElementById('output');
    const companyName   = '{{ $company->name ?? '' }}';

    if (receivedInput && output) {
        receivedInput.addEventListener('keyup', function () {
            output.textContent = 'Thank you for payment of ₹' + this.value + ' to ' + companyName + '.';
        });
    }

    // ── Delete Recovery Part ──
    document.querySelectorAll('.recoveryAmountDelete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var rowid = this.getAttribute("data-id");
            var pagename = this.getAttribute("data-page");
            var clickedBtn = this;

            // Hide the parent modal so SweetAlert2 is not blocked
            var parentModal = clickedBtn.closest('.modal');
            if (parentModal) { $(parentModal).modal('hide'); }

            setTimeout(function() {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to delete this payment record?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#ea4335',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'GET',
                            url: "/delete-recovery-amount",
                            data: {
                                pagename: pagename,
                                rowid: rowid,
                                recoveryAmountDelete: 'recoveryAmountDelete'
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire("Deleted!", "The record has been deleted.", "success").then(() => {
                                        location.reload(); 
                                    });
                                } else {
                                    Swal.fire("Error", response.error || "There was an issue deleting this record.", "error");
                                }
                            },
                            error: function () {
                                Swal.fire("Error", "An error occurred while processing your request.", "error");
                            }
                        });
                    } else {
                        // Re-show modal if user cancelled
                        if (parentModal) { $(parentModal).modal('show'); }
                    }
                });
            }, 300);
        });
    });

    // ── Double Click to Edit Recovery Amount ──
    document.querySelectorAll('.editableInputs').forEach(function(input) {
        let originalValue = input.value;

        input.addEventListener('dblclick', function() {
            this.removeAttribute('readonly');
            // Remove commas for easier editing
            this.value = this.value.replace(/,/g, '');
            this.focus();
            this.select();
        });

        // Proactively restrict to numbers and a single decimal during editing
        input.addEventListener('input', function() {
            if (!this.hasAttribute('readonly')) {
                this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
            }
        });

        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.blur();
            }
        });

        input.addEventListener('blur', function() {
            this.setAttribute('readonly', true);
            let newValue = this.value.replace(/,/g, '');
            let id = this.dataset.id;
            
            let floatVal = parseFloat(newValue);
            
            // Validate the parsed number
            if(isNaN(floatVal) || floatVal < 0 || newValue.trim() === "") {
                Swal.fire("Invalid Amount", "Please enter a valid positive number.", "warning");
                this.value = originalValue;
                return;
            }
            
            let origFloat = parseFloat(originalValue.replace(/,/g, ''));
            if(isNaN(origFloat)) origFloat = 0;

            if (floatVal !== origFloat) {
                $.ajax({
                    url: '/update-recovery-amount',
                    type: 'GET',
                    data: {
                        id: id,
                        amount: floatVal
                    },
                    success: function(response) {
                        originalValue = floatVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        input.value = originalValue;
                        // Reload parent page to reflect balance change
                        location.reload(); 
                    },
                    error: function() {
                        Swal.fire("Error", "Failed to update amount.", "error");
                        input.value = originalValue;
                    }
                });
            } else {
                input.value = originalValue;
            }
        });
    });

})();
</script>
