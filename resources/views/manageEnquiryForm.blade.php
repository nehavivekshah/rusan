<div class="modal-header border-0 p-4" style="background: linear-gradient(90deg, #163f7a, #0f2d57);">
    <h5 class="modal-title fw-bold text-white">
        <i class="bx bx-envelope me-2"></i> {{ $enquiry ? 'Manage Enquiry' : 'New Enquiry' }}
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form action="{{ route('manageEnquiry') }}" method="POST" class="p-0">
    @csrf
    <input type="hidden" name="id" value="{{ $enquiry->id ?? '' }}">
    
    <div class="modal-body p-4 bg-light bg-opacity-50">
        {{-- Section 1: Contact Details --}}
        <div class="cf-section-title mb-4">
            <h6 class="fw-bold mb-1" style="color: #163f7a;">Contact & Identification</h6>
            <p class="text-muted small mb-0">Basic information captured from the landing page</p>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="cf-input-box">
                    <label class="form-label small text-muted">Lead Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bx bx-user text-teal"></i></span>
                        <input type="text" name="name" class="form-control border-start-0" value="{{ $enquiry->name ?? '' }}" required placeholder="Full Name">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="cf-input-box">
                    <label class="form-label small text-muted">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bx bx-envelope text-teal"></i></span>
                        <input type="email" name="email" class="form-control border-start-0" value="{{ $enquiry->email ?? '' }}" placeholder="email@example.com">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="cf-input-box">
                    <label class="form-label small text-muted">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bx bx-phone text-teal"></i></span>
                        <input type="text" name="mob" class="form-control border-start-0" value="{{ $enquiry->mob ?? '' }}" placeholder="+91 ...">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="cf-input-box">
                    <label class="form-label small text-muted">Inquiry Status</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bx bx-loader text-teal"></i></span>
                        <select name="status" class="form-select border-start-0">
                            <option value="0" {{ ($enquiry->status ?? 0) == 0 ? 'selected' : '' }}>New Request</option>
                            <option value="1" {{ ($enquiry->status ?? 0) == 1 ? 'selected' : '' }}>In Discussion / contacted</option>
                            <option value="2" {{ ($enquiry->status ?? 0) == 2 ? 'selected' : '' }}>Closed / Qualified</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Message Content --}}
        <div class="cf-section-title mt-5 mb-4 border-top pt-4">
            <h6 class="fw-bold mb-1" style="color: #163f7a;">Submission Details</h6>
            <p class="text-muted small mb-0">The message and context provided by the lead</p>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <div class="cf-input-box">
                    <label class="form-label small text-muted">Subject / Interest</label>
                    <input type="text" name="subject" class="form-control" value="{{ $enquiry->subject ?? '' }}" placeholder="What were they looking for?">
                </div>
            </div>
            <div class="col-12">
                <div class="cf-input-box">
                    <label class="form-label small text-muted">Message Content</label>
                    <textarea name="message" class="form-control" rows="5" placeholder="Enquiry message content...">{{ $enquiry->message ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer border-0 p-4 bg-white">
        <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal" style="border-radius: 12px; font-weight: 500;">Cancel</button>
        <button type="submit" class="btn btn-teal px-5 py-2" style="border-radius: 12px; background: #163f7a; color: white; border: none; font-weight: 600;">
            <i class="bx bx-check me-2"></i> Update Enquiry
        </button>
    </div>
</form>

<style>
    .text-teal { color: #163f7a; }
    .cf-input-box .form-control:focus, .cf-input-box .form-select:focus {
        border-color: #163f7a;
        box-shadow: 0 0 0 3px rgba(22, 63, 122, 0.1);
    }
    .cf-section-title h6 { position: relative; display: inline-block; }
    .cf-section-title h6::after {
        content: "";
        position: absolute;
        bottom: -4px;
        left: 0;
        width: 100%;
        height: 2px;
        background: #163f7a;
        border-radius: 2px;
    }
</style>
