@extends('layout')
@section('title', ($project ? 'Edit Project' : 'Add Project') . ' - Rusan')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => $project ? 'Edit Project' : 'New Project'])

        <div class="dash-container">

            {{-- Page Top Bar --}}
            <div class="ml-page-topbar mb-4">
                <div class="ml-page-topbar-left">
                    <a href="{{ $project ? url('/project/view/' . $project->id) : url('/projects') }}" class="ml-back-btn"
                        title="Back to Projects">
                        <i class="bx bx-arrow-back"></i>
                    </a>
                    <div>
                        <h1 class="ml-page-title">
                            {{ $project ? 'Edit Project' : 'New Project' }}
                        </h1>
                        <p class="ml-page-subtitle">
                            @if($project)
                                Editing <strong>{{ $project->name }}</strong> ·
                                #PROU-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}
                            @else
                                Fill in the details to create a new project
                            @endif
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if($project)
                        <div class="ml-lead-badge">
                            <i class="bx bx-edit-alt"></i> Editing
                        </div>
                    @else
                        <div class="ml-lead-badge ml-lead-badge-new">
                            <i class="bx bx-plus-circle"></i> New Entry
                        </div>
                    @endif
                </div>
            </div>

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger shadow-sm border-0 alert-dismissible" role="alert">
                    <div class="d-flex gap-2">
                        <i class="bx bx-error-circle fs-5" style="margin-top:2px;"></i>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Success notifications now handled beautifully by global swal popup in layout.blade.php --}}

            <form method="POST" action="/manage-project" id="projectForm">
                @csrf
                <input type="hidden" name="previous_url" value="{{ $previous_url ?? url()->previous() }}">
                @if($project)
                    <input type="hidden" name="id" value="{{ $project->id }}">
                @endif

                <div class="row g-4">

                    {{-- ── Main Form Card ── --}}
                    <div class="col-lg-8">
                        <div class="ml-card">
                            <div class="ml-card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="ml-card-icon" style="background:rgba(0,102,102,0.1);color:#006666;">
                                        <i class="bx bx-detail"></i>
                                    </div>
                                    <div>
                                        <h6 class="ml-card-title">Project Information</h6>
                                        <span class="ml-card-sub">Core details and categorization</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ $project ? url('/project/view/' . $project->id) : url('/projects') }}"
                                        class="lb-btn lb-btn-ghost btn-sm text-decoration-none">
                                        <i class="bx bx-x"></i> Cancel
                                    </a>
                                    <button type="submit" class="lb-btn lb-btn-primary btn-sm" id="saveBtn">
                                        <i class="bx bx-save"></i> {{ $project ? 'Save Changes' : 'Create Project' }}
                                    </button>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">

                                    {{-- Project Name --}}
                                    <div class="col-12">
                                        <label class="ml-label" for="name">Project Name <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-layer"></i></span>
                                            <input type="text" id="name" name="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                                placeholder="e.g. Website Redesign, ERP Implementation…"
                                                value="{{ old('name', $project->name ?? '') }}" required>
                                        </div>
                                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Custom Project ID --}}
                                    <div class="col-md-6">
                                        <label class="ml-label" for="project_id_custom">Project ID (e.g.
                                            ESE-2023-001)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                            <input type="text" id="project_id_custom" name="project_id_custom"
                                                class="form-control" placeholder="Custom identifier…"
                                                value="{{ old('project_id_custom', $project->project_id_custom ?? $generatedId ?? '') }}">
                                        </div>
                                    </div>

                                    {{-- Batch No. --}}
                                    <div class="col-md-6">
                                        <label class="ml-label" for="batchNo">Batch No.</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-barcode-reader"></i></span>
                                            <input type="text" id="batchNo" name="batchNo"
                                                class="form-control" placeholder="e.g. B-001, BATCH-2026…"
                                                value="{{ old('batchNo', $project->batchNo ?? '') }}">
                                        </div>
                                    </div>

                                    {{-- Closed By (Sales) --}}
                                    <div class="col-md-6">
                                        <label class="ml-label" for="closed_by">Closed By (Sales)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-user-plus"></i></span>
                                            <select id="closed_by" name="closed_by" class="form-select">
                                                <option value="">— Select Sales Person —</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ old('closed_by', $project->closed_by ?? '') == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Client --}}
                                    <div class="col-12">
                                        <label class="ml-label" for="client_id">Client <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-user-circle"></i></span>
                                            <select id="client_id" name="client_id"
                                                class="selectpicker form-control @error('client_id') is-invalid @enderror"
                                                data-live-search="true" data-style="form-select"
                                                data-width="calc(100% - 46px)" required>
                                                <option value="">— Select a client —</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" {{ old('client_id', $project->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                                        {{ $client->name }} @if($client->company) ({{ $client->company }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('client_id') <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                        @if($clients->isEmpty())
                                            <div class="text-muted small mt-1"><i class="bx bx-info-circle"></i> No clients
                                                found. <a href="/manage-client">Add a client first</a>.</div>
                                        @endif
                                    </div>

                                    {{-- Status --}}
                                    <div class="col-12">
                                        <label class="ml-label" for="status">Project Status</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-check-shield"></i></span>
                                            <select id="status" name="status" class="form-select">
                                                <option value="1" {{ old('status', $project->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ old('status', $project->status ?? 1) == 0 ? 'selected' : '' }}>Inactive / Completed</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Dates --}}
                                    <div class="col-md-6">
                                        <label class="ml-label" for="start_date">Start Date</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-calendar-event"></i></span>
                                            <input type="date" id="start_date" name="start_date" class="form-control"
                                                value="{{ old('start_date', $project->start_date ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label" for="deadline">Deadline</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-calendar-x"></i></span>
                                            <input type="date" id="deadline" name="deadline" class="form-control"
                                                value="{{ old('deadline', $project->deadline ?? '') }}">
                                        </div>
                                    </div>

                                    {{-- Project Type & Amount --}}
                                    <div class="col-md-6">
                                        <label class="ml-label" for="type">Project Type</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-category"></i></span>
                                            <input type="text" id="type" name="type" class="form-control"
                                                placeholder="Custom category…"
                                                value="{{ old('type', $project->type ?? '') }}">
                                        </div>
                                        <div class="d-flex gap-1 flex-wrap mt-2" id="typePills">
                                            @foreach(['Web', 'App', 'ERP', 'CRM', 'Design', 'Other'] as $t)
                                                <button type="button" class="type-pill" data-val="{{ $t }}"
                                                    onclick="selectType('{{ $t }}')">{{ $t }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label" for="amount">Contract Amount <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text fw-bold">₹</span>
                                            <input type="number" id="amount" name="amount"
                                                class="form-control @error('amount') is-invalid @enderror"
                                                placeholder="0.00" step="0.01" min="0"
                                                value="{{ old('amount', $project->amount ?? '') }}">
                                        </div>
                                        @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    {{-- Deployment URL & Tags --}}
                                    <div class="col-md-6">
                                        <label class="ml-label" for="deployment_url">Deployment / Live URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-globe"></i></span>
                                            <input type="url" id="deployment_url" name="deployment_url"
                                                class="form-control @error('deployment_url') is-invalid @enderror"
                                                placeholder="https://site.com"
                                                value="{{ old('deployment_url', $project->deployment_url ?? '') }}">
                                        </div>
                                        @error('deployment_url') <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label" for="tags">Tags (comma separated)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-purchase-tag-alt"></i></span>
                                            <input type="text" id="tags" name="tags" class="form-control"
                                                placeholder="e.g. urgent, v2.0..."
                                                value="{{ old('tags', $project->tags ?? '') }}">
                                        </div>
                                    </div>

                                    {{-- Notes --}}
                                    <div class="col-12">
                                        <label class="ml-label" for="note">Notes / Description</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-note"></i></span>
                                            <textarea id="note" name="note" class="form-control"
                                                placeholder="Project scope, tech stack…"
                                                rows="4">{{ old('note', $project->note ?? '') }}</textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Sidebar ── --}}
                    <div class="col-lg-4">
                        <div class="ml-card">
                            {{-- Actions migrated to Card Header --}}

                            {{-- Section: Live Preview --}}
                            <div class="ml-card-body text-center border-bottom bg-light"
                                style="background-color:rgba(0,102,102,0.02) !important;" id="previewCard">
                                <div class="mb-3">
                                    <span class="text-muted small fw-bold text-uppercase text-center">
                                        <i class="bx bx-show"></i> Live Preview
                                    </span>
                                </div>
                                <div class="preview-avatar" id="prevAvatar">
                                    {{ $project ? strtoupper(substr($project->name, 0, 1)) : '?' }}
                                </div>

                                <div class="mb-1" id="prevCustomIdRow" @if(!($project && $project->project_id_custom) && !isset($generatedId)) style="display:none;" @endif>
                                    <span id="prevCustomId" class="badge bg-light text-primary border fw-bold"
                                        style="font-size:0.65rem; padding:2px 6px;">
                                        {{ $project->project_id_custom ?? $generatedId ?? '' }}
                                    </span>
                                </div>

                                <div class="preview-name" id="prevName">{{ $project->name ?? 'Project Name' }}</div>
                                <div class="small text-muted mb-2" id="prevClient">
                                    {{ $project ? ($project->client_name ?? '— No Client Selected —') : '— No Client Selected —' }}
                                </div>

                                <div class="preview-type" id="prevType">{{ $project->type ?? 'Type' }}</div>
                                <div class="preview-amount" id="prevAmount">₹{{ number_format($project->amount ?? 0) }}
                                </div>

                                <div class="mt-3 d-flex flex-column gap-1 align-items-center">
                                    <div id="prevStatusBadge">
                                        @if($project)
                                            <span class="pv-badge pv-badge-{{ $project->status == 1 ? 'success' : 'info' }}">
                                                {{ $project->status == 1 ? 'Active' : 'Closed' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mt-1" id="prevTimelineRow" @if(!($project && ($project->start_date || $project->deadline))) style="display:none;" @endif
                                        style="font-size:0.7rem;">
                                        <i class="bx bx-calendar-event"></i>
                                        <span id="prevDates">
                                            @if($project && ($project->start_date || $project->deadline))
                                                {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M') : '—' }}
                                                to
                                                {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d M') : '—' }}
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                @if($project)
                                    <div class="text-muted small mt-3 pt-2 border-top" style="font-size:0.65rem;">
                                        <i class="bx bx-history"></i> Created
                                        {{ \Carbon\Carbon::parse($project->created_at)->format('d M, Y') }}
                                    </div>
                                @endif
                            </div>

                            {{-- Section: Quick Links --}}
                            @if($project)
                                <div class="ml-card-header border-0 pb-0">
                                    <h6 class="ml-card-title"><i class="bx bx-link"></i> Quick Links</h6>
                                </div>
                                <div class="ml-card-body p-2">
                                    <div class="d-flex flex-column gap-1">
                                        <a href="/project/view/{{ $project->id }}" class="qlink-item">
                                            <i class="bx bx-show"></i> View Project
                                        </a>
                                        <a href="/manage-recovery?id={{ $project->id }}" class="qlink-item">
                                            <i class="bx bx-receipt"></i> Add Recovery
                                        </a>
                                        <a href="/manage-license" class="qlink-item">
                                            <i class="bx bx-key"></i> Manage License
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="ml-card-body p-3 text-center">
                                    <span class="text-muted small italic">Quick links will be available after project
                                        creation</span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>{{-- /row --}}
            </form>
        </div>
    </section>

    <style>
        /* ── Minimal Custom CSS for Manage Project ── */
        .type-pill {
            background: #f1f3f4;
            border: 1.5px solid #e0e4e8;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #5f6368;
            cursor: pointer;
            transition: all 0.15s;
        }

        .type-pill:hover,
        .type-pill.active {
            background: rgba(0, 102, 102, 0.08);
            border-color: #006666;
            color: #006666;
        }

        .preview-avatar {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, #006666, #009688);
            color: #fff;
            font-size: 1.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }

        .preview-name {
            font-size: 1.1rem;
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
            padding: 2px 12px;
            margin-bottom: 8px;
        }

        .preview-amount {
            font-size: 1.35rem;
            font-weight: 800;
            color: #006666;
            letter-spacing: -0.02em;
        }

        .qlink-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #3c4043;
            text-decoration: none;
            transition: background 0.12s;
        }

        .qlink-item:hover {
            background: #f1f3f4;
            color: #006666;
        }

        .qlink-item i {
            font-size: 1rem;
            color: #006666;
        }

        .pv-badge {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 3px 12px;
            border-radius: 20px;
            display: inline-block;
        }

        .pv-badge-success {
            background: #e6f4ea;
            color: #1e8e3e;
        }

        .pv-badge-info {
            background: #e8f0fe;
            color: #1967d2;
        }
    </style>

    <script>
        $(document).ready(function () {
            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

            function formatDate(dateStr) {
                if (!dateStr) return null;
                const d = new Date(dateStr);
                return d.getDate() + " " + months[d.getMonth()];
            }

            // Live preview core fields
            $('#name').on('input', function () {
                const v = $(this).val().trim();
                $('#prevName').text(v || 'Project Name');
                $('#prevAvatar').text(v ? v.charAt(0).toUpperCase() : '?');
            });

            $('#project_id_custom').on('input', function () {
                const v = $(this).val().trim();
                if (v) {
                    $('#prevCustomId').text(v);
                    $('#prevCustomIdRow').show();
                } else {
                    $('#prevCustomIdRow').hide();
                }
            });

            $('#client_id').on('change', function () {
                const text = $(this).find('option:selected').text().trim();
                if ($(this).val()) {
                    $('#prevClient').text(text.split('(')[0].trim()); // Just name, remove company part from preview for brevity
                } else {
                    $('#prevClient').text('— No Client Selected —');
                }
            });

            $('#status').on('change', function () {
                const val = $(this).val();
                if (val == "1") {
                    $('#prevStatusBadge').html('<span class="pv-badge pv-badge-success">Active</span>');
                } else {
                    $('#prevStatusBadge').html('<span class="pv-badge pv-badge-info">Closed</span>');
                }
            });

            $('#start_date, #deadline').on('change input', function () {
                const start = formatDate($('#start_date').val());
                const end = formatDate($('#deadline').val());
                if (start || end) {
                    $('#prevDates').text((start || '—') + ' to ' + (end || '—'));
                    $('#prevTimelineRow').show();
                } else {
                    $('#prevTimelineRow').hide();
                }
            });

            $('#type').on('input', function () {
                $('#prevType').text($(this).val().trim() || 'Type');
                $('.type-pill').removeClass('active');
                $('.type-pill[data-val="' + $(this).val().trim() + '"]').addClass('active');
            });

            $('#amount').on('input', function () {
                const n = parseFloat($(this).val()) || 0;
                $('#prevAmount').text('₹' + n.toLocaleString('en-IN'));
            });

            // Trigger on load to populate preview with existing data
            $('#name, #project_id_custom, #type, #amount').trigger('input');
            $('#client_id, #status, #start_date').trigger('change');

            $('#projectForm').on('submit', function () {
                $('#saveBtn').html('<i class="bx bx-loader-alt bx-spin"></i> Saving…').prop('disabled', true);
            });
        });

        function selectType(val) {
            $('#type').val(val).trigger('input');
        }
    </script>
@endsection
