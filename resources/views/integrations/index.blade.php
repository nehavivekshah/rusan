@extends('layout')
@section('title', 'Integrations - Rusan')

@section('content')
<section class="task__section">
    @include('inc.header', ['title' => 'Third-Party Integrations'])

    <div class="dash-container">
        <div class="row g-4">
            <!-- Exotel Settings -->
            <div class="col-md-6">
                <div class="dash-card p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="lb-avatar-md bg-light text-primary me-3">
                            <i class="bx bx-phone fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Exotel Telephony</h5>
                            <small class="text-muted">Click-to-call integration settings</small>
                        </div>
                    </div>
                    
                    <form action="/integrations" method="POST">
                        @csrf
                        <input type="hidden" name="provider" value="exotel">
                        @php $exotel = $settings['exotel'][0] ?? null; @endphp
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Account SID</label>
                            <input type="text" name="account_sid" class="form-control" value="{{ $exotel->account_sid ?? '' }}" placeholder="Ex: account_name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">API Key</label>
                            <input type="text" name="api_key" class="form-control" value="{{ $exotel->api_key ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">API Token</label>
                            <input type="password" name="api_token" class="form-control" value="{{ $exotel->api_token ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">From Number (Caller ID)</label>
                            <input type="text" name="from_number" class="form-control" value="{{ $exotel->from_number ?? '' }}" placeholder="Ex: 080xxxxxxx">
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="status" {{ ($exotel->status ?? 0) ? 'checked' : '' }}>
                            <label class="form-check-label small">Enable Exotel Integration</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Save Exotel Settings</button>
                    </form>
                </div>
            </div>

            <!-- WhatsApp Settings -->
            <div class="col-md-6">
                <div class="dash-card p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="lb-avatar-md bg-light text-success me-3">
                            <i class="bx bxl-whatsapp fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">WhatsApp Business</h5>
                            <small class="text-muted">Meta Graph API settings</small>
                        </div>
                    </div>
                    
                    <form action="/integrations" method="POST">
                        @csrf
                        <input type="hidden" name="provider" value="whatsapp">
                        @php $wa = $settings['whatsapp'][0] ?? null; @endphp
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Phone Number ID</label>
                            <input type="text" name="account_sid" class="form-control" value="{{ $wa->account_sid ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Permanent Access Token</label>
                            <textarea name="api_token" class="form-control" rows="3">{{ $wa->api_token ?? '' }}</textarea>
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="status" {{ ($wa->status ?? 0) ? 'checked' : '' }}>
                            <label class="form-check-label small">Enable WhatsApp Automation</label>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold">Save WhatsApp Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
