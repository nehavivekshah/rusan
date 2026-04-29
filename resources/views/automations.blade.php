@extends('layout')
@section('title', 'Sales Automations - eseCRM')

@section('content')
    @php
        $totalCount = $automations->count();
        $activeCount = $automations->where('status', 'Active')->count();
        $inactiveCount = $automations->where('status', 'Inactive')->count();
        
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Sales Automations & Workflows'])

        <div class="dash-container">
            {{-- ── Stat Cards ── --}}
            <div class="rv-stat-row mb-4">
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                        <i class="bx bx-git-branch"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num">{{ $totalCount }}</div>
                        <div class="rv-stat-label">Total Workflows</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(52,168,83,0.1);color:#34a853;">
                        <i class="bx bx-bolt-circle"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#34a853;">{{ $activeCount }}</div>
                        <div class="rv-stat-label">Active & Running</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(128,134,139,0.1);color:#5f6368;">
                        <i class="bx bx-power-off"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#5f6368;">{{ $inactiveCount }}</div>
                        <div class="rv-stat-label">Paused</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(251,188,4,0.1);color:#f9a825;">
                        <i class="bx bx-history"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#f9a825;">Live</div>
                        <div class="rv-stat-label">System Status</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count">
                        <i class="bx bx-list-ul"></i>
                        {{ $totalCount }} {{ $totalCount == 1 ? 'Automation' : 'Automations' }}
                    </span>
                </div>
                <div class="leads-toolbar-right gap-2">
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    <button type="button" class="lb-btn lb-btn-primary" data-bs-toggle="modal" data-bs-target="#newAutomationModal">
                        <i class="bx bx-plus"></i>
                        <span class="d-none d-sm-inline">Create workflow</span>
                    </button>
                </div>
            </div>

            {{-- ── Table Card ── --}}
            <div class="dash-card mb-4">
                <div class="table-responsive">
                    <table id="lists" class="leads-table" style="width:100%;">
                        <thead>
                            <tr>
                                <th style="width:60px;">Enable</th>
                                <th>Workflow Trigger (When...)</th>
                                <th class="text-center" style="width:40px;"><i class="bx bx-right-arrow-alt"></i></th>
                                <th>Automated Action (Do this...)</th>
                                <th style="width:120px;">Current Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($automations as $auto)
                                <tr id="auto-row-{{ $auto->id }}">
                                    <td>
                                        <div class="form-check form-switch ms-2">
                                            <input class="form-check-input status-toggle custom-switch" type="checkbox"
                                                data-id="{{ $auto->id }}" {{ $auto->status === 'Active' ? 'checked' : '' }} style="cursor:pointer; width:36px; height:18px;">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lb-avatar-sm" style="background:rgba(26,115,232,0.1); color:#1a73e8; border:none;">
                                                <i class="bx bx-log-in-circle"></i>
                                            </div>
                                            <div class="fw-600 text-dark">{{ $auto->trigger_event }}</div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <i class="bx bx-chevron-right fs-4 text-muted opacity-50"></i>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lb-avatar-sm" style="background:rgba(52,168,83,0.1); color:#34a853; border:none;">
                                                <i class="bx bx-bolt-circle"></i>
                                            </div>
                                            <span class="fw-500 text-dark">{{ $auto->action }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $stColor = $auto->status === 'Active' ? '#34a853' : '#80868b';
                                        @endphp
                                        <span class="rv-status-pill status-badge" style="background:{{ $stColor }}15; color:{{ $stColor }};">
                                            <i class="bx {{ $auto->status === 'Active' ? 'bx-check-circle' : 'bx-pause-circle' }}"></i>
                                            {{ $auto->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="rv-empty">
                                            <i class="bx bx-git-branch"></i>
                                            <span>No automation workflows defined.</span>
                                            <button type="button" class="lb-btn lb-btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#newAutomationModal">
                                                <i class="bx bx-plus"></i> Create Your First Workflow
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- ── New Automation Modal ── --}}
    <div class="modal fade" id="newAutomationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-800" style="color:#006666;">Create Workflow Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('automations.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold small mb-2">1. WHEN THIS HAPPENS... (TRIGGER)</label>
                            <select name="trigger_event" class="form-select" style="border-radius:10px; padding:12px 15px; border-left: 4px solid #1a73e8;" required>
                                <option value="">Select Trigger</option>
                                <option value="Lead Created">New Lead Created</option>
                                <option value="Opportunity Closed Won">Opportunity Marked as Won</option>
                                <option value="Opportunity Stage Changed">Opportunity Stage Updated</option>
                                <option value="Task Overdue">Task becomes Overdue</option>
                            </select>
                            <div class="text-muted small mt-1">The event that initiates the logic.</div>
                        </div>

                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center" style="width:36px; height:36px; background:#f1f3f4; border-radius:50%; color:#5f6368;">
                                <i class="bx bx-down-arrow-alt fs-5"></i>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label text-muted fw-bold small mb-2">2. THEN DO THIS... (ACTION)</label>
                            <select name="action" class="form-select" style="border-radius:10px; padding:12px 15px; border-left: 4px solid #34a853;" required>
                                <option value="">Select Action</option>
                                <option value="Send Welcome Email">Send Welcome Email Template</option>
                                <option value="Send Thank You Email">Send Thank You Email</option>
                                <option value="Assign Next Task">Auto-assign follow-up call</option>
                                <option value="Notify Admin">Send Admin Notification</option>
                            </select>
                            <div class="text-muted small mt-1">The task the system executes automatically.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="lb-btn lb-btn-primary rounded-pill px-4 fw-bold">Enable Workflow</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* ── Page Layout ── */
        .dash-container { padding: 24px 24px 24px; }
        .rv-stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        @media (max-width: 991px) { .rv-stat-row { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 576px) { .rv-stat-row { grid-template-columns: repeat(1, 1fr); } }

        .rv-stat-card { background: #fff; border: 1px solid #e8eaed; border-radius: 16px; padding: 18px; display: flex; align-items: center; gap: 14px; transition: all 0.2s; }
        .rv-stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-color: #d2d4d7; }
        .rv-stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
        .rv-stat-num { font-size: 1.5rem; font-weight: 800; color: #202124; line-height: 1; }
        .rv-stat-label { font-size: 0.75rem; color: #80868b; margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }

        /* ── Status Pills ── */
        .rv-status-pill { display: inline-flex; align-items: center; gap: 4px; border-radius: 20px; padding: 4px 12px; font-size: 0.75rem; font-weight: 700; transition: all 0.2s; }
        .rv-status-pill i { font-size: 0.9rem; }

        /* ── Empty State ── */
        .rv-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; color: #9aa0a6; text-align: center; }
        .rv-empty i { font-size: 3rem; margin-bottom: 12px; color: #dadce0; }
        .rv-empty span { font-size: 0.95rem; font-weight: 500; display: block; margin-bottom: 15px; }

        /* ── Form Inputs ── */
        .form-control:focus, .form-select:focus { border-color: #006666; box-shadow: 0 0 0 0.2rem rgba(0, 102, 102, 0.1); }
        .custom-switch:checked { background-color: #34a853; border-color: #34a853; }
    </style>

    <script>
        $(document).ready(function () {
            $('.status-toggle').change(function () {
                let id = $(this).data('id');
                let row = $('#auto-row-' + id);
                let badge = row.find('.status-badge');

                $.post("{{ route('automations.toggle_status') }}", {
                    _token: "{{ csrf_token() }}",
                    id: id
                }, function (res) {
                    if (res.status === 'Active') {
                        badge.css({'background': '#34a85315', 'color': '#34a853'})
                             .html('<i class="bx bx-check-circle"></i> Active');
                    } else {
                        badge.css({'background': '#80868b15', 'color': '#80868b'})
                             .html('<i class="bx bx-pause-circle"></i> Inactive');
                    }
                }).fail(function () {
                    alert('Error updating automation status.');
                    // Revert toggle if failed
                    $(this).prop('checked', !$(this).is(':checked'));
                });
            });
        });
    </script>
@endsection
