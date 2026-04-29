@extends('layout')
@section('title', 'Users Management - eseCRM')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
        
        $totalUsers = $users->count();
        $activeCount = $users->where('status', '1')->count();
        $deactiveCount = $users->where('status', '2')->count();
        $adminCount = $users->where('roleFeatures', 'All')->count();
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Users Management'])

        <div class="dash-container">

            {{-- ── Stat Cards Row ── --}}
            <div class="pj-stat-row mb-4">
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                        <i class="bx bx-group"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $totalUsers }}</div>
                        <div class="pj-stat-label">Total Users</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(52,168,83,0.1);color:#34a853;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#34a853;">{{ $activeCount }}</div>
                        <div class="pj-stat-label">Active Accounts</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(234,67,53,0.1);color:#ea4335;">
                        <i class="bx bx-x-circle"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#ea4335;">{{ $deactiveCount }}</div>
                        <div class="pj-stat-label">Suspended</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(24bbc04,0.1);color:#fbbc04;">
                        <i class="bx bx-shield-quarter"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $adminCount }}</div>
                        <div class="pj-stat-label">Super Admins</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count">
                        {{ $totalUsers }} {{ $totalUsers == 1 ? 'User' : 'Users' }}
                    </span>
                </div>
                <div class="leads-toolbar-right gap-2">
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    @if(in_array('users_add',$roleArray) || in_array('All',$roleArray))
                        <a href="/manage-user" class="lb-btn lb-btn-primary">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">Add User</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- ════════════════════════════════
            TABLE VIEW
            ════════════════════════════════ --}}
            <div class="dash-card mb-4" style="background: #fff; border: 1px solid #e8eaed; border-radius: 12px; overflow: hidden;">
                <div class="table-responsive">
                    <table class="leads-table projects align-middle" id="lists" style="width:100%;">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Staff Details</th>
                                <th class="m-none">Contact Info</th>
                                <th class="m-none">Role Access</th>
                                <th class="text-center">System Status</th>
                                @if(in_array('users_edit',$roleArray) || in_array('users_delete',$roleArray) || in_array('All',$roleArray))
                                <th class="text-center position-sticky end-0 mw60" data-orderable="false" style="z-index:1;">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr class="project-row-click" data-url="/manage-user?id={{ $user->id }}">
                                    <td class="fw-bold text-muted" style="font-size:0.75rem;">
                                        #USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if(!empty($user->photo))
                                                <div class="lb-avatar-sm" style="background: transparent;">
                                                    <img src="{{ asset('assets/images/profile/' . $user->photo) }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
                                                </div>
                                            @else
                                                <div class="lb-avatar-sm" style="background:linear-gradient(135deg,#006666,#009688);color:#fff;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="fw-600 text-truncate" style="max-width:180px;">{{ $user->name ?? '--' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="m-none">
                                        <div class="small">
                                            <i class="bx bx-envelope text-muted"></i> {{ $user->email ?? '--' }}
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bx bx-phone"></i> {{ $user->mob ?? '--' }}
                                        </div>
                                    </td>
                                    <td class="m-none">
                                        <span class="pj-type-pill">{{ $user->title ?? 'Staff' }}</span>
                                        <div class="small text-muted mt-1">{{ $user->subtitle ?? 'General Access' }}</div>
                                    </td>
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <div class="form-check form-switch p-0 d-flex justify-content-center align-items-center flex-column gap-1">
                                            <input class="form-check-input status-toggle m-0" type="checkbox" role="switch" 
                                                   id="status_{{ $user->id }}" 
                                                   data-id="{{ $user->id }}"
                                                   style="cursor: pointer; width: 34px; height: 18px;"
                                                   {{ $user->status == '1' ? 'checked' : '' }}
                                                   {{ Auth::id() == $user->id ? 'disabled' : '' }}>
                                            <span class="small text-muted" style="font-size: 0.65rem;">
                                                {{ $user->status == '1' ? 'Active' : 'Deactive' }}
                                            </span>
                                        </div>
                                    </td>
                                    @if(in_array('users_edit',$roleArray) || in_array('users_delete',$roleArray) || in_array('All',$roleArray))
                                    <td class="position-sticky end-0 bg-white">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            @if(in_array('users_edit',$roleArray) || in_array('All',$roleArray))
                                            <a href="/manage-user?id={{ $user->id }}" class="btn kb-action-btn kb-action-edit" title="Edit">
                                                <i class="bx bx-pencil"></i>
                                            </a>
                                            @endif
                                            @if(in_array('users_delete',$roleArray) || in_array('All',$roleArray))
                                            <a href="javascript:void(0)" class="btn kb-action-btn kb-action-del delete" id="{{ $user->id }}" data-page="userDelete" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="kb-empty-col" style="padding:40px 0;">
                                            <i class="bx bx-group" style="font-size:2.5rem;"></i>
                                            <span>No users found.</span>
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

    <style>
        /* ── Project Stat Cards ── */
        .pj-stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        @media (max-width: 768px) { .pj-stat-row { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .pj-stat-row { grid-template-columns: 1fr; } }

        .pj-stat-card { background: #fff; border: 1px solid #e8eaed; border-radius: 14px; padding: 16px 18px; display: flex; align-items: center; gap: 14px; transition: box-shadow 0.18s; }
        .pj-stat-card:hover { box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); }
        .pj-stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
        .pj-stat-num { font-size: 1.2rem; font-weight: 700; color: #202124; line-height: 1.2; }
        .pj-stat-label { font-size: 0.72rem; color: #80868b; font-weight: 500; margin-top: 2px; }

        .pj-type-pill { display: inline-block; background: rgba(0, 102, 102, 0.08); color: #006666; font-size: 0.68rem; font-weight: 600; border-radius: 20px; padding: 2px 10px; }

        .form-check-input:checked { background-color: #006666; border-color: #006666; }

        /* Row clickable behavior */
        .project-row-click { cursor: pointer; }
        .project-row-click:hover { background-color: #f8f9fa; }
    </style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Row click navigation (matching projects)
    $('.project-row-click').on('click', function(e) {
        if (!$(e.target).closest('.status-toggle, .kb-action-btn').length) {
            window.location.href = $(this).data('url');
        }
    });

    // ── Status Toggle ──
    $('.status-toggle').on('change', function() {
        const userId = $(this).data('id');
        const isChecked = $(this).is(':checked');
        const newStatus = isChecked ? 1 : 2;
        const $label = $(this).siblings('span');

        $(this).prop('disabled', true);

        $.ajax({
            url: "{{ route('users.toggle_status') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}", id: userId, status: newStatus },
            success: function(response) {
                if (response.success) {
                    $label.text(newStatus == 1 ? 'Active' : 'Deactive');
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
                    Toast.fire({ icon: 'success', title: response.message });
                } else {
                    $('#status_' + userId).prop('checked', !isChecked);
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function(xhr) {
                $('#status_' + userId).prop('checked', !isChecked);
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred while updating status.';
                Swal.fire({ icon: 'error', title: 'Oops...', text: msg });
            },
            complete: function() { $('#status_' + userId).prop('disabled', false); }
        });
    });

    // ── Delete User ──
    // Direct binding (not delegation) because the td has inline onclick="event.stopPropagation()"
    // which kills bubbling before it reaches document. All rows are in the DOM at render time
    // (client-side DataTable only shows/hides them), so direct binding works across all pages.
    function bindDeleteHandlers() {
        $('.delete[data-page="userDelete"]').off('click.userDelete').on('click.userDelete', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const userId   = $(this).attr('id');
            const $row     = $(this).closest('tr');
            const userName = $row.find('.fw-600').first().text().trim() || 'this user';

            Swal.fire({
                title: 'Delete User?',
                html: `Are you sure you want to permanently delete <strong>${userName}</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ea4335',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/delete-user',
                        method: 'GET',
                        data: {
                            rowid:      userId,
                            userDelete: 'userDelete',
                            _token:     "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                // Remove row from DataTable cleanly
                                if ($.fn.DataTable.isDataTable('#lists')) {
                                    $('#lists').DataTable().row($row).remove().draw(false);
                                } else {
                                    $row.fadeOut(300, function() { $(this).remove(); });
                                }
                                const Toast = Swal.mixin({
                                    toast: true, position: 'top-end',
                                    showConfirmButton: false, timer: 3000, timerProgressBar: true
                                });
                                Toast.fire({ icon: 'success', title: 'User deleted successfully.' });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: response.error || 'Could not delete user.' });
                            }
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.error)
                                ? xhr.responseJSON.error
                                : 'An unexpected error occurred. Please try again.';
                            Swal.fire({ icon: 'error', title: 'Oops...', text: msg });
                        }
                    });
                }
            });
        });
    }

    // Bind on initial load
    bindDeleteHandlers();

    // Re-bind after every DataTable redraw (search / sort / page change)
    if ($.fn.DataTable.isDataTable('#lists')) {
        $('#lists').DataTable().on('draw', function() {
            bindDeleteHandlers();
        });
    }
});
</script>
@endpush
