@extends('layout')
@section('title', 'Marketing Campaigns - Rusan')

@section('content')
    @php
        $totalCount = $campaigns->count();
        $activeCount = $campaigns->where('status', 'Active')->count();
        $draftCount = $campaigns->where('status', 'Draft')->count();
        $distinctChannels = $campaigns->pluck('type')->unique()->count();

        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Marketing Campaigns'])

        <div class="dash-container">
            {{-- ── Stat Cards ── --}}
            <div class="rv-stat-row mb-4">
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                        <i class="bx bx-bullseye"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num">{{ $totalCount }}</div>
                        <div class="rv-stat-label">Total Campaigns</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(52,168,83,0.1);color:#34a853;">
                        <i class="bx bx-rocket"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#34a853;">{{ $activeCount }}</div>
                        <div class="rv-stat-label">Active</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(128,134,139,0.1);color:#5f6368;">
                        <i class="bx bx-edit-alt"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#5f6368;">{{ $draftCount }}</div>
                        <div class="rv-stat-label">Drafts</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(251,188,4,0.1);color:#f9a825;">
                        <i class="bx bx-broadcast"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#f9a825;">{{ $distinctChannels }}</div>
                        <div class="rv-stat-label">Channels Used</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count">
                        <i class="bx bx-list-ul"></i>
                        {{ $totalCount }} {{ $totalCount == 1 ? 'Campaign' : 'Campaigns' }}
                    </span>
                </div>
                <div class="leads-toolbar-right gap-2">
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    <button type="button" class="lb-btn lb-btn-primary" data-bs-toggle="modal" data-bs-target="#newCampaignModal">
                        <i class="bx bx-plus"></i>
                        <span class="d-none d-sm-inline">New Campaign</span>
                    </button>
                </div>
            </div>

            {{-- ── Table Card ── --}}
            <div class="dash-card mb-4">
                <div class="table-responsive">
                    <table id="lists" class="leads-table" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="m-none" style="width:40px;">#</th>
                                <th>Campaign Name</th>
                                <th>Channel Type</th>
                                <th style="width:120px;">Status</th>
                                <th class="text-center position-sticky end-0 bg-white" style="width:140px; border-left: 1px solid #f1f3f4; box-shadow: -4px 0 8px rgba(0,0,0,0.02); z-index: 10;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $k => $camp)
                                <tr id="camp-row-{{ $camp->id }}">
                                    <td class="m-none text-muted" style="font-size:0.78rem;">{{ $k + 1 }}</td>
                                    <td>
                                        <div class="fw-600 text-dark">{{ $camp->name }}</div>
                                        <div class="text-muted small">Created {{ $camp->created_at->format('d M, Y') }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @php
                                                $channelData = [
                                                    'WhatsApp' => ['bx bxl-whatsapp', '#25d366'],
                                                    'Email' => ['bx bx-envelope', '#ea4335'],
                                                    'SMS' => ['bx bx-message-rounded-dots', '#1a73e8'],
                                                    'Web Form' => ['bx bx-window-alt', '#006666']
                                                ][$camp->type] ?? ['bx bx-broadcast', '#5f6368'];
                                            @endphp
                                            <div class="lb-avatar-sm" style="background:{{ $channelData[1] }}15; color:{{ $channelData[1] }}; border:none;">
                                                <i class="{{ $channelData[0] }}"></i>
                                            </div>
                                            <span class="fw-500">{{ $camp->type }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $stColor = $camp->status === 'Active' ? '#34a853' : '#80868b';
                                        @endphp
                                        <span class="rv-status-pill" style="background:{{ $stColor }}15; color:{{ $stColor }};">
                                            <i class="bx {{ $camp->status === 'Active' ? 'bx-check-circle' : 'bx-time' }}"></i>
                                            {{ $camp->status }}
                                        </span>
                                    </td>
                                    <td class="position-sticky end-0 bg-white" style="border-left: 1px solid #f1f3f4; box-shadow: -4px 0 8px rgba(0,0,0,0.02); z-index: 9;">
                                        <div class="d-flex align-items-center justify-content-center gap-1 text-center">
                                            @if($camp->status === 'Draft')
                                                <button class="btn kb-action-btn launch-campaign" data-id="{{ $camp->id }}" title="Launch Campaign" style="background:rgba(26,115,232,0.1); color:#1a73e8;">
                                                    <i class="bx bx-rocket"></i>
                                                </button>
                                            @else
                                                <button class="btn kb-action-btn disabled" title="Running" style="background:rgba(52,168,83,0.1); color:#34a853;">
                                                    <i class="bx bx-check"></i>
                                                </button>
                                            @endif
                                            
                                            <button class="btn kb-action-btn delete-campaign" data-id="{{ $camp->id }}" title="Delete" style="background:rgba(234,67,53,0.1); color:#ea4335;">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="rv-empty">
                                            <i class="bx bx-broadcast"></i>
                                            <span>No marketing campaigns found.</span>
                                            <button type="button" class="lb-btn lb-btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#newCampaignModal">
                                                <i class="bx bx-plus"></i> Create Your First Campaign
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

    {{-- ── New Campaign Modal ── --}}
    <div class="modal fade" id="newCampaignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-800" style="color:#006666;">Create New Campaign</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('campaigns.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold small">Campaign Name*</label>
                            <input type="text" name="name" class="form-control" style="border-radius:10px; padding:10px 15px;" required placeholder="e.g. Festival Special Offer">
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-muted fw-bold small">Channel Type*</label>
                            <select name="type" class="form-select" style="border-radius:10px; padding:10px 15px;" required>
                                <option value="Email">Email Broadcast</option>
                                <option value="WhatsApp">WhatsApp Message</option>
                                <option value="SMS">SMS Text</option>
                                <option value="Web Form">Lead Capture Form</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="lb-btn lb-btn-primary rounded-pill px-4 fw-bold">Create Campaign Draft</button>
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
        .rv-status-pill { display: inline-flex; align-items: center; gap: 4px; border-radius: 20px; padding: 4px 12px; font-size: 0.75rem; font-weight: 700; }
        .rv-status-pill i { font-size: 0.9rem; }

        /* ── Action Buttons ── */
        .kb-action-btn { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; border: none; transition: transform 0.1s; }
        .kb-action-btn:hover { transform: scale(1.08); }
        .kb-action-btn:active { transform: scale(0.95); }

        /* ── Empty State ── */
        .rv-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; color: #9aa0a6; text-align: center; }
        .rv-empty i { font-size: 3rem; margin-bottom: 12px; color: #dadce0; }
        .rv-empty span { font-size: 0.95rem; font-weight: 500; display: block; margin-bottom: 15px; }

        /* ── Form Inputs ── */
        .form-control:focus, .form-select:focus { border-color: #006666; box-shadow: 0 0 0 0.2rem rgba(0, 102, 102, 0.1); }
    </style>

    <script>
        $(document).ready(function () {
            // Launch Campaign
            $('.launch-campaign').click(function () {
                let btn = $(this);
                let id = btn.data('id');
                let row = $('#camp-row-' + id);

                if (confirm('Review your settings. Are you ready to launch this campaign?')) {
                    btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i>');
                    
                    $.post("{{ route('campaigns.launch') }}", {
                        _token: "{{ csrf_token() }}",
                        id: id
                    }, function (res) {
                        if (res.success) {
                            row.find('.rv-status-pill').css({'background': '#34a85315', 'color': '#34a853'})
                               .html('<i class="bx bx-check-circle"></i> Active');
                            btn.replaceWith('<button class="btn kb-action-btn disabled" title="Running" style="background:rgba(52,168,83,0.1); color:#34a853;"><i class="bx bx-check"></i></button>');
                        } else {
                            alert('Launch failed. Please check campaign settings.');
                            btn.prop('disabled', false).html('<i class="bx bx-rocket"></i>');
                        }
                    }).fail(function () {
                        alert('Server error occurred.');
                        btn.prop('disabled', false).html('<i class="bx bx-rocket"></i>');
                    });
                }
            });

            // Delete Campaign
            $('.delete-campaign').click(function () {
                let btn = $(this);
                let id = btn.data('id');
                let row = $('#camp-row-' + id);

                if (confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) {
                    btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i>');

                    $.ajax({
                        url: "/campaigns/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (res) {
                            if (res.success) {
                                row.fadeOut(400, function() { $(this).remove(); });
                            } else {
                                alert(res.message || 'Deletion failed.');
                                btn.prop('disabled', false).html('<i class="bx bx-trash"></i>');
                            }
                        },
                        error: function () {
                            alert('Server error occurred.');
                            btn.prop('disabled', false).html('<i class="bx bx-trash"></i>');
                        }
                    });
                }
            });
        });
    </script>
@endsection
