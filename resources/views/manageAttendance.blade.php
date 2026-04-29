@extends('layout')
@section('title', ($attendance ? 'Edit' : 'Add') . ' Attendance - Rusan')

@section('content')
<section class="task__section">
    @include('inc.header', ['title' => ($attendance ? 'Edit' : 'Add') . ' Attendance'])

    <div class="dash-container" style="max-width: 720px;">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb" style="font-size:0.8rem; background:none; padding:0; margin:0;">
                <li class="breadcrumb-item"><a href="/attendances" class="text-decoration-none" style="color:#006666;">Attendances</a></li>
                <li class="breadcrumb-item active text-muted">{{ $attendance ? 'Edit Record' : 'New Record' }}</li>
            </ol>
        </nav>

        <div class="dash-card p-4">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom:1px solid #f1f3f4;">
                <div style="width:48px;height:48px;border-radius:14px;background:rgba(0,102,102,0.1);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#006666;">
                    <i class="bx bx-calendar-check"></i>
                </div>
                <div>
                    <h2 class="mb-0" style="font-size:1.1rem;font-weight:700;color:#202124;">
                        {{ $attendance ? 'Edit Attendance Record' : 'Add New Attendance' }}
                    </h2>
                    <div class="text-muted" style="font-size:0.78rem;">
                        {{ $attendance ? 'Update check-in/check-out details' : 'Manually log attendance for a team member' }}
                    </div>
                </div>
            </div>

            <form method="POST" action="/manage-attendance" id="attendanceForm">
                @csrf
                @if($attendance)
                    <input type="hidden" name="id" value="{{ $attendance->id }}">
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-3" style="border-radius:10px; font-size:0.85rem;">
                        <i class="bx bx-error-circle me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3">

                    {{-- Team Member --}}
                    @if($isAdmin)
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:0.82rem; color:#3c4043;">
                            <i class="bx bx-user me-1 text-muted"></i>Team Member <span class="text-danger">*</span>
                        </label>
                        <select name="user_id" class="form-select" required style="border-radius:10px; border-color:#e8eaed; font-size:0.88rem;">
                            <option value="">— Select Member —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}"
                                    {{ ($attendance && $attendance->user_id == $u->id) ? 'selected' : ((!$attendance && isset($prefillUser) && $prefillUser == $u->id) ? 'selected' : '') }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="user_id" value="{{ $authUser->id }}">
                    @endif

                    {{-- Date --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:0.82rem; color:#3c4043;">
                            <i class="bx bx-calendar me-1 text-muted"></i>Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date" class="form-control" required
                               value="{{ $attendance ? $attendance->date : ($prefillDate ?? now()->toDateString()) }}"
                               max="{{ now()->toDateString() }}"
                               style="border-radius:10px; border-color:#e8eaed; font-size:0.88rem;">
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:0.82rem; color:#3c4043;">
                            <i class="bx bx-info-circle me-1 text-muted"></i>Status <span class="text-danger">*</span>
                        </label>
                        <select name="status" id="statusSelect" class="form-select" required
                                style="border-radius:10px; border-color:#e8eaed; font-size:0.88rem;">
                            @foreach(['Present','Absent','Leave','Holiday'] as $st)
                                <option value="{{ $st }}"
                                    {{ ($attendance && $attendance->status == $st) ? 'selected' : ($st == 'Present' && !$attendance ? 'selected' : '') }}>
                                    {{ $st }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Check In / Check Out (visible only for Present) --}}
                    <div id="timingFields" class="col-12 {{ ($attendance && in_array($attendance->status, ['Absent','Leave','Holiday'])) ? 'd-none' : '' }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600" style="font-size:0.82rem; color:#3c4043;">
                                    <i class="bx bx-log-in-circle text-success me-1"></i>Check-In Time
                                </label>
                                <input type="time" name="check_in" class="form-control"
                                       value="{{ $attendance ? $attendance->check_in : '09:30' }}"
                                       style="border-radius:10px; border-color:#e8eaed; font-size:0.88rem;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600" style="font-size:0.82rem; color:#3c4043;">
                                    <i class="bx bx-log-out-circle text-danger me-1"></i>Check-Out Time
                                </label>
                                <input type="time" name="check_out" class="form-control"
                                       value="{{ $attendance ? $attendance->check_out : '18:30' }}"
                                       style="border-radius:10px; border-color:#e8eaed; font-size:0.88rem;">
                            </div>
                        </div>
                    </div>

                    {{-- Method --}}
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:0.82rem; color:#3c4043;">
                            <i class="bx bx-fingerprint me-1 text-muted"></i>Method
                        </label>
                        <select name="method" class="form-select" style="border-radius:10px; border-color:#e8eaed; font-size:0.88rem;">
                            <option value="">— None / Auto —</option>
                            @foreach(['Manual','Biometric','App','Card','GPS'] as $m)
                                <option value="{{ $m }}" {{ ($attendance && $attendance->method == $m) ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Remarks --}}
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:0.82rem; color:#3c4043;">
                            <i class="bx bx-message-detail me-1 text-muted"></i>Remarks
                        </label>
                        <textarea name="remarks" class="form-control" rows="3"
                                  placeholder="Optional notes about this attendance record…"
                                  style="border-radius:10px; border-color:#e8eaed; font-size:0.88rem; resize:none;">{{ $attendance ? $attendance->remarks : '' }}</textarea>
                    </div>

                </div>

                <div class="d-flex align-items-center gap-2 mt-4 pt-3" style="border-top:1px solid #f1f3f4;">
                    <button type="submit" class="lb-btn lb-btn-primary" style="min-width:140px;">
                        <i class="bx bx-save me-1"></i>
                        {{ $attendance ? 'Update Record' : 'Save Attendance' }}
                    </button>
                    <a href="/attendances" class="lb-btn" style="background:#f1f3f4; color:#5f6368; border:none;">
                        <i class="bx bx-x me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
    .dash-container { padding: 0 24px 24px; }
    .dash-card { background: #fff; border: 1px solid #e8eaed; border-radius: 16px; }
    .form-control:focus, .form-select:focus {
        border-color: #006666 !important;
        box-shadow: 0 0 0 3px rgba(0,102,102,0.1) !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusSel = document.getElementById('statusSelect');
        const timingFields = document.getElementById('timingFields');

        function toggleTiming() {
            const hideStatuses = ['Absent', 'Leave', 'Holiday'];
            if (hideStatuses.includes(statusSel.value)) {
                timingFields.classList.add('d-none');
            } else {
                timingFields.classList.remove('d-none');
            }
        }

        statusSel.addEventListener('change', toggleTiming);
        toggleTiming(); // Run once on load
    });
</script>
@endsection
