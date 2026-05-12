@extends('layout')
@section('title', 'SMTP Email Setup - Rusan')

@section('content')

<section class="task__section">
    @include('inc.header', ['title' => 'SMTP Email Setup'])

    <div class="dash-container">

        {{-- ── Status Banner ── --}}
        <div class="smtp-banner mb-4">
            <div class="smtp-banner-icon">
                <i class="bx bx-mail-send"></i>
            </div>
            <div class="smtp-banner-info">
                <div class="smtp-banner-title">Email Delivery Configuration</div>
                <div class="smtp-banner-sub">
                    Configure your outgoing mail server. These settings control how system emails
                    (invoices, notifications, campaigns) are sent to your clients and team.
                </div>
            </div>
            <div class="smtp-status-pill {{ !empty($smtpsetup?->host) ? 'smtp-configured' : 'smtp-unconfigured' }}">
                <i class="bx {{ !empty($smtpsetup?->host) ? 'bx-check-circle' : 'bx-error-circle' }}"></i>
                {{ !empty($smtpsetup?->host) ? 'Configured' : 'Not Configured' }}
            </div>
        </div>

        {{-- ── Quick Presets ── --}}
        <div class="smtp-preset-row mb-4">
            <span class="smtp-preset-label">Quick presets:</span>
            <button type="button" class="smtp-preset-btn" onclick="applyPreset('gmail')">
                <i class="bx bxl-google"></i> Gmail
            </button>
            <button type="button" class="smtp-preset-btn" onclick="applyPreset('outlook')">
                <i class="bx bx-envelope"></i> Outlook / Office365
            </button>
            <button type="button" class="smtp-preset-btn" onclick="applyPreset('mailtrap')">
                <i class="bx bx-bug-alt"></i> Mailtrap (Dev)
            </button>
            <button type="button" class="smtp-preset-btn" onclick="applyPreset('sendgrid')">
                <i class="bx bx-send"></i> SendGrid
            </button>
            <button type="button" class="smtp-preset-btn" onclick="applyPreset('ses')">
                <i class="bx bx-cloud"></i> Amazon SES
            </button>
        </div>

        {{-- ── Form Card ── --}}
        <div class="dash-card smtp-form-card">

            {{-- Header --}}
            <div class="smtp-form-header">
                <div>
                    <p class="smtp-form-header-title">
                        <i class="bx bx-cog me-1"></i>
                        SMTP Server Settings
                    </p>
                    <p class="smtp-form-header-sub">All fields are required for email delivery to work correctly</p>
                </div>
                <div class="smtp-secure-badge">
                    <i class="bx bx-shield-quarter"></i> Credentials stored securely
                </div>
            </div>

            {{-- Body --}}
            <div class="smtp-form-body">
                <form action="/smtp-settings" method="POST" id="smtpForm">
                    @csrf

                    {{-- ── SERVER SETTINGS ── --}}
                    <div class="smtp-section-title">Server Connection</div>
                    <div class="row g-3">

                        <div class="col-md-4 smtp-field">
                            <label>Mailer Driver <span class="req">*</span></label>
                            <div class="smtp-input-box">
                                <span class="smtp-icon"><i class="bx bx-envelope"></i></span>
                                <select name="mailer" id="mailer" required>
                                    <option value="">— Select driver —</option>
                                    @foreach(['smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'mailgun' => 'Mailgun', 'ses' => 'Amazon SES', 'postmark' => 'Postmark', 'log' => 'Log (Testing)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($smtpsetup?->mailer ?? 'smtp') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-5 smtp-field">
                            <label>SMTP Host <span class="req">*</span></label>
                            <div class="smtp-input-box">
                                <span class="smtp-icon"><i class="bx bx-server"></i></span>
                                <input type="text" name="host" id="host" required
                                       placeholder="e.g. smtp.gmail.com"
                                       value="{{ old('host', $smtpsetup?->host ?? '') }}">
                            </div>
                        </div>

                        <div class="col-md-3 smtp-field">
                            <label>SMTP Port <span class="req">*</span></label>
                            <div class="smtp-input-box">
                                <span class="smtp-icon"><i class="bx bx-transfer-alt"></i></span>
                                <input type="number" name="port" id="port" required
                                       placeholder="587"
                                       value="{{ old('port', $smtpsetup?->port ?? '') }}">
                            </div>
                        </div>

                        <div class="col-md-4 smtp-field">
                            <label>Encryption <span class="req">*</span></label>
                            <div class="smtp-input-box">
                                <span class="smtp-icon"><i class="bx bx-lock-alt"></i></span>
                                <select name="encryption" id="encryption" required>
                                    <option value="">— Select —</option>
                                    @foreach(['tls' => 'TLS (Recommended)', 'ssl' => 'SSL', 'starttls' => 'STARTTLS', '' => 'None'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ ($smtpsetup?->encryption ?? 'tls') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>

                    {{-- ── AUTHENTICATION ── --}}
                    <div class="smtp-section-title">Authentication</div>
                    <div class="row g-3">

                        <div class="col-md-6 smtp-field">
                            <label>SMTP Username <span class="req">*</span></label>
                            <div class="smtp-input-box">
                                <span class="smtp-icon"><i class="bx bx-user"></i></span>
                                <input type="text" name="username" id="username" required
                                       autocomplete="username"
                                       placeholder="your-email@example.com"
                                       value="{{ old('username', $smtpsetup?->username ?? '') }}">
                            </div>
                        </div>

                        <div class="col-md-6 smtp-field">
                            <label>SMTP Password <span class="req">*</span></label>
                            <div class="smtp-input-box" id="passBox">
                                <span class="smtp-icon"><i class="bx bx-lock"></i></span>
                                <input type="password" name="password" id="password" required
                                       autocomplete="new-password"
                                       placeholder="{{ !empty($smtpsetup?->password) ? '••••••••••••' : 'Enter SMTP password or app key' }}">
                                <button type="button" class="smtp-eye-btn" id="togglePass" title="Show/Hide">
                                    <i class="bx bx-hide" id="eyeIcon"></i>
                                </button>
                            </div>
                            @if(!empty($smtpsetup?->password))
                                <small class="text-muted" style="font-size:0.72rem; margin-top:3px;">
                                    <i class="bx bx-check-circle text-success"></i> Password saved — leave blank to keep unchanged
                                </small>
                            @endif
                        </div>

                    </div>

                    {{-- ── SENDER IDENTITY ── --}}
                    <div class="smtp-section-title">Sender Identity</div>
                    <div class="row g-3">

                        <div class="col-md-6 smtp-field">
                            <label>From Email Address <span class="req">*</span></label>
                            <div class="smtp-input-box">
                                <span class="smtp-icon"><i class="bx bx-mail-send"></i></span>
                                <input type="email" name="from_address" id="from_address" required
                                       placeholder="noreply@yourdomain.com"
                                       value="{{ old('from_address', $smtpsetup?->from_address ?? '') }}">
                            </div>
                            <small class="text-muted" style="font-size:0.72rem; margin-top:3px;">
                                Appears in recipient's inbox as the "From" address
                            </small>
                        </div>

                        <div class="col-md-6 smtp-field">
                            <label>From Name <span class="req">*</span></label>
                            <div class="smtp-input-box">
                                <span class="smtp-icon"><i class="bx bx-user-voice"></i></span>
                                <input type="text" name="from_name" id="from_name" required
                                       placeholder="e.g. Rusan Notifications"
                                       value="{{ old('from_name', $smtpsetup?->from_name ?? '') }}">
                            </div>
                            <small class="text-muted" style="font-size:0.72rem; margin-top:3px;">
                                Display name recipients see alongside the email address
                            </small>
                        </div>

                    </div>

                    {{-- ── TEST EMAIL ── --}}
                    <div class="smtp-section-title">Test Configuration</div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6 smtp-field">
                            <label>Send Test Email</label>
                            <div class="smtp-input-box">
                                <span class="smtp-icon"><i class="bx bx-test-tube"></i></span>
                                <input type="email" id="testEmailAddr"
                                       placeholder="Enter test recipient email…">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="smtp-test-btn" id="sendTestBtn">
                                <i class="bx bx-send"></i> Send Test Email
                            </button>
                        </div>
                        <div class="col-md-3" id="testResult" style="display:none;"></div>
                    </div>

                    {{-- ── FOOTER ── --}}
                    <div class="smtp-form-footer mt-4">
                        <button type="reset" class="smtp-btn-cancel">
                            <i class="bx bx-reset"></i> Reset
                        </button>
                        <button type="submit" class="smtp-btn-save">
                            <i class="bx bx-check"></i>
                            Save SMTP Settings
                        </button>
                    </div>

                </form>
            </div>

        </div>

        {{-- ── Help Tips ── --}}
        <div class="smtp-tips-row mt-4">
            <div class="smtp-tip-card">
                <i class="bx bxl-google smtp-tip-icon" style="color:#ea4335;"></i>
                <div>
                    <div class="smtp-tip-title">Gmail Tips</div>
                    <div class="smtp-tip-body">Use an <strong>App Password</strong> if 2FA is enabled. Host: <code>smtp.gmail.com</code>, Port: <code>587</code>, Encryption: <code>TLS</code></div>
                </div>
            </div>
            <div class="smtp-tip-card">
                <i class="bx bx-envelope smtp-tip-icon" style="color:#0078d4;"></i>
                <div>
                    <div class="smtp-tip-title">Outlook / Office 365</div>
                    <div class="smtp-tip-body">Host: <code>smtp.office365.com</code>, Port: <code>587</code>, Encryption: <code>STARTTLS</code></div>
                </div>
            </div>
            <div class="smtp-tip-card">
                <i class="bx bx-shield smtp-tip-icon" style="color:#163f7a;"></i>
                <div>
                    <div class="smtp-tip-title">Security Note</div>
                    <div class="smtp-tip-body">Always use <strong>TLS or SSL</strong>. Never use port <code>25</code> in production — it's blocked by most cloud providers.</div>
                </div>
            </div>
        </div>

    </div>
</section>

@include('smtpSettings_styles')

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Password Show / Hide ── */
    const toggleBtn  = document.getElementById('togglePass');
    const passInput  = document.getElementById('password');
    const eyeIcon    = document.getElementById('eyeIcon');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const isPassword = passInput.type === 'password';
            passInput.type   = isPassword ? 'text' : 'password';
            eyeIcon.className = isPassword ? 'bx bx-show' : 'bx bx-hide';
        });
    }

    /* ── Quick Presets ── */
    window.applyPreset = function (provider) {
        const presets = {
            gmail   : { host: 'smtp.gmail.com',       port: '587', encryption: 'tls',      mailer: 'smtp' },
            outlook : { host: 'smtp.office365.com',   port: '587', encryption: 'starttls', mailer: 'smtp' },
            mailtrap: { host: 'sandbox.smtp.mailtrap.io', port: '2525', encryption: 'tls', mailer: 'smtp' },
            sendgrid: { host: 'smtp.sendgrid.net',    port: '587', encryption: 'tls',      mailer: 'smtp' },
            ses     : { host: 'email-smtp.us-east-1.amazonaws.com', port: '587', encryption: 'tls', mailer: 'ses' },
        };
        const p = presets[provider];
        if (!p) return;
        document.getElementById('host').value       = p.host;
        document.getElementById('port').value       = p.port;
        document.getElementById('encryption').value = p.encryption;
        document.getElementById('mailer').value     = p.mailer;

        /* highlight applied preset button */
        document.querySelectorAll('.smtp-preset-btn').forEach(b => b.style.borderColor = '');
        event.currentTarget.style.borderColor = '#163f7a';
        event.currentTarget.style.color       = '#163f7a';
    };

    /* ── Send Test Email ── */
    const sendTestBtn = document.getElementById('sendTestBtn');
    if (sendTestBtn) {
        sendTestBtn.addEventListener('click', function () {
            const to = document.getElementById('testEmailAddr').value.trim();
            const result = document.getElementById('testResult');

            if (!to || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(to)) {
                result.style.display = 'flex';
                result.innerHTML = '<div class="smtp-result-err"><i class="bx bx-error-circle"></i> Enter a valid email address first.</div>';
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Sending…';
            result.style.display = 'none';

            fetch('/smtp-test', {
                method: 'POST',
                headers: {
                    'Content-Type'    : 'application/json',
                    'X-CSRF-TOKEN'    : document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({ to })
            })
            .then(r => r.json())
            .then(data => {
                result.style.display = 'flex';
                if (data.success) {
                    result.innerHTML = '<div class="smtp-result-ok"><i class="bx bx-check-circle"></i> Test email sent!</div>';
                } else {
                    result.innerHTML = `<div class="smtp-result-err"><i class="bx bx-error-circle"></i> ${data.message || 'Failed to send.'}</div>`;
                }
            })
            .catch(() => {
                result.style.display = 'flex';
                result.innerHTML = '<div class="smtp-result-err"><i class="bx bx-error-circle"></i> Request failed. Save settings first.</div>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-send"></i> Send Test Email';
            });
        });
    }
});
</script>

{{-- Flash ── --}}
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Swal !== 'undefined') {
        Swal.fire("Saved!", "{!! addslashes(session('success')) !!}", "success");
    } else if (typeof swal !== 'undefined') {
        swal("Saved!", "{!! addslashes(session('success')) !!}", "success");
    }
});
</script>
@endif
@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Swal !== 'undefined') {
        Swal.fire("Error", "{!! addslashes(session('error')) !!}", "error");
    } else if (typeof swal !== 'undefined') {
        swal("Error", "{!! addslashes(session('error')) !!}", "error");
    }
});
</script>
@endif

@endsection
