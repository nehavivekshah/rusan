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

<style>
/* ════════════════════════════════════════
   SMTP Settings — Premium Page Styles
════════════════════════════════════════ */
.dash-container { padding: 24px; }

/* ── Banner ── */
.smtp-banner {
    display: flex; align-items: center; gap: 18px;
    background: linear-gradient(135deg, #005757, #163f7a);
    border-radius: 18px; padding: 20px 24px; color: #fff; flex-wrap: wrap;
}
.smtp-banner-icon {
    width: 56px; height: 56px; border-radius: 16px;
    background: rgba(255,255,255,0.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; flex-shrink: 0;
}
.smtp-banner-info { flex: 1; min-width: 0; }
.smtp-banner-title { font-size: 1.05rem; font-weight: 800; }
.smtp-banner-sub   { font-size: 0.78rem; opacity: .80; margin-top: 4px; line-height: 1.5; }
.smtp-status-pill  {
    display: inline-flex; align-items: center; gap: 6px;
    border-radius: 20px; padding: 7px 16px; font-size: 0.8rem; font-weight: 700; flex-shrink: 0;
}
.smtp-configured   { background: rgba(52,168,83,0.2); border: 1px solid rgba(52,168,83,0.4); color: #b7f5c8; }
.smtp-unconfigured { background: rgba(234,67,53,0.2); border: 1px solid rgba(234,67,53,0.4); color: #ffd0cc; }

/* ── Presets ── */
.smtp-preset-row {
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
}
.smtp-preset-label { font-size: 0.78rem; font-weight: 600; color: #5f6368; }
.smtp-preset-btn {
    display: inline-flex; align-items: center; gap: 5px;
    border: 1.5px solid #e0e4e8; background: #fff; border-radius: 20px;
    padding: 5px 14px; font-size: 0.78rem; font-weight: 600; color: #5f6368;
    cursor: pointer; transition: all 0.15s;
}
.smtp-preset-btn:hover { border-color: #163f7a; color: #163f7a; background: rgba(22, 63, 122,0.04); }

/* ── Form Card ── */
.smtp-form-card { border-radius: 18px; border: 1px solid #e8eaed; overflow: hidden; }

/* ── Card Header ── */
.smtp-form-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 28px;
    background: linear-gradient(135deg, #005757, #163f7a);
}
.smtp-form-header-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
.smtp-form-header-sub   { font-size: 0.74rem; color: rgba(255,255,255,.72); margin: 4px 0 0; }
.smtp-secure-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);
    color: rgba(255,255,255,0.9); border-radius: 20px; padding: 5px 14px;
    font-size: 0.75rem; font-weight: 600; white-space: nowrap;
}

/* ── Body ── */
.smtp-form-body { padding: 28px; background: #f4fbfb; }

/* ── Section title ── */
.smtp-section-title {
    font-size: 0.72rem; font-weight: 700; color: #163f7a;
    text-transform: uppercase; letter-spacing: .07em;
    margin: 24px 0 14px; padding-bottom: 5px;
    border-bottom: 1.5px solid rgba(22, 63, 122,.12);
}
.smtp-section-title:first-child { margin-top: 0; }

/* ── Field ── */
.smtp-field { display: flex; flex-direction: column; }
.smtp-field label { font-size: 0.78rem; color: #5f6368; margin-bottom: 5px; font-weight: 500; }
.smtp-field .req { color: #ea4335; }

/* ── Input Box ── */
.smtp-input-box {
    display: flex; align-items: center;
    border: 1.5px solid #d1d5db; border-radius: 10px;
    background: #fff; overflow: hidden;
    transition: border-color .15s, box-shadow .15s; height: 44px;
}
.smtp-input-box:focus-within {
    border-color: #163f7a;
    box-shadow: 0 0 0 3px rgba(22, 63, 122,.08);
}
.smtp-icon {
    display: flex; align-items: center; justify-content: center;
    width: 40px; height: 100%; flex-shrink: 0;
    color: #163f7a; font-size: 1.05rem;
    border-right: 1.5px solid #e8eaed; background: #f8fdfd;
}
.smtp-input-box input,
.smtp-input-box select {
    flex: 1; border: none !important; outline: none !important;
    box-shadow: none !important; background: transparent;
    font-size: 0.875rem; color: #202124; padding: 0 12px; height: 100%;
    appearance: none; -webkit-appearance: none;
}
.smtp-input-box input::placeholder { color: #9aa0a6; }
.smtp-input-box select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%235f6368'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center;
    padding-right: 28px;
}

/* ── Eye button ── */
.smtp-eye-btn {
    width: 40px; height: 100%; flex-shrink: 0; border: none;
    background: transparent; color: #80868b; font-size: 1.05rem;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: color .15s;
}
.smtp-eye-btn:hover { color: #163f7a; }

/* ── Test button ── */
.smtp-test-btn {
    width: 100%; height: 44px; border-radius: 10px;
    border: 1.5px solid #1a73e8; background: rgba(26,115,232,0.06);
    color: #1a73e8; font-size: 0.85rem; font-weight: 600;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    cursor: pointer; transition: all .15s;
}
.smtp-test-btn:hover { background: #1a73e8; color: #fff; }

/* ── Footer ── */
.smtp-form-footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding-top: 20px; border-top: 1px solid #e8eaed;
}
.smtp-btn-cancel {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 0.85rem; padding: 9px 20px; border-radius: 10px;
    border: 1.5px solid #d1d5db; background: #fff; color: #5f6368;
    cursor: pointer; transition: background .15s;
}
.smtp-btn-cancel:hover { background: #f5f5f5; }
.smtp-btn-save {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 0.85rem; font-weight: 700; padding: 9px 24px; border-radius: 10px;
    border: none; background: #163f7a; color: #fff;
    cursor: pointer; transition: background .15s;
}
.smtp-btn-save:hover { background: #004e4e; }

/* ── Result Badge ── */
.smtp-result-ok  { background: rgba(52,168,83,0.1); color:#163f7a; border-radius:8px; padding:8px 14px; font-size:0.82rem; font-weight:600; display:flex; align-items:center; gap:5px; }
.smtp-result-err { background: rgba(234,67,53,0.1); color:#ea4335; border-radius:8px; padding:8px 14px; font-size:0.82rem; font-weight:600; display:flex; align-items:center; gap:5px; }

/* ── Tips Row ── */
.smtp-tips-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
@media (max-width: 900px) { .smtp-tips-row { grid-template-columns: 1fr; } }
.smtp-tip-card {
    background: #fff; border: 1px solid #e8eaed; border-radius: 14px;
    padding: 16px 18px; display: flex; align-items: flex-start; gap: 14px;
}
.smtp-tip-icon { font-size: 1.8rem; flex-shrink: 0; margin-top: 2px; }
.smtp-tip-title { font-size: 0.82rem; font-weight: 700; color: #202124; margin-bottom: 5px; }
.smtp-tip-body  { font-size: 0.76rem; color: #5f6368; line-height: 1.6; }
.smtp-tip-body code { background: rgba(22, 63, 122,0.08); color: #163f7a; padding: 1px 5px; border-radius: 4px; font-size: 0.73rem; }

@media (max-width: 768px) {
    .smtp-form-body   { padding: 16px; }
    .smtp-form-header { padding: 16px 18px; flex-direction: column; align-items: flex-start; gap: 10px; }
    .smtp-banner      { flex-direction: column; }
}
</style>

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
