@extends('layout')
@section('title', 'CRM Follow-Up Tasks - Rusan')

@section('content')

    <section class="task__section">
        @include('inc.header', ['title' => 'CRM Follow-Up Tasks'])

        <div class="dash-container">

            {{-- Toolbar --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left d-flex align-items-center gap-3">
                    <span class="lb-page-count d-flex align-items-center">
                        <i class="bx bx-task me-1"></i>
                        Task Board &mdash; <span id="memberCount" class="mx-1">{{ count($users) }}</span> Members
                    </span>
                    
                    {{-- Status Legend --}}
                    <div class="d-md-flex align-items-center gap-2 border-start ps-3 border-light m-none">
                        <span class="tb-legend tb-legend-urgent">Urgent</span>
                        <span class="tb-legend tb-legend-pending">Pending</span>
                        <span class="tb-legend tb-legend-progress">In Progress</span>
                        <span class="tb-legend tb-legend-done">Done</span>
                        <span class="tb-legend tb-legend-closed">Closed</span>
                    </div>
                </div>
                <div class="leads-toolbar-right">

                    {{-- Project Filter --}}
                    <div class="tk-project-filter-wrap">
                        <form method="GET" action="/task" id="projectFilterForm">
                            <div class="tk-project-filter-box" style="border:none; padding:0; background:transparent;">
                                <select name="project_id" id="projectFilterSelect" class="tk-project-filter-select"
                                        onchange="document.getElementById('projectFilterForm').submit()">
                                    <option value="">All Projects</option>
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ $activeProjectId == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    {{-- Search --}}
                    <div class="tb-search-wrap">
                        <form method="post" autocomplete="off" onsubmit="return false;">
                            @csrf
                            <div class="tb-search-box">
                                <i class="bx bx-search tb-search-icon"></i>
                                <input type="text" id="taskSearch" name="taskSearch"
                                       placeholder="Search tasks…" class="tb-search-input" />
                            </div>
                        </form>
                        {{-- Dropdown OUTSIDE the search-box so absolute positioning works --}}
                        <div class="searchTaskResult">
                            <ul id="tsdata"></ul>
                        </div>
                    </div>

                    {{-- Status Legend moved to left --}}

                    @if($canAddTask)
                        <button type="button" class="lb-btn lb-btn-primary ms-3" onclick="openAddTaskOffcanvas('{{ Auth::id() }}')">
                            <i class="bx bx-plus"></i> Add Task
                        </button>
                    @endif
                </div>
            </div>

            {{-- Active project badge --}}
            @if($activeProjectId)
                @php $activeProject = $projects->firstWhere('id', $activeProjectId); @endphp
                @if($activeProject)
                    <div class="tk-active-filter-bar mb-3">
                        <i class="bx bx-filter-alt"></i>
                        Filtered by project: <strong>{{ $activeProject->name }}</strong>
                        <a href="/task" class="tk-clear-filter" title="Clear filter">
                            <i class="bx bx-x"></i> Clear
                        </a>
                    </div>
                @endif
            @endif

            {{-- Kanban Board --}}
            <input type="hidden" id="userCount" value="{{ count($users) }}" />

            <div class="tk-board">
                @php
                    $colColors = [
                        '#1a73e8', '#9334e9', '#163f7a', '#f29900',
                        '#163f7a', '#ea4335', '#0b8043', '#e52592'
                    ];
                @endphp

                @foreach ($kanbanData as $idx => $column)
                    @php
                        $accent  = $colColors[$idx % count($colColors)];
                        $bgAlpha = 'rgba(' . implode(',', sscanf(ltrim($accent,'#'), '%02x%02x%02x')) . ',0.07)';
                        $initial = strtoupper(substr($column['user']->name, 0, 1));
                        $uid     = $column['user']->id;
                    @endphp

                    <div class="tk-col" data-user="{{ $uid }}">
                        {{-- Column Header --}}
                        <div class="tk-col-header" style="border-bottom-color: {{ $accent }};">
                            <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                                <div class="tk-col-avatar" style="background:{{ $bgAlpha }}; color:{{ $accent }};">
                                    {{ $initial }}
                                </div>
                                <div class="min-w-0">
                                    <div class="tk-col-name">{{ $column['user']->name }}</div>
                                    <div class="tk-col-count">
                                        <span class="tk-count-badge" id="count-{{ $uid }}">{{ count($column['tasks']) }}</span> tasks
                                    </div>
                                </div>
                            </div>
                            @if($canAddTask)
                                <button type="button" class="tk-add-btn" onclick="openAddTaskOffcanvas({{ $uid }})"
                                        data-uid="{{ $uid }}" title="Add Task">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endif
                        </div>

                        {{-- Cards --}}
                        <div class="tk-cards eventblock connectedSortable" data-user="{{ $uid }}">
                            @forelse ($column['tasks'] as $task)
                                @php
                                    $statusColors = [
                                        '1' => ['#ea4335', 'Urgent',      'bg-danger'],
                                        '2' => ['#f29900', 'Pending',     'bg-warning'],
                                        '3' => ['#1a73e8', 'In Progress', 'bg-primary'],
                                        '4' => ['#163f7a', 'Done',        'bg-success'],
                                        '5' => ['#163f7a', 'Closed',      'bg-secondary'],
                                    ];
                                    $sc = $statusColors[$task->status] ?? ['#9aa0a6', 'Open', 'bg-light'];
                                    $displayTitle = strlen($task->title) > 55
                                        ? substr($task->title, 0, 55) . '…'
                                        : $task->title;
                                    $displayDesc  = (!empty($task->msg) && $task->msg !== $task->title)
                                        ? (strlen($task->msg) > 60 ? substr($task->msg, 0, 60) . '…' : $task->msg)
                                        : '';
                                    $whr = floatval($task->whr ?? 0);
                                    $taskAssignees = $task->assignees ?? collect();
                                @endphp

                                <a href="javascript:void(0)" onclick="openTaskAjax(event, '{{ $task->id }}')"
                                   class="tk-card {{ $task->is_highlighted ? 'tk-card-highlighted' : '' }}"
                                   draggable="true" data-taskid="{{ $task->id }}"
                                   style="border-left-color: {{ $sc[0] }};">

                                    {{-- Top row: status pill + timer icon --}}
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="tk-status-pill" style="background:{{ $sc[0] }}18; color:{{ $sc[0] }};">
                                            {{ $sc[1] }}
                                        </span>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="tk-card-action">
                                                @if($task->status == '0')
                                                    <i class="bx bx-time" title="Start Timer"></i>
                                                @else
                                                    <i class="bx bx-stopwatch" title="Running"></i>
                                                @endif
                                            </div>
                                            <i class="bx bx-dots-vertical-rounded tk-drag-handle" title="Drag"></i>
                                        </div>
                                    </div>

                                    {{-- Title --}}
                                    <div class="tk-card-title">{{ $displayTitle }}</div>

                                    {{-- Description preview --}}
                                    @if($displayDesc)
                                        <div class="tk-card-desc">{{ $displayDesc }}</div>
                                    @endif

                                    {{-- Project & Subtask Info --}}
                                    @if($task->project || $task->parent)
                                        <div class="tk-card-relation mt-2 d-flex flex-wrap gap-1">
                                            @if($task->project)
                                                <span class="pv-badge pv-badge-info" style="font-size: 0.65rem; padding: 1px 6px;">
                                                    <i class="bx bx-briefcase-alt-2"></i> {{ $task->project->name }}
                                                </span>
                                            @endif
                                            @if($task->parent)
                                                <span class="pv-badge pv-badge-warn" style="font-size: 0.65rem; padding: 1px 6px;">
                                                    <i class="bx bx-subdirectory-right"></i> Subtask
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Multi-assignee avatar chips on card --}}
                                    @if($taskAssignees->count() > 1)
                                        <div class="tk-assignees-row mt-2">
                                            @foreach($taskAssignees->take(4) as $assignee)
                                                <div class="tk-assignee-chip" title="{{ $assignee->name }}">
                                                    {{ strtoupper(substr($assignee->name, 0, 1)) }}
                                                </div>
                                            @endforeach
                                            @if($taskAssignees->count() > 4)
                                                <div class="tk-assignee-chip tk-assignee-more">+{{ $taskAssignees->count() - 4 }}</div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Footer: hours worked & attachments --}}
                                    @if($whr > 0 || !empty($task->label) || ($task->attachment_count ?? 0) > 0)
                                        <div class="tk-card-footer d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-light">
                                            <div class="d-flex align-items-center gap-2">
                                                @if($whr > 0)
                                                    <span class="tk-card-hours text-muted" title="Hours worked" style="font-size: 0.75rem;">
                                                        <i class="bx bx-time-five"></i> {{ $whr }}h
                                                    </span>
                                                @endif
                                                @if(($task->attachment_count ?? 0) > 0)
                                                    <span class="text-muted d-flex align-items-center gap-1" title="{{ $task->attachment_count }} Attachments" style="font-size: 0.75rem;">
                                                        <i class="bx bx-paperclip"></i> {{ $task->attachment_count }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if(!empty($task->label))
                                                <span class="tk-card-label-dot shadow-sm" style="background:{{ $task->label }};" title="Label"></span>
                                            @endif
                                        </div>
                                    @endif
                                </a>

                            @empty
                                <div class="tk-empty-col">
                                    <i class="bx bx-clipboard"></i>
                                    <span>No tasks yet</span>
                                </div>
                            @endforelse
                        </div>

                        {{-- Quick Add form has been moved to popup --}}
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    {{-- Task popup is loaded exclusively via AJAX (openTaskAjax) to avoid duplicate rendering --}}

    <div id="taskAjaxContainer"></div>

    {{-- Create Task Modal (Contract Style UX) --}}
    @if($canAddTask)
    <style>
        .cf-wrap * { box-sizing: border-box; font-family: inherit; }
        .cf-section-title { font-size: .72rem; font-weight: 700; color: #163f7a; text-transform: uppercase; letter-spacing: .07em; margin: 18px 0 12px; padding-bottom: 4px; border-bottom: 1.5px solid rgba(22, 63, 122,.12); }
        .cf-section-title:first-child { margin-top: 0; }
        .cf-field { display: flex; flex-direction: column; }
        .cf-field label { font-size: .78rem; color: #5f6368; font-weight: 400; margin-bottom: 5px; text-transform: none; }
        .cf-field label .req { color: #ea4335; }
        .cf-input-box { display: flex; align-items: center; border: 1.5px solid #d1d5db; border-radius: 8px; background: #fff; overflow: hidden; transition: border-color .15s, box-shadow .15s; height: 42px; }
        .cf-input-box:focus-within { border-color: #163f7a; box-shadow: 0 0 0 3px rgba(22, 63, 122,.08); }
        .cf-input-box .cf-icon { display: flex; align-items: center; justify-content: center; width: 38px; height: 100%; flex-shrink: 0; color: #163f7a; font-size: 1.05rem; border-right: 1.5px solid #e8eaed; background: #f8fdfd; }
        .cf-input-box input, .cf-input-box select, .cf-input-box textarea { flex: 1; border: none !important; outline: none !important; box-shadow: none !important; background: transparent; font-size: .875rem; color: #202124; padding: 0 10px; height: 100%; appearance: none; -webkit-appearance: none; }
        .cf-input-box select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%235f6368'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px; }
        .cf-input-box.cf-textarea-box { height: auto; align-items: flex-start; }
        .cf-input-box.cf-textarea-box textarea { height: auto; padding: 10px; resize: none; width: 100%; }
        
        /* Select2 wrapper */
        .cf-select2-wrap { position: relative; border: 1.5px solid #d1d5db; border-radius: 8px; overflow: hidden; background: #fff; display: flex; align-items: center; height: 42px; transition: border-color .15s, box-shadow .15s; }
        .cf-select2-wrap:focus-within { border-color: #163f7a; box-shadow: 0 0 0 3px rgba(22, 63, 122,.08); }
        .cf-select2-wrap .cf-icon-abs { display: flex; align-items: center; justify-content: center; width: 38px; height: 100%; flex-shrink: 0; color: #163f7a; font-size: 1.05rem; border-right: 1.5px solid #e8eaed; background: #f8fdfd; pointer-events: none; z-index: 2; }
        .cf-select2-wrap .select2-container { flex: 1; min-width: 0; }
        .cf-select2-wrap .select2-container--default .select2-selection--single { height: 42px; border: none !important; border-radius: 0; padding-left: 10px; display: flex; align-items: center; background: transparent; box-shadow: none !important; }
        .cf-select2-wrap .select2-container--default.select2-container--focus .select2-selection--single, .cf-select2-wrap .select2-container--default.select2-container--open .select2-selection--single { border: none !important; box-shadow: none !important; }
        .cf-select2-wrap .select2-selection--single .select2-selection__rendered { line-height: normal; font-size: .875rem; color: #202124; padding: 0; }
        .cf-select2-wrap .select2-selection--single .select2-selection__placeholder { color: #9aa0a6; }
        .cf-select2-wrap .select2-selection--single .select2-selection__arrow { height: 40px; right: 6px; }
        .select2-dropdown { border: 1.5px solid #d1d5db; border-radius: 8px; box-shadow: 0 6px 24px rgba(0,0,0,.1); z-index: 99999 !important; overflow: hidden; }
        .select2-search--dropdown .select2-search__field { border: 1px solid #e0e0e0; border-radius: 6px; font-size: .85rem; padding: 6px 10px; }
        .select2-results__option { font-size: .85rem; padding: 8px 12px; }
        .select2-results__option--highlighted { background: #163f7a !important; color: #fff !important; }
        .cf-select2-wrap select { border: 0 !important; outline: none !important; box-shadow: none !important; }
        
        /* Filter override for Select2 */
        .tk-project-filter-box .select2-container--default .select2-selection--single { height: 38px; border: 1.5px solid #d1d5db !important; border-radius: 10px !important; display: flex; align-items: center; padding-left: 12px; }
        .tk-project-filter-box .select2-container--default.select2-container--open .select2-selection--single,
        .tk-project-filter-box .select2-container--default.select2-container--focus .select2-selection--single { border-color: #163f7a !important; box-shadow: 0 0 0 3px rgba(22, 63, 122,.08) !important; }
        
        .cf-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: linear-gradient(135deg, #005757, #163f7a); border-radius: 16px 16px 0 0; }
        .cf-modal-header-title { font-size: .975rem; font-weight: 700; color: #fff; margin: 0; }
        .cf-modal-header-sub { font-size: .73rem; color: rgba(255,255,255,.72); margin: 0; }
        .cf-modal-header .btn-close { filter: invert(1); opacity:.8; }
        .cf-modal-footer { padding: 12px 20px; border-top: 1px solid #e8eaed; display: flex; justify-content: flex-end; gap: 8px; background: #fff; border-radius: 0 0 16px 16px; }
        .cf-btn-cancel { font-size: .85rem; padding: 8px 20px; border-radius: 8px; border: 1.5px solid #d1d5db; background: #fff; color: #5f6368; cursor: pointer; transition: background .15s; }
        .cf-btn-cancel:hover { background: #f5f5f5; }
        .cf-btn-save { font-size: .85rem; font-weight: 600; padding: 8px 22px; border-radius: 8px; border: none; background: #163f7a; color: #fff; cursor: pointer; transition: background .15s; display: flex; align-items: center; gap: 5px; }
        .cf-btn-save:hover { background: #004e4e; }
        .cf-assignee-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 8px; max-height: 180px; overflow-y: auto; }
        .cf-assignee-label { display: flex; align-items: center; gap: 6px; padding: 6px 10px; border: 1.5px solid #e8eaed; border-radius: 6px; cursor: pointer; font-size: 0.8rem; background: #fff; transition: all 0.2s; }
        .cf-assignee-label:hover { border-color: #163f7a; background: #f8fdfd; }
        .cf-assignee-label input[type="checkbox"] { accent-color: #163f7a; width: 14px; height: 14px; }
    </style>

    <div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border:none;">
                
                {{-- HEADER --}}
                <div class="cf-modal-header">
                    <div>
                        <p class="cf-modal-header-title">
                            <i class="bx bx-task me-1"></i> New Task
                        </p>
                        <p class="cf-modal-header-sub">Fill in details to create a new task</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                {{-- BODY --}}
                <div class="modal-body px-4 py-3 cf-wrap" style="max-height:68vh; overflow-y:auto; background:#f4fbfb;">
                    <form action="{{ route('task') }}" method="post" id="createTaskForm">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ request('parent_id') }}" />

                        {{-- Section: Task Information --}}
                        <div class="cf-section-title">Task Information</div>
                        <div class="row g-3">
                            <div class="col-12 cf-field">
                                <label>Task Title <span class="req">*</span></label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-text"></i></span>
                                    <input type="text" name="title" placeholder="Enter task title" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 cf-field">
                                <label>Project</label>
                                <div class="cf-select2-wrap">
                                    <span class="cf-icon-abs"><i class="bx bx-briefcase-alt-2"></i></span>
                                    <select name="project_id" id="createProjectSelect">
                                        <option value="">— No Project —</option>
                                        @foreach($projects as $proj)
                                            <option value="{{ $proj->id }}" {{ $activeProjectId == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6 cf-field">
                                <label>Label Color</label>
                                <div class="cf-input-box" style="padding-left: 14px;">
                                    <span class="et-label-dot shadow-sm" id="createLabelIcon" style="background:#787878; width: 14px; height: 14px; border-radius: 50%; margin-right: 8px;"></span>
                                    <select name="label" id="createColorPalet" style="background-image:none; padding-right:10px;">
                                        <option value="#787878">New Task</option>
                                        <option value="#007265">In Working</option>
                                        <option value="#ff9800">Pause</option>
                                        <option value="#e91e1e">Urgent</option>
                                        <option value="#0dd500">Complete</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 cf-field">
                                <label>Description</label>
                                <div class="cf-input-box cf-textarea-box">
                                    <span class="cf-icon" style="padding-top: 12px; height: 110px; align-items: flex-start;"><i class="bx bx-detail"></i></span>
                                    <textarea name="des" rows="4" placeholder="Add a more detailed description..."></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Assignees --}}
                        <div class="cf-section-title mt-4">Assignees</div>
                        <div class="row g-3">
                            <div class="col-md-4 cf-field">
                                <label>Primary Assignee <span class="req">*</span></label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bxs-user"></i></span>
                                    <select name="uid" id="createTaskUid" required>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-8 cf-field">
                                <label>Also Assign To</label>
                                <div class="cf-assignee-grid">
                                    @foreach($users as $u)
                                        <label class="cf-assignee-label">
                                            <input type="checkbox" name="assignee_ids[]" value="{{ $u->id }}" />
                                            <span>{{ $u->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                {{-- FOOTER --}}
                <div class="cf-modal-footer">
                    <button type="button" class="cf-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="createTaskForm" class="cf-btn-save">
                        <i class="bx bx-check"></i> Create Task
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    <script>
        // Open Create Task Modal
        window.openAddTaskOffcanvas = function(uid) {
            document.getElementById('createTaskUid').value = uid;
            var modalEl = document.getElementById('createTaskModal');
            var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        };

        const createColorPalet = document.getElementById('createColorPalet');
        if (createColorPalet) {
            createColorPalet.addEventListener('change', function() {
                document.getElementById('createLabelIcon').style.background = this.value;
            });
        }

        // Open task modal via AJAX
        function openTaskAjax(event, taskId, isRefresh = false) {
            if(event) event.preventDefault();

            const container = document.getElementById('taskAjaxContainer');
            
            // Only show loader if we aren't already refreshing an open modal
            if (!isRefresh) {
                container.innerHTML = `
                    <div class="modal-backdrop fade show" style="z-index: 1050;"></div>
                    <div class="modal fade show d-block" tabindex="-1" style="z-index: 1060;">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content" style="border-radius:16px; border:none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                                <div class="p-5 text-center">
                                    <i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:#163f7a;"></i>
                                    <p class="mt-2 text-muted">Loading Task Details...</p>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }

            fetch('{{ url("/task-details") }}/' + taskId)
                .then(response => {
                    if (!response.ok) throw new Error('Status: ' + response.status);
                    return response.text();
                })
                .then(html => {
                    if (window.jQuery) {
                        $(container).html(html);
                    } else {
                        container.innerHTML = html;
                        Array.from(container.querySelectorAll('script')).forEach(oldScript => {
                            const newScript = document.createElement('script');
                            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching task details:', error);
                    if (!isRefresh) container.innerHTML = '';
                    alert('Could not load task details (Task ID: ' + taskId + ').');
                });
        }

        // Helper to refresh current open task without flicker
        function refreshTaskDetails(taskId) {
            if (!taskId) {
                const idEl = document.getElementById('taskid');
                if (idEl) taskId = idEl.value;
            }
            if (taskId) openTaskAjax(null, taskId, true);
        }

        // Close task modal
        function closeTaskAjax() {
            document.getElementById('taskAjaxContainer').innerHTML = '';
            if(window.location.search.includes('id=')) {
                window.location = '{{ route("task") }}';
            }
        }

        // Handle URL parameters for auto-actions
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);

            const taskId = urlParams.get('id');
            if (taskId && !document.querySelector('.offcanvas.show')) {
                openTaskAjax(null, taskId);
            }

            if (urlParams.get('action') === 'add') {
                if (typeof openAddTaskOffcanvas === 'function') {
                    openAddTaskOffcanvas('{{ Auth::id() }}');
                }
            }

            // Initialize Select2 dropdowns if available
            if (typeof $.fn.select2 !== 'undefined') {
                $('#projectFilterSelect').select2({
                    placeholder: "Search Project...",
                    minimumResultsForSearch: 1,
                    width: '180px'
                });

                $('#createProjectSelect').select2({
                    placeholder: "Search or select a project...",
                    allowClear: true,
                    dropdownParent: $('#createTaskModal'),
                    width: '100%'
                });
            }
        });
    </script>

@endsection

@push('scripts')
    {{-- Select2 JS --}}
    @once
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endonce
@endpush
