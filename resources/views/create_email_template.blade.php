@extends('layout')
@section('title', 'New Email Template - Rusan')

@section('content')

<section class="task__section">
    @include('inc.header', ['title' => 'New Email Template'])

    <div class="dash-container">
        <div class="dash-card et-form-card">

            {{-- Card Header --}}
            <div class="et-form-header">
                <div>
                    <p class="et-form-header-title"><i class="bx bx-plus-circle me-1"></i>Create Email Template</p>
                    <p class="et-form-header-sub">Define when and what to send — use variables to personalise content</p>
                </div>
                <a href="{{ route('email-templates.index') }}" class="et-back-btn">
                    <i class="bx bx-arrow-back"></i> Back
                </a>
            </div>

            {{-- Card Body --}}
            <div class="et-form-body">
                <form method="POST" action="{{ route('email-templates.store') }}" id="etForm">
                    @csrf

                    {{-- ── VARIABLES CHIP STRIP ── --}}
                    <div class="et-variables-bar mb-4">
                        <span class="et-var-label"><i class="bx bx-code-curly"></i> Available Variables</span>
                        <span class="et-var-hint">Click to copy</span>
                        @foreach(['client_name','company_name','client_contract','invoice_number','amount','due_date','end_date'] as $var)
                            <button type="button" class="et-var-chip" data-value="@{{{{ $var }}}}">
                                @{{{{ $var }}}}
                            </button>
                        @endforeach
                    </div>

                    {{-- ── TEMPLATE IDENTITY ── --}}
                    <div class="et-section-title">Template Identity</div>
                    <div class="row g-3">

                        <div class="col-md-4 et-field">
                            <label>Module <span class="req">*</span></label>
                            <div class="et-input-box">
                                <span class="et-icon"><i class="bx bx-layer"></i></span>
                                <select name="module" required>
                                    <option value="">— Select Module —</option>
                                    @foreach(['contracts' => 'Contracts', 'invoices' => 'Invoices', 'proposals' => 'Proposals', 'recovery' => 'Recovery'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ old('module') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 et-field">
                            <label>Event Trigger <span class="req">*</span></label>
                            <div class="et-input-box">
                                <span class="et-icon"><i class="bx bx-calendar-event"></i></span>
                                <input type="text" name="event" required
                                       placeholder="e.g. expiry / created / overdue"
                                       value="{{ old('event') }}">
                            </div>
                            <small class="text-muted" style="font-size:0.72rem;margin-top:3px;">When should this email fire?</small>
                        </div>

                        <div class="col-md-4 et-field">
                            <label>Status</label>
                            <div class="et-input-box">
                                <span class="et-icon"><i class="bx bx-toggle-right"></i></span>
                                <select name="is_active">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 et-field">
                            <label>Email Subject <span class="req">*</span></label>
                            <div class="et-input-box">
                                <span class="et-icon"><i class="bx bx-envelope"></i></span>
                                <input type="text" name="subject" required
                                       placeholder="e.g. Your contract @{{client_contract}} is expiring soon"
                                       value="{{ old('subject') }}">
                            </div>
                        </div>

                    </div>

                    {{-- ── EMAIL BODY ── --}}
                    <div class="et-section-title">Email Body</div>
                    <div class="et-field">
                        <label>Message Content <span class="req">*</span></label>
                        <div class="et-textarea-box">
                            <textarea name="body" id="emailBody" rows="12" required
                                      placeholder="Dear @{{client_name}},&#10;&#10;This is a reminder that...">{{ old('body') }}</textarea>
                        </div>
                        <small class="text-muted" style="font-size:0.72rem;margin-top:4px;">
                            HTML is supported. Click variable chips above to insert them at the cursor.
                        </small>
                    </div>

                    {{-- ── REMINDER DAYS ── --}}
                    <div class="et-section-title">Reminder Schedule</div>
                    <div class="et-field">
                        <label>Send Reminders Before Event (Days)</label>
                        <div id="reminder-days" class="et-reminder-row"></div>
                        <button type="button" class="et-add-day-btn" id="add-day">
                            <i class="bx bx-plus"></i> Add Another Day
                        </button>
                        <small class="text-muted" style="font-size:0.72rem;margin-top:4px;">
                            e.g. Enter <strong>7</strong> to send a reminder 7 days before the event.
                        </small>
                    </div>

                    {{-- ── FOOTER ── --}}
                    <div class="et-form-footer mt-4">
                        <a href="{{ route('email-templates.index') }}" class="et-btn-cancel">Cancel</a>
                        <button type="reset" class="et-btn-cancel">Reset</button>
                        <button type="submit" class="et-btn-save">
                            <i class="bx bx-save"></i> Save Template
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

@include('partials.et-styles')

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Insert initial empty reminder day ── */
    addReminderDay('');

    /* ── Add day button ── */
    document.getElementById('add-day').addEventListener('click', () => addReminderDay(''));

    function addReminderDay(val) {
        const container = document.getElementById('reminder-days');
        const wrap = document.createElement('div');
        wrap.className = 'et-reminder-item';
        wrap.innerHTML = `
            <div class="et-input-box" style="max-width:180px;">
                <span class="et-icon"><i class="bx bx-time"></i></span>
                <input type="number" name="reminder_days[]" min="0" placeholder="days"
                       value="${val}" style="max-width:100px;">
            </div>
            <button type="button" class="et-remove-day" title="Remove">
                <i class="bx bx-trash"></i>
            </button>`;
        container.appendChild(wrap);
    }

    /* ── Remove day ── */
    document.addEventListener('click', e => {
        if (e.target.closest('.et-remove-day')) {
            e.target.closest('.et-reminder-item').remove();
        }
    });

    /* ── Variable chips → copy & insert into body ── */
    const bodyTA = document.getElementById('emailBody');
    document.querySelectorAll('.et-var-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            const val = this.dataset.value;

            /* try inserting at cursor in textarea */
            if (bodyTA) {
                const start = bodyTA.selectionStart;
                const end   = bodyTA.selectionEnd;
                const text  = bodyTA.value;
                bodyTA.value = text.slice(0, start) + val + text.slice(end);
                bodyTA.selectionStart = bodyTA.selectionEnd = start + val.length;
                bodyTA.focus();
            }

            /* fallback clipboard copy */
            navigator.clipboard?.writeText(val);

            /* feedback flash */
            const orig = this.textContent;
            this.textContent = '✓ Copied';
            this.style.background = 'rgba(52,168,83,0.15)';
            this.style.color = '#163f7a';
            setTimeout(() => {
                this.textContent = orig;
                this.style.background = '';
                this.style.color = '';
            }, 1200);
        });
    });
});
</script>
@endsection
