{{-- Modal: Client Details --}}
<div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width:900px;">
        <div class="modal-content" style="border-radius:16px; border:none; overflow:hidden;">

            {{-- Header (matching cf-modal-header / ld-header) --}}
            <div class="ld-header">
                <div class="ld-header-content d-flex justify-content-between align-items-center w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="ld-avatar" id="clientAvatarBadge">C</div>
                        <div class="text-white">
                            <h5 class="ld-name mb-0" id="clientModalLabel">Customer Details</h5>
                            <div class="d-flex align-items-center gap-2 small opacity-75 mt-1">
                                <span class="ld-company" id="clientAvatarSub" style="font-size:0.85rem;color:#e8f0fe;">Loading...</span>
                                <span class="lb-dot" style="background:#fff;"></span>
                                <span id="clientSince" style="font-size:0.8rem;">Added on —</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="#" id="c_btnCall" class="ld-quick-btn" title="Call"><i class="bx bx-phone"></i></a>
                        <a href="#" id="c_btnWa" class="ld-quick-btn" style="background:rgba(37,211,102,0.2) !important; color:#25D366 !important;" title="WhatsApp" target="_blank"><i class="bx bxl-whatsapp"></i></a>
                        <a href="#" id="c_btnMail" class="ld-quick-btn" title="Email"><i class="bx bx-envelope"></i></a>
                        <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="Close" style="opacity:.8;"></button>
                    </div>
                </div>
            </div>

            <div class="modal-body p-0 ld-body">
                {{-- Tabs Navigation --}}
                <div class="ld-tab-nav">
                    <button class="ld-tab active" onclick="cTab(this, 'c-tab-info')">
                        <i class="bx bx-user"></i> Profile
                    </button>
                    <button class="ld-tab" onclick="cTab(this, 'c-tab-timeline')">
                        <i class="bx bx-history"></i> Timeline
                    </button>
                    <button class="ld-tab" onclick="cTab(this, 'c-tab-props')">
                        <i class="bx bx-file"></i> Proposals
                    </button>
                    <button class="ld-tab" onclick="cTab(this, 'c-tab-projects')">
                        <i class="bx bx-briefcase"></i> Projects
                    </button>
                </div>

                <div class="ld-tab-container">

                    {{-- Profile Tab --}}
                    <div id="c-tab-info" class="ld-tab-content active">
                        <div class="ld-info-grid">
                            {{-- Contact Card --}}
                            <div class="ld-info-card">
                                <h6><i class="bx bx-phone-call"></i> Contact Information</h6>
                                <div class="ld-info-row">
                                    <span class="label">Primary Phone</span>
                                    <span class="value" id="c_mob">—</span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="label">WhatsApp</span>
                                    <span class="value" id="c_wa">—</span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="label">Email Address</span>
                                    <span class="value" id="c_email">—</span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="label">Website</span>
                                    <span class="value"><a href="#" id="c_website" target="_blank">—</a></span>
                                </div>
                            </div>

                            {{-- Business Card --}}
                            <div class="ld-info-card">
                                <h6><i class="bx bx-building-house"></i> Business Details</h6>
                                <div class="ld-info-row">
                                    <span class="label">Company Name</span>
                                    <span class="value" id="c_company_val">—</span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="label">GST Number</span>
                                    <span class="value" id="c_gst">—</span>
                                </div>
                                <div class="ld-info-row">
                                    <span class="label">Position</span>
                                    <span class="value" id="c_position">—</span>
                                </div>
                            </div>

                            {{-- CRM Intelligence --}}
                            <div class="ld-info-card" style="grid-column: 1 / -1;">
                                <h6><i class="bx bx-brain"></i> CRM Intelligence</h6>
                                <div class="row g-0">
                                    <div class="col-md-6 pe-md-3" style="border-right:1.5px solid #f1f3f4;">
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-target-lock"></i> Purpose</span>
                                            <span class="ld-info-val" id="c_purpose">—</span>
                                        </div>
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-rupee"></i> Lead Value</span>
                                            <span class="ld-info-val text-success fw-bold" id="c_value">—</span>
                                        </div>
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-user-check"></i> POC</span>
                                            <span class="ld-info-val" id="c_poc">—</span>
                                        </div>
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-layer"></i> Lifecycle Stage</span>
                                            <span class="ld-info-val" id="c_stage">—</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 ps-md-3">
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-building"></i> Industry</span>
                                            <span class="ld-info-val" id="c_industry_val">—</span>
                                        </div>
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-purchase-tag-alt"></i> Tags</span>
                                            <span class="ld-info-val" id="c_tags">—</span>
                                        </div>
                                        <div class="ld-info-row">
                                            <span class="ld-info-label"><i class="bx bx-map"></i> Location</span>
                                            <span class="ld-info-val text-muted small" id="c_location_full">—</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 pt-0">
                            <a href="#" id="c_editBtn" class="lb-btn lb-btn-primary w-100">
                                <i class="bx bx-pencil"></i> Edit Customer Full Profile
                            </a>
                        </div>
                    </div>

                    {{-- Timeline Tab --}}
                    <div id="c-tab-timeline" class="ld-tab-content" style="display:none;">
                        <div class="p-4">
                            <div class="ld-timeline" id="c_timeline">
                                {{-- Timeline items injected here --}}
                            </div>
                        </div>
                    </div>

                    {{-- Proposals Tab --}}
                    <div id="c-tab-props" class="ld-tab-content" style="display:none;">
                        <div class="p-0">
                            <table class="table leads-table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Subject</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="c_proposals">
                                    <tr><td colspan="4" class="text-center py-4">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Projects Tab --}}
                    <div id="c-tab-projects" class="ld-tab-content" style="display:none;">
                        <div class="p-0">
                            <table class="table leads-table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Project</th>
                                        <th>Value</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody id="c_projects">
                                    <tr><td colspan="3" class="text-center py-4">Loading...</td></tr>
                                </tbody>
                            </table>

                            <div class="p-3 bg-light border-top">
                                <h6 class="mb-3 small text-uppercase fw-bold text-muted">Related Invoices</h6>
                                <table class="table leads-table mb-0 bg-white shadow-sm rounded">
                                    <thead>
                                        <tr>
                                            <th>Inv #</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="c_invoices">
                                        <tr><td colspan="4" class="text-center py-3 small text-muted">No invoices found.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
