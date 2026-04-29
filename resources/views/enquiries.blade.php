@extends('layout')

@section('title', 'Enquiries - eseCRM')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => 'Landing Enquiries'])

        <div class="dash-container">

            {{-- ── Stat Cards Row ── --}}
            <div class="pj-stat-row mb-4">
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(0,102,102,0.1);color:#006666;">
                        <i class="bx bx-mail-send"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $stats['total'] }}</div>
                        <div class="pj-stat-label">Total Requests</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                        <i class="bx bx-envelope"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#1a73e8;">{{ $stats['new'] }}</div>
                        <div class="pj-stat-label">New Leads</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(255,193,7,0.1);color:#ffc107;">
                        <i class="bx bx-conversation"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#ffc107;">{{ $stats['contacted'] }}</div>
                        <div class="pj-stat-label">In Discussion</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(52,168,83,0.1);color:#34a853;">
                        <i class="bx bx-check-double"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#34a853;">{{ $stats['closed'] }}</div>
                        <div class="pj-stat-label">Qualified/Closed</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <div class="d-flex align-items-center gap-2">
                        <select id="statusFilter" class="form-select" style="width: auto; min-width: 140px;">
                            <option value="all">All Status</option>
                            <option value="0">New Leads</option>
                            <option value="1">Contacted</option>
                            <option value="2">Closed</option>
                        </select>
                        <!-- <div class="input-group search-box d-none d-md-flex" style="width: 240px;">
                                <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="enquirySearch" placeholder="Search leads...">
                            </div> -->
                    </div>

                    <!-- <span class="lb-page-count ms-3">
                            Showing {{ count($enquiries) }} records
                        </span> -->
                </div>
                <div class="leads-toolbar-right gap-2">
                    {{-- View Toggle --}}
                    <div class="pj-view-toggle">
                        <button class="pj-view-btn" id="cardViewBtn" title="Card View" onclick="setView('card')">
                            <i class="bx bx-grid-alt"></i>
                        </button>
                        <button class="pj-view-btn active" id="tableViewBtn" title="Table View" onclick="setView('table')">
                            <i class="bx bx-list-ul"></i>
                        </button>
                    </div>
                    <button class="lb-icon-btn active" id="openApiDocsBtn" title="API Documentation" data-bs-toggle="modal" data-bs-target="#apiDocsModal">
                        <i class="bx bx-code-alt"></i>
                    </button>
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>
            </div>

            {{-- ════════════════════════════════
            CARD VIEW
            ════════════════════════════════ --}}
            <div id="cardView" class="pj-card-grid mb-4" style="display:none;">
                @forelse($enquiries as $enquiry)
                    <div class="pj-card enquiry-card-wrapper open-enquiry-modal"
                        data-url="/manage-enquiry?id={{ $enquiry->id }}" data-status="{{ $enquiry->status }}"
                        data-search="{{ strtolower($enquiry->name . ' ' . $enquiry->email . ' ' . $enquiry->subject) }}">

                        {{-- Top accent --}}
                        <div class="pj-card-accent"
                            style="background: @if($enquiry->status == 0)#1a73e8 @elseif($enquiry->status == 1)#ffc107 @else#34a853 @endif;">
                        </div>

                        {{-- Header --}}
                        <div class="pj-card-header">
                            <div class="pj-card-avatar">
                                {{ strtoupper(substr($enquiry->name, 0, 1)) }}
                            </div>
                            <div class="pj-card-meta">
                                <div class="pj-card-name">{{ $enquiry->name }}</div>
                                <div class="pj-card-id">
                                    {{ $enquiry->created_at->format('d M, Y · h:i A') }}
                                </div>
                            </div>
                            <div class="pj-card-actions">
                                <button type="button" class="btn kb-action-btn open-enquiry-modal"
                                    data-url="/manage-enquiry?id={{ $enquiry->id }}" title="Edit">
                                    <i class="bx bx-pencil"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="pj-card-info mt-2">
                            <div class="pj-info-row">
                                <i class="bx bx-envelope"></i>
                                <span>{{ $enquiry->email ?? 'No email' }}</span>
                            </div>
                            <div class="pj-info-row">
                                <i class="bx bx-phone"></i>
                                <span>{{ $enquiry->mob ?? 'No phone' }}</span>
                            </div>
                        </div>

                        {{-- Subject Preview --}}
                        @if($enquiry->subject)
                            <div class="mt-2 p-2 bg-light rounded-3 small fw-500 border-start border-primary border-3">
                                {{ Str::limit($enquiry->subject, 50) }}
                            </div>
                        @endif

                        <p class="text-muted small mt-2 mb-0"
                            style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.8em;">
                            {{ $enquiry->message }}
                        </p>

                        {{-- Footer Badge --}}
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            @if($enquiry->status == 0)
                                <span class="pv-badge pv-badge-info">New Request</span>
                            @elseif($enquiry->status == 1)
                                <span class="pv-badge pv-badge-warning" style="background:rgba(255,193,7,0.1);color:#ffc107;">In
                                    Discussion</span>
                            @else
                                <span class="pv-badge pv-badge-success">Qualified</span>
                            @endif
                            <button class="btn btn-sm text-danger border-0 p-0 delete-enquiry-btn" data-id="{{ $enquiry->id }}">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="pj-empty" style="grid-column:1/-1;">
                        <i class="bx bx-mail-send"></i>
                        <p>No enquiries found.</p>
                    </div>
                @endforelse
            </div>

            {{-- ════════════════════════════════
            TABLE VIEW
            ════════════════════════════════ --}}
            <div id="tableView" class="dash-card mb-4"
                style="background: #fff; border: 1px solid #e8eaed; border-radius: 12px; overflow: hidden;">
                <div class="table-responsive">
                    <table class="leads-table projects align-middle" id="lists" style="width:100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Lead Details</th>
                                <th class="m-none">Contact Info</th>
                                <th class="m-none">Subject</th>
                                <th class="m-none">Received On</th>
                                <th class="text-center">Status</th>
                                <th class="text-center position-sticky end-0 mw60" data-orderable="false"
                                    style="z-index:1;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enquiries as $k => $enquiry)
                                <tr class="pointer-cursor selectrow enquiry-card-wrapper open-enquiry-modal"
                                    data-url="/manage-enquiry?id={{ $enquiry->id }}" data-status="{{ $enquiry->status }}"
                                    data-search="{{ strtolower($enquiry->name . ' ' . $enquiry->email . ' ' . $enquiry->subject) }}">
                                    <td class="fw-bold text-muted" style="font-size:0.75rem;">
                                        {{ $k + 1 }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lb-avatar-sm"
                                                style="background:linear-gradient(135deg,#1a73e8,#00c6ff);color:#fff;">
                                                {{ strtoupper(substr($enquiry->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-600 text-truncate" style="max-width:200px;">{{ $enquiry->name }}
                                                </div>
                                                <div class="small text-muted text-truncate" style="max-width:200px;">
                                                    {{ $enquiry->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="m-none">
                                        <div class="small fw-500">{{ $enquiry->email }}</div>
                                        <div class="small text-muted">{{ $enquiry->mob }}</div>
                                    </td>
                                    <td class="m-none">
                                        <div class="small text-truncate" style="max-width:250px;">
                                            {{ $enquiry->subject ?? 'General Enquiry' }}</div>
                                    </td>
                                    <td class="m-none">
                                        <div class="small">{{ $enquiry->created_at->format('d M, Y') }}</div>
                                        <div class="small text-muted text-uppercase" style="font-size:0.65rem;">
                                            {{ $enquiry->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($enquiry->status == 0)
                                            <span class="pv-badge pv-badge-info">New</span>
                                        @elseif($enquiry->status == 1)
                                            <span class="pv-badge pv-badge-warning"
                                                style="background:rgba(255,193,7,0.1);color:#ffc107;">In Contact</span>
                                        @else
                                            <span class="pv-badge pv-badge-success">Closed</span>
                                        @endif
                                    </td>
                                    <td class="position-sticky end-0 bg-white">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn kb-action-btn kb-action-edit open-enquiry-modal"
                                                data-url="/manage-enquiry?id={{ $enquiry->id }}" title="Edit">
                                                <i class="bx bx-pencil"></i>
                                            </button>
                                            <button class="btn kb-action-btn kb-action-del delete-enquiry-btn"
                                                data-id="{{ $enquiry->id }}" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>



        </div>
    </section>

    {{-- Enquiry Modal --}}
    <div class="modal fade" id="enquiryModal" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border:none;" id="enquiryModalContent">
                <!-- Content injected via AJAX -->
            </div>
        </div>
    </div>

    {{-- API Documentation Modal --}}
    <div class="modal fade" id="apiDocsModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 p-4" style="background: linear-gradient(90deg, #006666, #009688);">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bx bx-code-block me-2"></i> API Integration Guide
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <p class="text-muted mb-4">Integrate your external landing pages with the eseCRM lead capture system
                        using the details below.</p>

                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="p-3 bg-white rounded-3 shadow-sm border h-100">
                                <div class="small fw-bold mb-2 text-uppercase tracking-wider"
                                    style="font-size: 0.65rem; color: #006666;">Endpoint Details</div>
                                <div class="mb-3">
                                    <label class="small text-muted d-block">POST URL</label>
                                    <div
                                        class="p-2 bg-light rounded border small font-monospace mt-1 d-flex justify-content-between align-items-center">
                                        <span id="apiUrlText">{{ url('/enquiry-submit') }}</span>
                                        <i class="bx bx-copy cur-pointer text-primary" onclick="copyApiEndpoint()"
                                            title="Copy URL"></i>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0" style="font-size: 0.75rem;">
                                        <thead>
                                            <tr class="border-bottom text-muted">
                                                <th>Field</th>
                                                <th>Required</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td><code>name</code></td><td><span class="text-danger">Yes</span></td></tr>
                                            <tr><td><code>email</code></td><td>No</td></tr>
                                            <tr><td><code>mob</code></td><td>No</td></tr>
                                            <tr><td><code>subject</code></td><td>No</td></tr>
                                            <tr><td><code>message</code></td><td>No</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="p-3 bg-white rounded-3 shadow-sm border h-100">
                                <div class="small fw-bold mb-2 text-uppercase tracking-wider"
                                    style="font-size: 0.65rem; color: #006666;">Sample HTML Form</div>
                                <pre class="bg-dark text-white p-3 rounded-3 mb-0" style="font-size: 0.7rem; overflow-x: auto;">&lt;form action="{{ url('/enquiry-submit') }}" method="POST"&gt;
    &lt;input type="text" name="name" placeholder="Full Name" required&gt;
    &lt;input type="email" name="email" placeholder="Email"&gt;
    &lt;input type="text" name="mob" placeholder="Phone"&gt;
    &lt;textarea name="message"&gt;&lt;/textarea&gt;
    &lt;button type="submit"&gt;Submit Enquiry&lt;/button&gt;
&lt;/form&gt;</pre>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-white">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                        style="border-radius:12px;">Close</button>
                    <button type="button" class="btn btn-teal px-4" onclick="copyApiEndpoint()"
                        style="background:#006666; color:white; border-radius:12px;">
                        <i class="bx bx-copy me-2"></i> Copy Endpoint URL
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ── Reusing Company Stat Cards Styling ── */
        .pj-stat-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        @media (max-width: 768px) {
            .pj-stat-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .pj-stat-row {
                grid-template-columns: 1fr;
            }
        }

        .pj-stat-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 14px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: box-shadow 0.18s;
        }

        .pj-stat-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .pj-stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .pj-stat-num {
            font-size: 1.2rem;
            font-weight: 700;
            color: #202124;
            line-height: 1.2;
        }

        .pj-stat-label {
            font-size: 0.72rem;
            color: #80868b;
            font-weight: 500;
            margin-top: 2px;
        }

        /* ── View Toggle ── */
        .pj-view-toggle {
            display: flex;
            gap: 3px;
            background: #f1f3f4;
            border-radius: 20px;
            padding: 3px;
        }

        .pj-view-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: transparent;
            border-radius: 17px;
            cursor: pointer;
            color: #80868b;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }

        .pj-view-btn.active {
            background: #fff;
            color: #006666;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
        }

        /* ── Card Grid ── */
        .pj-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
        }

        .pj-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: box-shadow 0.2s, transform 0.18s;
            position: relative;
            padding: 0 16px 16px;
        }

        .pj-card:hover {
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.10);
            transform: translateY(-2px);
        }

        .pj-card-accent {
            height: 4px;
            margin: 0 -16px 14px;
        }

        .pj-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .pj-card-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #006666, #009688);
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pj-card-meta {
            flex: 1;
            min-width: 0;
        }

        .pj-card-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #202124;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pj-card-id {
            font-size: 0.68rem;
            color: #80868b;
        }

        .pj-card-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .pj-info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            color: #5f6368;
        }

        .pj-info-row i {
            font-size: 0.95rem;
            color: #006666;
        }

        .pv-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 600;
        }

        .pv-badge-success {
            background: rgba(52, 168, 83, 0.1);
            color: #34a853;
        }

        .pv-badge-info {
            background: rgba(26, 115, 232, 0.1);
            color: #1a73e8;
        }

        .pv-badge-warning {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .pj-empty {
            text-align: center;
            padding: 60px 20px;
            color: #9aa0a6;
        }

        .pj-empty i {
            font-size: 3rem;
            display: block;
            margin-bottom: 12px;
            color: #dadce0;
        }

        .pj-empty p {
            font-size: 0.85rem;
            margin: 0;
        }
    </style>

    <script>
        function setView(view) {
            const cardView = document.getElementById('cardView');
            const tableView = document.getElementById('tableView');
            const cardBtn = document.getElementById('cardViewBtn');
            const tableBtn = document.getElementById('tableViewBtn');

            if (view === 'card') {
                cardView.style.display = 'grid';
                tableView.style.display = 'none';
                cardBtn.classList.add('active');
                tableBtn.classList.remove('active');
                localStorage.setItem('enquiry_view_pref', 'card');
            } else {
                cardView.style.display = 'none';
                tableView.style.display = 'block';
                cardBtn.classList.remove('active');
                tableBtn.classList.add('active');
                localStorage.setItem('enquiry_view_pref', 'table');
            }
        }

        function copyApiEndpoint() {
            const el = document.createElement('textarea');
            el.value = '{{ url('/enquiry-submit') }}';
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);

            const btn = document.getElementById('copyApiBtn');
            const icon = btn.querySelector('i');
            const oldIcon = icon.className;

            icon.className = 'bx bx-check';
            btn.style.color = '#34a853';

            setTimeout(() => {
                icon.className = oldIcon;
                btn.style.color = '';
            }, 2000);
        }

        function loadEnquiryModal(url) {
            const content = document.getElementById('enquiryModalContent');
            const modalEl = document.getElementById('enquiryModal');

            content.innerHTML = '<div class="p-5 text-center"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:#006666;"></i><p class="mt-2 text-muted">Loading...</p></div>';

            bootstrap.Modal.getOrCreateInstance(modalEl).show();

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    content.innerHTML = html;
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const pref = localStorage.getItem('enquiry_view_pref') || 'table';
            setView(pref);

            // Filtering Logic
            const searchInput = document.getElementById('enquirySearch');
            const statusFilter = document.getElementById('statusFilter');

            function applyFilters() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusTerm = statusFilter.value;
                const items = document.querySelectorAll('.enquiry-card-wrapper');

                items.forEach(item => {
                    const searchData = item.dataset.search;
                    const statusData = item.dataset.status;
                    const matchesSearch = searchData.includes(searchTerm);
                    const matchesStatus = statusTerm === 'all' || statusData === statusTerm;

                    item.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
                    if (item.tagName === 'TR') {
                        item.style.display = (matchesSearch && matchesStatus) ? 'table-row' : 'none';
                    }
                });
            }

            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);

            // Global Modal Delegate
            document.addEventListener('click', function (e) {
                // Ignore if clicking delete
                if (e.target.closest('.delete-enquiry-btn') || e.target.closest('.btn-close')) {
                    return;
                }

                const trigger = e.target.closest('.open-enquiry-modal');
                if (trigger) {
                    e.preventDefault();
                    loadEnquiryModal(trigger.dataset.url);
                }

                // Delete logic
                const delBtn = e.target.closest('.delete-enquiry-btn');
                if (delBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm('Are you sure you want to delete this enquiry?')) {
                        window.location.href = '/delete-enquiry?id=' + delBtn.dataset.id;
                    }
                }
            });
        });
    </script>
@endsection