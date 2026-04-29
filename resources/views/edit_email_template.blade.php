@extends('layout')
@section('title', 'Edit Email Template - Rusan')

@section('content')

<section class="task__section">
    @include('inc.header', ['title' => 'Edit Email Template'])

    <div class="dash-container">

        {{-- ── Info Banner ── --}}
        <div class="et-banner mb-4">
            <div class="et-banner-icon"><i class="bx bx-edit-alt"></i></div>
            <div class="et-banner-info">
                <div class="et-banner-title">Editing: {{ ucfirst($template->module) }} — {{ ucfirst($template->event) }}</div>
                <div class="et-banner-sub">Subject: {{ $template->subject }}</div>
            </div>
            <span class="et-banner-pill {{ $template->is_active ? 'et-pill-active' : 'et-pill-inactive' }}">
                <i class="bx {{ $template->is_active ? 'bx-check-circle' : 'bx-pause-circle' }}"></i>
                {{ $template->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <div class="dash-card et-form-card">

            {{-- Card Header --}}
            <div class="et-form-header">
                <div>
                    <p class="et-form-header-title"><i class="bx bx-edit-alt me-1"></i>Update Template</p>
                    <p class="et-form-header-sub">Modify the template content and scheduling. Module is locked after creation.</p>
                </div>
                <a href="{{ route('email-templates.index') }}" class="et-back-btn">
                    <i class="bx bx-arrow-back"></i> Back
                </a>
            </div>

            {{-- Card Body --}}
            <div class="et-form-body">
                <form method="POST" action="{{ route('email-templates.update', $template->id) }}" id="etForm">
                    @csrf
                    @method('PUT')

                    {{-- ── VARIABLES CHIP STRIP ── --}}
                    <div class="et-variables-bar mb-4">
                        <span class="et-var-label"><i class="bx bx-code-curly"></i> Available Variables</span>
                        <span class="et-var-hint">Click to copy &amp; insert</span>
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
                            <label>Module</label>
                            <div class="et-input-box et-disabled">
                                <span class="et-icon"><i class="bx bx-layer"></i></span>
                                <input type="text" value="{{ ucfirst($template->module) }}" disabled>
                            </div>
                            <small class="text-muted" style="font-size:0.72rem;margin-top:3px;">Module cannot be changed after creation</small>
                        </div>

                        <div class="col-md-4 et-field">
                            <label>Event Trigger <span class="req">*</span></label>
                            <div class="et-input-box">
                                <span class="et-icon"><i class="bx bx-calendar-event"></i></span>
                                <input type="text" name="event" required
                                       placeholder="e.g. expiry / created / overdue"
                                       value="{{ old('event', $template->event) }}">
                            </div>
                        </div>

                        <div class="col-md-4 et-field">
                            <label>Status</label>
                            <div class="et-input-box">
                                <span class="et-icon"><i class="bx bx-toggle-right"></i></span>
                                <select name="is_active">
                                    <option value="1" {{ $template->is_active ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$template->is_active ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 et-field">
                            <label>Email Subject <span class="req">*</span></label>
                            <div class="et-input-box">
                                <span class="et-icon"><i class="bx bx-envelope"></i></span>
                                <input type="text" name="subject" required
                                       placeholder="e.g. Your contract is expiring soon"
                                       value="{{ old('subject', $template->subject) }}">
                            </div>
                        </div>

                    </div>

                    {{-- ── EMAIL BODY ── --}}
                    <div class="et-section-title">Email Body</div>
                    <div class="et-field">
                        <label>Message Content <span class="req">*</span></label>
                        <div class="et-textarea-box">
                            <textarea name="body" id="emailBody" rows="12" required>{{ old('body', $template->body) }}</textarea>
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
                    </div>

                    {{-- ── FOOTER ── --}}
                    <div class="et-form-footer mt-4">
                        <a href="{{ route('email-templates.index') }}" class="et-btn-cancel">Cancel</a>
                        <button type="submit" class="et-btn-save">
                            <i class="bx bx-check"></i> Update Template
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

@include('partials.et-styles')

<script>
const existingDays = {!! json_encode(old('reminder_days', $template->reminder_days ?? [])) !!};

document.addEventListener('DOMContentLoaded', function () {

    /* ── Pre-load existing reminder days ── */
    if (existingDays && existingDays.length) {
        existingDays.forEach(d => addReminderDay(d));
    } else {
        addReminderDay('');
    }

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

    /* ── Variable chips → insert into body ── */
    const bodyTA = document.getElementById('emailBody');
    document.querySelectorAll('.et-var-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            const val = this.dataset.value;
            if (bodyTA) {
                const start = bodyTA.selectionStart;
                const end   = bodyTA.selectionEnd;
                bodyTA.value = bodyTA.value.slice(0, start) + val + bodyTA.value.slice(end);
                bodyTA.selectionStart = bodyTA.selectionEnd = start + val.length;
                bodyTA.focus();
            }
            navigator.clipboard?.writeText(val);
            const orig = this.textContent;
            this.textContent = '✓ Inserted';
            this.style.background = 'rgba(52,168,83,0.15)';
            this.style.color = '#34a853';
            setTimeout(() => {
                this.textContent = orig;
                this.style.background = '';
                this.style.color = '';
            }, 1200);
        });
    });
});
</script>

@if(session('success'))
<script>document.addEventListener('DOMContentLoaded',()=>{if(typeof swal!=='undefined')swal("Updated!","{{ session('success') }}","success");});</script>
@endif
@endsection
