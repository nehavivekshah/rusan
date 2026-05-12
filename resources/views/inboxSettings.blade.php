@extends('layout')
@section('title', 'Inbox Sync Setup (IMAP) - Rusan')

@section('content')

<section class="task__section">
    @include('inc.header', ['title' => 'Inbox Synchronization'])

    <div class="dash-container">

        {{-- ── Status Banner ── --}}
        <div class="smtp-banner mb-4" style="background: linear-gradient(135deg, #4b6cb7, #182848);">
            <div class="smtp-banner-icon">
                <i class="bx bx-sync"></i>
            </div>
            <div class="smtp-banner-info">
                <div class="smtp-banner-title">Inbound Email Synchronization</div>
                <div class="smtp-banner-sub">
                    Configure your incoming mail (IMAP) servers. This allows Rusan CRM to track replies from your leads
                    and display them directly in the customer timeline.
                </div>
            </div>
            <button class="smtp-status-pill smtp-configured" id="globalSyncBtn">
                <i class="bx bx-refresh"></i>
                Sync Now
            </button>
        </div>

        <div class="row g-4">
            {{-- ── Form Column ── --}}
            <div class="col-lg-8">
                <div class="dash-card smtp-form-card">
                    <div class="smtp-form-header" style="background: linear-gradient(135deg, #4b6cb7, #182848);">
                        <div>
                            <p class="smtp-form-header-title">
                                <i class="bx bx-plus-circle me-1"></i>
                                Add New Inbox
                            </p>
                        </div>
                    </div>
                    <div class="smtp-form-body">
                        <form action="/inbox-settings" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6 smtp-field">
                                    <label>Email Address <span class="req">*</span></label>
                                    <div class="smtp-input-box">
                                        <span class="smtp-icon"><i class="bx bx-envelope"></i></span>
                                        <input type="email" name="email" required placeholder="e.g. sales@yourdomain.com">
                                    </div>
                                </div>
                                <div class="col-md-6 smtp-field">
                                    <label>IMAP Host <span class="req">*</span></label>
                                    <div class="smtp-input-box">
                                        <span class="smtp-icon"><i class="bx bx-server"></i></span>
                                        <input type="text" name="imap_host" required placeholder="imap.gmail.com">
                                    </div>
                                </div>
                                <div class="col-md-4 smtp-field">
                                    <label>IMAP Port <span class="req">*</span></label>
                                    <div class="smtp-input-box">
                                        <span class="smtp-icon"><i class="bx bx-transfer-alt"></i></span>
                                        <input type="number" name="imap_port" required value="993">
                                    </div>
                                </div>
                                <div class="col-md-4 smtp-field">
                                    <label>Encryption <span class="req">*</span></label>
                                    <div class="smtp-input-box">
                                        <span class="smtp-icon"><i class="bx bx-lock-alt"></i></span>
                                        <select name="imap_encryption" required>
                                            <option value="ssl">SSL (Recommended)</option>
                                            <option value="tls">TLS</option>
                                            <option value="notls">No Encryption</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12 smtp-field">
                                    <label>Username <span class="req">*</span></label>
                                    <div class="smtp-input-box">
                                        <span class="smtp-icon"><i class="bx bx-user"></i></span>
                                        <input type="text" name="username" required placeholder="Your login email">
                                    </div>
                                </div>
                                <div class="col-md-12 smtp-field">
                                    <label>Password / App Key <span class="req">*</span></label>
                                    <div class="smtp-input-box">
                                        <span class="smtp-icon"><i class="bx bx-lock"></i></span>
                                        <input type="password" name="password" required placeholder="••••••••••••">
                                    </div>
                                </div>
                            </div>
                            <div class="smtp-form-footer mt-4">
                                <button type="submit" class="smtp-btn-save" style="background: #182848;">
                                    <i class="bx bx-save"></i> Save & Connect
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Active Inboxes Column ── --}}
            <div class="col-lg-4">
                <div class="dash-card p-4">
                    <h5 class="mb-3" style="font-size: 0.95rem; font-weight: 700; color: #182848;">
                        <i class="bx bx-list-ul me-1"></i> Active Inboxes
                    </h5>
                    @forelse($inboxes as $inbox)
                        <div class="inbox-card p-3 mb-3 border rounded-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold" style="font-size: 0.85rem;">{{ $inbox->email }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Last Sync: {{ $inbox->last_sync_at ? $inbox->last_sync_at->diffForHumans() : 'Never' }}</div>
                                </div>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bx bx-info-circle mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p style="font-size: 0.8rem;">No inboxes configured yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</section>

@include('smtpSettings_styles') {{-- Reusing styles from SMTP settings --}}

<style>
.inbox-card { transition: transform 0.2s; cursor: default; }
.inbox-card:hover { transform: translateY(-2px); border-color: #4b6cb7 !important; }
</style>

<script>
document.getElementById('globalSyncBtn').addEventListener('click', function() {
    let btn = this;
    let originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Syncing...';

    fetch("{{ route('inboxSync') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message || 'Sync failed', 'error');
        }
    })
    .catch(e => {
        Swal.fire('Error', 'Connection error', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
});
</script>

@endsection
