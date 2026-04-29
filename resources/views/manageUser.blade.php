@extends('layout')
@section('title', (Request::segment(1) != 'my-profile' ? ($users->id ?? false ? 'Edit User' : 'Add User') : 'My Account') . ' - eseCRM')

@section('content')

    @php
        $sessionroles = session('roles');
        $roleArray = explode(',',($sessionroles->permissions ?? ''));
        $userAssign = explode(',', ($users->assign ?? ''));
        $userFeaturs = explode(',', ($users->features ?? ''));
        $workingTime = json_decode($users->working_times ?? '', true) ?? [];
        $isMyProfile = Request::segment(1) == 'my-profile';
    @endphp
    
    <section class="task__section">
        @include('inc.header', ['title' => ($isMyProfile ? 'My Account' : 'Manage User')])

        <div class="dash-container">

            {{-- ── Page Top Bar ── --}}
            <div class="ml-page-topbar mb-4">
                <div class="ml-page-topbar-left">
                    @if(!$isMyProfile)
                        <a href="/users" class="ml-back-btn" title="Back to Users">
                            <i class="bx bx-arrow-back"></i>
                        </a>
                    @else
                        <div class="ml-back-btn" style="pointer-events: none; background: rgba(0,102,102,0.1); color: #006666;">
                            <i class="bx bx-user-circle"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="ml-page-title">
                            @if($isMyProfile)
                                My Account Settings
                            @elseif(!empty($_GET['id']))
                                Edit User Profile
                            @else
                                Add New User
                            @endif
                        </h1>
                        <p class="ml-page-subtitle">
                            @if($isMyProfile)
                                Manage your personal information and preferences
                            @elseif(!empty($_GET['id']))
                                Editing <strong>{{ $users->name }}</strong> · #USR-{{ str_pad($users->id, 4, '0', STR_PAD_LEFT) }}
                            @else
                                Fill in the details to register a new staff member
                            @endif
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if($isMyProfile)
                        <div class="ml-lead-badge ml-lead-badge-new" style="background:#e8f0fe; color:#1a73e8; border-color:#d2e3fc;">
                            <i class="bx bx-shield-quarter"></i> Personal Profile
                        </div>
                    @elseif(!empty($_GET['id']))
                        <div class="ml-lead-badge">
                            <i class="bx bx-edit-alt"></i> Editing
                        </div>
                    @else
                        <div class="ml-lead-badge ml-lead-badge-new">
                            <i class="bx bx-user-plus"></i> New Entry
                        </div>
                    @endif
                </div>
            </div>

            <form action="/manage-user" method="post" enctype="multipart/form-data" id="userForm">
                @csrf
                @if($isMyProfile)
                    <input type="hidden" name="id" value="{{ Auth::id() }}">
                @else
                    <input type="hidden" name="id" value="{{ $users->id ?? '' }}">
                @endif

                <div class="row g-4">

                    {{-- ── Main Form Card ── --}}
                    <div class="col-lg-8">
                        <div class="ml-card">
                            <div class="ml-card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="ml-card-icon" style="background:rgba(0,102,102,0.1);color:#006666;">
                                        <i class="bx bx-user-detail"></i>
                                    </div>
                                    <div>
                                        <h6 class="ml-card-title">Professional Information</h6>
                                        <span class="ml-card-sub">Core details and roles</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if(!$isMyProfile)
                                        <a href="/users" class="lb-btn lb-btn-ghost btn-sm text-decoration-none">
                                            <i class="bx bx-x"></i> Cancel
                                        </a>
                                    @endif
                                    <button type="submit" class="lb-btn lb-btn-primary btn-sm" id="saveBtn">
                                        <i class="bx bx-save"></i> Save Profile
                                    </button>
                                </div>
                            </div>

                            <div class="ml-card-body">
                                <div class="row g-3">
                                    
                                    {{-- Full Name --}}
                                    <div class="col-md-6">
                                        <label class="ml-label" for="name">Full Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                                            <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" value="{{ $users->name ?? '' }}" required>
                                        </div>
                                    </div>
                                    
                                    {{-- Email --}}
                                    <div class="col-md-6">
                                        <label class="ml-label" for="email">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                            <input type="email" id="email" name="email" class="form-control" placeholder="user@company.com" value="{{ $users->email ?? '' }}">
                                        </div>
                                    </div>

                                    {{-- Mobile --}}
                                    <div class="col-md-6">
                                        <label class="ml-label" for="mob">Mobile Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                            <input type="text" id="mob" name="mob" class="form-control" placeholder="+91 000000000" value="{{ $users->mob ?? '' }}">
                                        </div>
                                    </div>

                                    {{-- Password --}}
                                    @if(!$isMyProfile)
                                    <div class="col-md-6">
                                        <label class="ml-label" for="password">Access Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                                            <input type="password" id="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Role --}}
                                    <div class="col-md-6">
                                        <label class="ml-label" for="role">System Role <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-shield-quarter"></i></span>
                                            <select id="role" name="role" class="form-select" @if(($users->roleFeatures ?? '') == 'All') style="pointer-events:none; background-color:#f8f9fa;" @else required @endif>
                                                @if(($users->roleFeatures ?? '') == 'All')
                                                    <option value="">{{ $users->title ?? '' }} (Super Admin)</option>
                                                @else
                                                    <option value="">— Select a Role —</option>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}" @if(($users->role ?? '') == $role->id) selected @endif>
                                                            {{ $role->title }} ({{ $role->subtitle }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Status --}}
                                    <div class="col-md-6">
                                        <label class="ml-label" for="status">Account Status <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-check-shield"></i></span>
                                            <select id="status" name="status" class="form-select" required>
                                                <option value="1" @if(($users->status ?? '1') == '1') selected @endif>Active (Grant Access)</option>
                                                <option value="2" @if(($users->status ?? '1') == '2') selected @endif>Deactive (Suspend Access)</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>

                                {{-- Permissions & Features --}}
                                <div class="mt-4 mb-3 border-top pt-4 text-muted fw-bold" style="font-size: 0.8rem; text-transform: uppercase;">
                                    <i class="bx bx-slider-alt me-1"></i> Assignment & Scheduling
                                </div>
                                <div class="row g-3">
                                    @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray))
                                        <div class="col-md-6">
                                            <label class="ml-label">Assigned Team Members</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-network-chart"></i></span>
                                                <select class="selectpicker form-control" multiple data-live-search="true" data-selected-text-format="count > 2" data-container="body" name="assign[]" id="assignSelect">
                                                    @foreach($allusers as $user)
                                                        <option value="{{ $user->id }}" @if(in_array($user->id, $userAssign)) selected @endif>{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="ml-label">Enabled Modules</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bx bx-category"></i></span>
                                                <select class="selectpicker form-control" multiple data-live-search="true" data-container="body" name="features[]" id="featuresSelect">
                                                    <option value="tasks" @if(in_array('tasks', $userFeaturs)) selected @endif>Tasks</option>
                                                    <option value="leads" @if(in_array('leads', $userFeaturs)) selected @endif>Leads</option>
                                                    <option value="customers" @if(in_array('customers', $userFeaturs)) selected @endif>Customers</option>
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-md-6">
                                        <label class="ml-label">Working Hours (Start)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-time"></i></span>
                                            <input type="time" name="time[start]" class="form-control" value="{{ $workingTime['start'] ?? '10:00' }}" 
                                                   @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray)) @else readonly style="background:#f8f9fa;" @endif>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Working Hours (End)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-time-five"></i></span>
                                            <input type="time" name="time[end]" class="form-control" value="{{ $workingTime['end'] ?? '18:00' }}" 
                                                   @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray)) @else readonly style="background:#f8f9fa;" @endif>
                                        </div>
                                    </div>
                                </div>

                                {{-- Email Signature Text --}}
                                <div class="mt-4 mb-2">
                                    <label class="ml-label" for="emailSign">Email Signature Text (HTML Allowed)</label>
                                    <textarea class="form-control" id="emailSign" rows="4" name="emailSign" placeholder="Enter standard signature text...">{{ $users->esign ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Sidebar ── --}}
                    <div class="col-lg-4">
                        <div class="ml-card" id="previewCard">
                            {{-- Section: Live Preview --}}
                            <div class="ml-card-body text-center bg-light" style="background-color:rgba(0,102,102,0.02) !important;">
                                <div class="mb-3">
                                    <span class="text-muted small fw-bold text-uppercase text-center">
                                        <i class="bx bx-show"></i> Live Preview
                                    </span>
                                </div>
                                <div class="preview-avatar" id="prevAvatar" style="position: relative; cursor: pointer;" onclick="document.getElementById('profilePhoto').click()">
                                    @if(!empty($users->photo))
                                        <img id="avatar-img" src="{{ asset('assets/images/profile/' . $users->photo) }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
                                        <span id="avatar-initial" style="display:none;"></span>
                                    @else
                                        <img id="avatar-img" style="display:none; width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
                                        <span id="avatar-initial">{{ !empty($users->name) ? strtoupper(substr($users->name, 0, 1)) : '?' }}</span>
                                    @endif
                                    <div class="avatar-edit-overlay"><i class="bx bx-camera"></i></div>
                                </div>
                                <input type="file" id="profilePhoto" name="profilePhoto" class="d-none" accept="image/*">

                                <div class="mb-2">
                                    <div id="prevStatusBadge">
                                        @if(($users->status ?? '1') == '1')
                                            <span class="pv-badge pv-badge-success">Active</span>
                                        @else
                                            <span class="pv-badge pv-badge-info">Deactive</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="preview-name" id="prevName">{{ $users->name ?? 'User Name' }}</div>
                                <div class="small text-muted mb-2 d-flex align-items-center justify-content-center gap-1" id="prevContact">
                                    <i class="bx bx-envelope"></i> <span id="prevEmail">{{ $users->email ?? 'No email' }}</span>
                                </div>

                                <div class="preview-type" id="prevRole">{{ $users->title ?? 'Role' }}</div>

                                {{-- Signature Image Upload --}}
                                <div class="mt-4 px-3 pt-3 border-top text-start">
                                    <label class="ml-label mb-2 d-flex justify-content-between align-items-center">
                                        <span>Signature Image</span>
                                        <i class="bx bx-cloud-upload text-primary" style="font-size: 1.1rem; cursor: pointer;" onclick="document.getElementById('imgsign').click()"></i>
                                    </label>
                                    <div class="p-2 bg-white border border-dashed rounded text-center position-relative" style="min-height: 70px; border-style: dashed !important; cursor: pointer;" onclick="document.getElementById('imgsign').click()">
                                        @if(!empty($users->imgsign))
                                            <img id="sign-img" src="{{ asset('assets/images/signs/' . $users->imgsign) }}" style="max-height: 50px; max-width: 100%; object-fit: contain;">
                                        @else
                                            <div id="sign-placeholder" class="text-muted small py-2">
                                                <i class="bx bx-image-add d-block mb-1" style="font-size: 1.2rem;"></i> Click to upload
                                            </div>
                                            <img id="sign-img" style="display: none; max-height: 50px; max-width: 100%; object-fit: contain;">
                                        @endif
                                    </div>
                                    <input type="file" id="imgsign" name="imgsign" class="d-none" accept="image/*">
                                </div>

                                @if(!empty($_GET['id']))
                                    <div class="text-muted small mt-4 pt-2 border-top" style="font-size:0.65rem;">
                                        <i class="bx bx-id-card"></i> SYSTEM ID: #USR-{{ str_pad($users->id, 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>{{-- /row --}}
            </form>
        </div>
    </section>

    <style>
        .preview-avatar {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            background: linear-gradient(135deg, #006666, #009688);
            color: #fff;
            font-size: 1.8rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            box-shadow: 0 4px 10px rgba(0,102,102,0.2);
            overflow: hidden;
        }

        .avatar-edit-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.5);
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .preview-avatar:hover .avatar-edit-overlay {
            opacity: 1;
        }

        .preview-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: #202124;
            margin-bottom: 2px;
        }

        .preview-type {
            display: inline-block;
            background: rgba(0, 102, 102, 0.08);
            color: #006666;
            font-size: 0.72rem;
            font-weight: 600;
            border-radius: 20px;
            padding: 3px 12px;
            margin-top: 4px;
        }

        .pv-badge {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 3px 12px;
            border-radius: 20px;
            display: inline-block;
        }

        .pv-badge-success { background: #e6f4ea; color: #1e8e3e; }
        .pv-badge-info { background: #fce8e6; color: #d93025; }

        /* Bootstrap SelectOverrides to match input-group */
        .input-group .bootstrap-select .dropdown-toggle {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>

    <script>
        $(document).ready(function () {

            // Live preview core fields
            $('#name').on('input', function () {
                const v = $(this).val().trim();
                $('#prevName').text(v || 'User Name');
                
                // Only update initial if there is no image
                if ($('#avatar-img').css('display') === 'none') {
                    $('#avatar-initial').text(v ? v.charAt(0).toUpperCase() : '?');
                }
            });

            $('#email').on('input', function () {
                $('#prevEmail').text($(this).val().trim() || 'No email provided');
            });

            $('#role').on('change', function () {
                const text = $(this).find('option:selected').text().trim();
                if ($(this).val()) {
                    $('#prevRole').text(text.split('(')[0].trim());
                } else if($(this).val() === "") {
                    // It might be super admin disabled
                    if($(this).html().includes('Super Admin')) {
                        $('#prevRole').text('Super Admin');
                    } else {
                        $('#prevRole').text('Role');
                    }
                }
            });

            $('#status').on('change', function () {
                const val = $(this).val();
                if (val == "1") {
                    $('#prevStatusBadge').html('<span class="pv-badge pv-badge-success">Active</span>');
                } else {
                    $('#prevStatusBadge').html('<span class="pv-badge pv-badge-info">Deactive</span>');
                }
            });

            // Image Previews
            $('#profilePhoto').on('change', function(e) {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#avatar-img').attr('src', e.target.result).show();
                        $('#avatar-initial').hide();
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            $('#imgsign').on('change', function(e) {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#sign-img').attr('src', e.target.result).show();
                        if($('#sign-placeholder').length) {
                            $('#sign-placeholder').hide();
                        }
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Trigger on load
            $('#name, #email').trigger('input');
            $('#role, #status').trigger('change');

            // Button Loading State
            $('#userForm').on('submit', function () {
                $('#saveBtn').html('<i class="bx bx-loader-alt bx-spin"></i> Saving…').prop('disabled', true);
            });
            
            // Re-apply selectpicker for dynamic size
            setTimeout(function() {
                $('.selectpicker').selectpicker('refresh');
            }, 100);
        });
    </script>
@endsection
