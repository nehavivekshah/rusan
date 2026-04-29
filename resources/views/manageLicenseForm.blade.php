{{-- Manage License — AJAX Form Partial --}}
<style>
/* Premium Form Styling (.cf- scope) */
.cf-form-wrap { font-family: inherit; color: #202124; }
.cf-section-title {
    font-size: .75rem;
    font-weight: 700;
    color: #006666;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin: 24px 0 12px;
    padding-bottom: 6px;
    border-bottom: 2px solid rgba(0, 102, 102, 0.08);
}
.cf-section-title:first-child { margin-top: 0; }

.cf-label { font-size: 0.82rem; font-weight: 600; color: #5f6368; margin-bottom: 6px; display: block; }
.cf-input {
    width: 100%; padding: 10px 14px; border: 1.5px solid #e8eaed; border-radius: 10px;
    font-size: 0.88rem; transition: all 0.2s; background: #fff;
}
.cf-input:focus { border-color: #006666; outline: none; box-shadow: 0 0 0 3px rgba(0,102,102,0.1); }
.cf-input:placeholder { color: #9aa0a6; }
.cf-input:disabled { background: #f8f9fa; cursor: not-allowed; }

.cf-header {
    background: linear-gradient(135deg, #006666, #004d4d);
    padding: 24px 28px;
    border-radius: 16px 16px 0 0;
    color: #fff;
    position: relative;
}
.cf-title { font-size: 1.25rem; font-weight: 700; margin: 0; }
.cf-subtitle { font-size: 0.82rem; color: rgba(255,255,255,0.7); margin-top: 4px; }
.cf-close {
    position: absolute; top: 20px; right: 20px;
    background: rgba(0,0,0,0.2); color: #fff; border: none;
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: 0.2s;
}
.cf-close:hover { background: rgba(0,0,0,0.4); }

.cf-footer {
    padding: 20px 28px;
    background: #f8f9fa;
    border-top: 1px solid #e8eaed;
    border-radius: 0 0 16px 16px;
    display: flex; gap: 12px; justify-content: flex-end;
}

/* Generator Button */
.cf-gen-btn {
    background: #f1f3f4; color: #3c4043; border: 1px solid #dadce0;
    padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;
    margin-top: 8px;
}
.cf-gen-btn:hover { background: #e8eaed; border-color: #bdc1c6; }
</style>

<div class="cf-header">
    <button type="button" class="cf-close" data-bs-dismiss="modal"><i class="bx bx-x"></i></button>
    <h3 class="cf-title">{{ isset($license) ? 'Edit License' : 'Issue New License' }}</h3>
    <div class="cf-subtitle">{{ isset($license) ? 'Update activation parameters' : 'Generate and assign a new product key' }}</div>
</div>

<form id="manageLicenseForm" action="{{ route('manageLicense') }}" method="post" class="cf-form-wrap">
    @csrf
    <input type="hidden" name="id" value="{{ $license->id ?? '' }}">

    <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
        
        <div class="row g-4">
            {{-- Quick Select --}}
            <div class="col-12">
                <div class="cf-section-title">Project Assignment</div>
                <label class="cf-label">Select Project</label>
                <select class="cf-input" id="project_id_modal" name="project_id">
                    <option value="">-- Choose Assigned Project --</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" 
                            data-name="{{ $project->client_name ?? '' }}"
                            data-company="{{ $project->company ?? '' }}"
                            data-mobile="{{ $project->mob ?? '' }}"
                            data-email="{{ $project->email ?? '' }}"
                            data-projectname="{{ $project->name ?? '' }}"
                            data-type="{{ $project->type ?? '' }}"
                            data-cost="{{ $project->amount ?? '' }}"
                            data-website="{{ $project->deployment_url ?? '' }}"
                            data-note="{{ $project->note ?? '' }}"
                            {{ ($license && $license->project_id == $project->id) ? 'selected' : '' }}>
                            {{ $project->client_name ?? '' }} - {{ $project->name ?? '' }}
                        </option>
                    @endforeach
                </select>
                <div class="small text-muted mt-2">Selecting a project will auto-fill established client details.</div>
            </div>

            {{-- Client Info --}}
            <div class="col-md-6">
                <div class="cf-section-title">Client Information</div>
                <div class="mb-3">
                    <label class="cf-label">Full Name*</label>
                    <input type="text" class="cf-input" name="name" id="l_name" value="{{ $license->client_name ?? '' }}" required>
                </div>
                <div class="mb-3">
                    <label class="cf-label">Email Address*</label>
                    <input type="email" class="cf-input" name="email" id="l_email" value="{{ $license->email ?? '' }}" required>
                </div>
                <div class="mb-3">
                    <label class="cf-label">Mobile Number*</label>
                    <input type="text" class="cf-input" name="mobile" id="l_mob" value="{{ $license->mob ?? '' }}" required>
                </div>
            </div>

            {{-- Technical Details --}}
            <div class="col-md-6">
                <div class="cf-section-title">Product Details</div>
                <div class="mb-3">
                    <label class="cf-label">Application Name*</label>
                    <input type="text" class="cf-input" name="project_name" id="l_project_name" value="{{ $license->project_name ?? '' }}" required>
                </div>
                <div class="mb-3">
                    <label class="cf-label">Deployment URL*</label>
                    <input type="url" class="cf-input" name="website" id="l_website" value="{{ $license->deployment_url ?? '' }}" required placeholder="https://app.client.com">
                </div>
                <div class="mb-3">
                    <label class="cf-label">Tech Stack*</label>
                    <select class="cf-input" name="technology_stack" required>
                        <option value="Laravel" {{ ($license && $license->technology_stack == 'Laravel') ? 'selected' : '' }}>Laravel</option>
                        <option value="WordPress" {{ ($license && $license->technology_stack == 'WordPress') ? 'selected' : '' }}>WordPress</option>
                        <option value="Core PHP" {{ ($license && $license->technology_stack == 'Core PHP') ? 'selected' : '' }}>Core PHP</option>
                        <option value="Next.js" {{ ($license && $license->technology_stack == 'Next.js') ? 'selected' : '' }}>Next.js / React</option>
                        <option value="Node.js" {{ ($license && $license->technology_stack == 'Node.js') ? 'selected' : '' }}>Node.js / Express</option>
                    </select>
                </div>
            </div>

            {{-- License Parameters --}}
            <div class="col-12">
                <div class="cf-section-title">Activation Parameters</div>
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="cf-label">License Key*</label>
                        <input type="text" class="cf-input fw-bold text-primary" name="license_key" id="l_key" value="{{ $license->eselicense_key ?? '' }}" required readonly style="background:#f0f7f7; font-family:monospace; letter-spacing:1px;">
                        <button type="button" class="cf-gen-btn" id="gen_key_btn">
                            <i class="bx bx-refresh"></i> Regenerate Key
                        </button>
                    </div>
                    <div class="col-md-5">
                        <label class="cf-label">Expiry Date*</label>
                        <input type="date" class="cf-input" name="expiry_date" value="{{ $license->expiry_date ?? '' }}" required>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="cf-footer">
        <button type="button" class="lb-btn lb-btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="lb-btn lb-btn-primary px-4">
            <i class="bx bx-check-circle me-1"></i> {{ isset($license) ? 'Update License' : 'Issue License' }}
        </button>
    </div>
</form>

<script>
    (function() {
        const pSelect = document.getElementById('project_id_modal');
        const genBtn = document.getElementById('gen_key_btn');
        const keyInput = document.getElementById('l_key');

        // Auto-fill logic
        if(pSelect) {
            pSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if(opt.value) {
                    document.getElementById('l_name').value = opt.dataset.name || '';
                    document.getElementById('l_email').value = opt.dataset.email || '';
                    document.getElementById('l_mob').value = opt.dataset.mobile || '';
                    document.getElementById('l_project_name').value = opt.dataset.projectname || '';
                    document.getElementById('l_website').value = opt.dataset.website || '';
                }
            });
        }

        // Key generation logic
        function generateKey() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let key = '';
            for (let i = 0; i < 4; i++) {
                for (let j = 0; j < 4; j++) {
                    key += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                if (i < 3) key += '-';
            }
            keyInput.value = key;
        }

        if(genBtn) {
            genBtn.addEventListener('click', generateKey);
        }

        // Initial key if empty
        if(!keyInput.value) {
            generateKey();
        }
    })();
</script>
