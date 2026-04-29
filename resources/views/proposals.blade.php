@extends('layout')
@section('title', 'Proposals - Rusan')

@section('content')
@php
    $roles     = session('roles');
    $roleArray = explode(',', ($roles->permissions ?? ''));

    // Stats
    $total    = $proposals->count();
    $draft    = $proposals->where('status', 'Draft')->count();
    $sent     = $proposals->where('status', 'Sent')->count();
    $accepted = $proposals->where('status', 'Accepted')->count();
    $declined = $proposals->where('status', 'Declined')->count();
    $expired  = $proposals->where('status', 'Expired')->count();
    $totalVal = $proposals->sum('grand_total');

    $statusConfig = [
        'Draft'    => ['#80868b', 'bx bx-pencil'],
        'Sent'     => ['#1a73e8', 'bx bx-send'],
        'Accepted' => ['#163f7a', 'bx bx-check-circle'],
        'Declined' => ['#ea4335', 'bx bx-x-circle'],
        'Expired'  => ['#f29900', 'bx bx-time-five'],
    ];
@endphp

<section class="task__section">
    @include('inc.header', ['title' => 'Proposals'])

    <div class="dash-container">

        {{-- ── Stat cards row ── --}}
        <div class="pr-stat-row mb-4">
            @php
                $statCards = [
                    ['Total Proposals', $total,    '#163f7a', 'bx bx-file'],
                    ['Draft',           $draft,    '#80868b', 'bx bx-pencil'],
                    ['Sent',            $sent,     '#1a73e8', 'bx bx-send'],
                    ['Accepted',        $accepted, '#163f7a', 'bx bx-check-circle'],
                    ['Total Value',     '₹'.number_format($totalVal, 0), '#163f7a', 'bx bx-rupee'],
                ];
            @endphp
            @foreach($statCards as [$label, $count, $color, $icon])
                @php
                    $filterVal = ($label == 'Total Proposals' || $label == 'Total Value') ? 'all' : $label;
                @endphp
                <div class="pr-stat-card pr-filter-trigger" data-filter="{{ $filterVal }}" style="cursor:pointer;">
                    <div class="pr-stat-icon" style="background:{{ $color }}15;color:{{ $color }};">
                        <i class="{{ $icon }}"></i>
                    </div>
                    <div>
                        <div class="pr-stat-count" style="color:{{ $label == 'Accepted' ? '#163f7a' : '#202124' }};">{{ $count }}</div>
                        <div class="pr-stat-label">{{ $label }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Toolbar ── --}}
        <div class="leads-toolbar mb-4">
            <div class="leads-toolbar-left gap-3">
                <span class="lb-page-count">
                    <i class="bx bx-file"></i>
                    {{ $total }} {{ $total == 1 ? 'Proposal' : 'Proposals' }}
                </span>
                <span class="ok-pipeline-total">
                    <i class="bx bx-rupee"></i>
                    {{ number_format($totalVal, 2) }}
                    <span class="ok-pipeline-label">Total Value</span>
                </span>
            </div>
            <div class="leads-toolbar-right gap-2">
                {{-- Status filter pills --}}
                <div class="pr-filter-group" id="statusFilterGroup">
                    <button class="pr-filter active" data-filter="all">All</button>
                    @foreach($statusConfig as $st => [$color, $icon])
                        <button class="pr-filter" data-filter="{{ $st }}"
                                style="--pr-color:{{ $color }}">
                            <i class="{{ $icon }}"></i> {{ $st }}
                        </button>
                    @endforeach
                </div>
                <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                    <i class="bx bx-refresh"></i>
                </button>
                @if(in_array('proposals_add', $roleArray) || in_array('All', $roleArray))
                    <a href="/manage-proposal" class="lb-btn lb-btn-primary">
                        <i class="bx bx-plus"></i> New Proposal
                    </a>
                @endif
            </div>
        </div>

        {{-- ── Proposals Table Card ── --}}
        <div class="leads-table-card">
            <div class="table-responsive">
                <table class="leads-table" id="lists">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th class="m-none">Client</th>
                            <th class="m-none">Company</th>
                            <th>Amount</th>
                            <th class="m-none">Date</th>
                            <th class="m-none">Open Till</th>
                            <th class="m-none">Project</th>
                            <th>Status</th>
                            <th class="m-none">Tags</th>
                            @if(in_array('proposals_edit',$roleArray) || in_array('proposals_delete',$roleArray) || in_array('All',$roleArray))
                                <th class="text-end position-sticky end-0" style="background-color:#ffffff !important; background-clip: padding-box; z-index:10; min-width:130px; box-shadow: -2px 0 5px rgba(0,0,0,0.05);">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proposals as $proposal)
                            @php
                                $st     = $proposal->status ?? 'Draft';
                                [$stColor, $stIcon] = $statusConfig[$st] ?? ['#80868b', 'bx bx-file'];
                                $openTill = \Carbon\Carbon::parse($proposal->open_till ?? now());
                                $isExpired = $openTill->isPast() && !in_array($st, ['Accepted','Declined','Expired']);
                            @endphp
                            <tr class="pr-row" data-status="{{ $st }}">
                                {{-- ID --}}
                                <td>
                                    <span class="pr-id-badge">
                                        PRO-{{ str_pad($proposal->id, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                {{-- Subject --}}
                                <td>
                                    <a href="/quotation/{{ $proposal->id }}/{{ md5($proposal->client_email) }}"
                                       class="pr-subject-link" target="_blank" title="View Proposal">
                                        {{ $proposal->subject ?? '—' }}
                                    </a>
                                </td>

                                {{-- Client name --}}
                                <td class="m-none">
                                    <span class="pr-client-cell">
                                        <span class="pr-client-avatar">
                                            {{ strtoupper(substr($proposal->client_name ?? 'U', 0, 1)) }}
                                        </span>
                                        {{ $proposal->client_name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Company --}}
                                <td class="m-none">
                                    <span class="text-muted small" title="{{ $proposal->company ?? '' }}">
                                        {{ Str::limit($proposal->company ?? '—', 18) }}
                                    </span>
                                </td>

                                {{-- Amount --}}
                                <td>
                                    <span class="pr-amount">
                                        <i class="bx bx-rupee"></i>
                                        {{ number_format($proposal->grand_total ?? 0, 0) }}
                                        <small class="text-muted">{{ $proposal->currency ?? '' }}</small>
                                    </span>
                                </td>

                                {{-- Created date --}}
                                <td class="m-none">
                                    <span class="small text-muted">
                                        {{ date_format(date_create($proposal->proposal_date ?? 'now'), 'd M, Y') }}
                                    </span>
                                </td>

                                {{-- Open till --}}
                                <td class="m-none">
                                    <span class="small {{ $isExpired ? 'text-danger fw-bold' : 'text-muted' }}">
                                        @if($isExpired)<i class="bx bx-error-circle"></i> @endif
                                        {{ date_format(date_create($proposal->open_till ?? 'now'), 'd M, Y') }}
                                    </span>
                                </td>

                                {{-- Project --}}
                                <td class="m-none">
                                    <span class="small text-muted">{{ $proposal->project_name ?? '—' }}</span>
                                </td>

                                {{-- Status pill --}}
                                <td data-search="{{ $st }}" data-order="{{ $st }}">
                                     <span class="pr-status-pill"
                                           style="background:{{ $stColor }}18;color:{{ $stColor }};">
                                        <i class="{{ $stIcon }}"></i>
                                        {{ $st }}
                                    </span>
                                </td>

                                {{-- Tags --}}
                                <td class="m-none">
                                    @if($proposal->tags)
                                        <span class="pr-tag-chip">{{ $proposal->tags }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                @if(in_array('proposals_edit',$roleArray) || in_array('proposals_delete',$roleArray) || in_array('All',$roleArray))
                                    <td class="text-end position-sticky end-0" style="background-color:#ffffff !important; background-clip: padding-box; z-index:5; box-shadow: -2px 0 5px rgba(0,0,0,0.02);">
                                        <div class="d-flex align-items-center justify-content-end gap-1" style="flex-wrap: nowrap;">
                                            <a href="/quotation/{{ $proposal->id }}/{{ md5($proposal->client_email) }}"
                                               class="btn kb-action-btn" title="View" target="_blank"
                                               style="background:rgba(26,115,232,0.08);color:#1a73e8; flex-shrink: 0;">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            @if(in_array('proposals_edit',$roleArray) || in_array('All',$roleArray))
                                                <a href="/manage-proposal?id={{ $proposal->id }}"
                                                   class="btn kb-action-btn" title="Edit"
                                                   style="background:rgba(22, 63, 122,0.08);color:#163f7a; flex-shrink: 0;">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                            @endif
                                            @if(in_array('proposals_delete',$roleArray) || in_array('All',$roleArray))
                                                <a href="javascript:void(0)"
                                                   class="btn kb-action-btn kb-action-del delete" title="Delete"
                                                   id="{{ $proposal->id }}" data-page="proposalDelete"
                                                   style="flex-shrink: 0;">
                                                    <i class="bx bx-trash"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11">
                                    <div class="kb-empty-col" style="padding:40px 0;">
                                        <i class="bx bx-file" style="font-size:2.2rem;"></i>
                                        <span>No proposals yet. Create your first one!</span>
                                        @if(in_array('proposals_add', $roleArray) || in_array('All', $roleArray))
                                            <a href="/manage-proposal" class="lb-btn lb-btn-primary mt-2">
                                                <i class="bx bx-plus"></i> New Proposal
                                            </a>
                                        @endif
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

<script>
$(document).ready(function () {
    // Ensure DataTable is initialized and get instance
    const table = $('#lists').DataTable({ retrieve: true });

    function applyFilter(f) {
        // Sync the pills UI
        $('.pr-filter').removeClass('active');
        $(`.pr-filter[data-filter="${f}"]`).addClass('active');

        if (f === 'all') {
            table.column(8).search('').draw();
        } else {
            // Exact match using regex to prevent partial matches (e.g. "Sent" matching "Sent to Client")
            table.column(8).search('^' + f + '$', true, false).draw();
        }
    }

    /* Status filter pills */
    $('#statusFilterGroup').on('click', '.pr-filter', function () {
        applyFilter($(this).data('filter'));
    });

    /* Stat cards row */
    $('.pr-stat-row').on('click', '.pr-filter-trigger', function () {
        applyFilter($(this).data('filter'));
    });
});
</script>
<style>
    /* ── Stat Row ── */
    .pr-stat-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 14px;
    }

    @media (max-width: 900px) {
        .pr-stat-row {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 576px) {
        .pr-stat-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .pr-stat-card {
        background: #fff;
        border: 1px solid #e8eaed;
        border-radius: 16px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: box-shadow 0.15s;
    }

    .pr-stat-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    }

    .pr-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .pr-stat-count {
        font-size: 1.35rem;
        font-weight: 800;
        color: #202124;
        line-height: 1;
    }

    .pr-stat-label {
        font-size: 0.72rem;
        color: #80868b;
        margin-top: 3px;
        font-weight: 500;
    }
</style>

@endsection
