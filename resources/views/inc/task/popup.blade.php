@php
    $roles = session('roles');
    $roleArray = explode(',', ($roles->permissions ?? ''));
    $task = $taskSingle[0];
    $labels = [
        '#787878' => 'New Task',
        '#007265' => 'In Working',
        '#ff9800' => 'Pause',
        '#e91e1e' => 'Urgent',
        '#0dd500' => 'Complete',
    ];
    // Working hours calculation
    $isRunning = !empty($taskHistory[0]->id) && $taskHistory[0]->status == '0';
    $workingMin = $isRunning
        ? (strtotime(date('d-m-Y h:i:s a')) - strtotime($taskHistory[0]->start_time)) / 60
        : 0;
    // Total duration — fixed: end_time - start_time (not reversed)
    $total_min = 0;
    foreach ($taskHistory as $t) {
        $start = strtotime($t->start_time ?? '');
        $end   = strtotime($t->end_time ?? '');
        if ($end > $start) {
            $total_min += intval(($end - $start) / 60);
        }
    }
    $th = intval($total_min / 60);
    $tm = $total_min % 60;

    // Current assignee IDs for the multi-select
    $currentAssigneeIds = $task->assignees->pluck('id')->toArray();
    $allUsers   = $allUsers   ?? collect();
    $projects   = $projects   ?? collect();
@endphp

{{-- Backdrop overlay --}}
<div class="modal-backdrop fade show" style="z-index: 1050;" onclick="closeTaskAjax();"></div>

<div class="modal fade show" tabindex="-1" id="taskModal" style="display: block; z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content cf-wrap" style="border-radius:16px; border:none; overflow:hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            
            {{-- ── HEADER ── --}}
            <div class="cf-modal-header" style="align-items: flex-start;">
                <div class="flex-grow-1 pe-3">
                    <p class="cf-modal-header-title d-flex align-items-center">
                        <i class="bx bx-task me-2 fs-5"></i> Edit Task #{{ $task->id }}
                    </p>
                    <div class="mt-2 text-white">
                        <textarea id="tasktitle" class="cf-task-title-input" style="width:100%; background:transparent; border:none; outline:none; color:#fff; font-size:1.15rem; font-weight:600; resize:none;" rows="1" placeholder="Task title…">{{ ucfirst($task->title) }}</textarea>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
                    {{-- Timer start/stop --}}
                    @if($isRunning)
                        <a href="javascript:void(0)" class="btn btn-sm btn-light text-danger et-timer-running taskstart"
                            data-taskid="{{ $task->id }}" data-taskhr="{{ round($workingMin, 2) }}" id="{{ $taskHistory[0]->id }}" title="Stop Timer" style="font-weight:600; border-radius:8px;">
                            <i class="bx bx-stop-circle"></i> <span>Stop &bull; {{ floor($workingMin / 60) }}h {{ floor($workingMin % 60) }}m</span>
                        </a>
                    @else
                        <a href="javascript:void(0)" class="btn btn-sm btn-light text-success taskstart" 
                            data-taskid="{{ $task->id }}" id="{{ $task->id }}" title="Start Timer" style="font-weight:600; border-radius:8px;">
                            <i class="bx bx-play-circle"></i> Start Timer
                        </a>
                    @endif
                    
                    {{-- Delete --}}
                    @if(in_array('tasks_delete', $roleArray) || in_array('All', $roleArray))
                        <button type="button" class="btn btn-sm taskdeleted" id="{{ $task->id }}" style="background:rgba(255,255,255,0.15); color:#fff; border-radius:8px;" title="Delete Task">
                            <i class="bx bx-trash"></i>
                        </button>
                    @endif
                    
                    {{-- Close --}}
                    <button type="button" onclick="closeTaskAjax()" class="btn-close btn-close-white ms-1" style="opacity:1;" title="Close"></button>
                </div>
            </div>

            {{-- ── BODY ── --}}
            <div class="modal-body p-4" style="background:#f4fbfb; max-height: 75vh; overflow-y:auto;">
                <div class="row g-4">
                    {{-- MAIN CONTENT (Tabs) --}}
                    <div class="col-lg-8 ps-lg-4 border-start order-2">
                        <style>
                            .cf-nav-tabs { border-bottom: 1px solid #d1d5db; margin-bottom: 20px; flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; }
                            .cf-nav-tabs::-webkit-scrollbar { height: 4px; }
                            .cf-nav-tabs::-webkit-scrollbar-thumb { background: #c1c5cb; border-radius: 4px; }
                            .cf-nav-tabs .nav-item { margin-bottom: -1px; }
                            .cf-nav-tabs .nav-link { color: #6c757d; font-weight: 600; border: none; border-bottom: 3px solid transparent; background: transparent !important; padding: 10px 16px; transition: 0.2s; white-space: nowrap; }
                            .cf-nav-tabs .nav-link.active { color: #006666; border-bottom: 3px solid #006666; }
                            .cf-nav-tabs .nav-link:hover:not(.active) { border-bottom: 3px solid #d1d5db; color: #495057; }
                        </style>

                        <ul class="nav nav-tabs cf-nav-tabs" id="taskTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">
                                    <i class="bx bx-align-left"></i> Description
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="attach-tab" data-bs-toggle="tab" data-bs-target="#attach" type="button" role="tab">
                                    <i class="bx bx-paperclip"></i> Attachments (<span id="attachmentCountTab">{{ count($taskAttachments ?? []) }}</span>)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="comment-tab" data-bs-toggle="tab" data-bs-target="#comment" type="button" role="tab">
                                    <i class="bx bx-comment-dots"></i> Comments
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="taskTabsContent">
                            {{-- Tab 1: Description --}}
                            <div class="tab-pane fade show active" id="desc" role="tabpanel">
                                
                        <div class="cf-field">
                            <form id="edttaskdetails" method="post">
                                @csrf
                                <input type="hidden" name="taskid" id="taskid" value="{{ $task->id }}" />
                                <div class="cf-input-box cf-textarea-box" style="background:#fff; height:180px;">
                                    <textarea name="taskdes" class="et-textarea w-100 h-100" id="example" placeholder="Add a more detailed description…" required style="border:none; outline:none; resize:none;">{{ ucfirst($task->des) }}</textarea>
                                </div>
                                @if(in_array('tasks_edit', $roleArray) || in_array('All', $roleArray))
                                    <div class="d-flex align-items-center gap-2 mt-3">
                                        <button type="submit" class="cf-btn-save"><i class="bx bx-save"></i> Save Changes</button>
                                        <button type="reset" class="cf-btn-cancel">Reset</button>
                                        <span id="res" class="small ms-2 text-success fw-bold"></span>
                                    </div>
                                @endif
                            </form>
                        </div>
                        
                        
                            </div>
                            
                            {{-- Tab 2: Attachments --}}
                            <div class="tab-pane fade" id="attach" role="tabpanel">
                                <div class="pt-1">
                                    
                        <div class="bg-white border rounded p-3" style="border-color:#d1d5db;">
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-sm" onclick="document.getElementById('taskAttachmentInput').click()" style="background:rgba(0,102,102,0.1); color:#006666; font-weight:600;">
                                    <i class="bx bx-upload"></i> Upload
                                </button>
                                <input type="file" id="taskAttachmentInput" style="display:none;" onchange="uploadTaskAttachment(this)" />
                            </div>
                            <div id="attachmentsWrap">
                                @forelse($taskAttachments ?? [] as $attachment)
                                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-light" id="attachment-{{ $attachment->id }}">
                                        <a href="{{ asset($attachment->file_path) }}" target="_blank" class="d-flex align-items-center gap-2 text-decoration-none text-truncate" style="max-width: 80%;">
                                            <i class="bx bxs-file-pdf text-danger fs-4"></i>
                                            <span class="text-dark small fw-medium text-truncate">{{ $attachment->file_name }}</span>
                                        </a>
                                        @if(in_array('tasks_edit', $roleArray) || in_array('All', $roleArray))
                                            <button type="button" class="btn btn-sm text-danger border-0 bg-transparent" onclick="deleteAttachment({{ $attachment->id }})">
                                                <i class="bx bx-trash" style="font-size: 1.1rem;"></i>
                                            </button>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-muted small text-center p-3" id="noAttachmentsMsg">No files attached yet.</div>
                                @endforelse
                            </div>
                            <div id="attachmentLoader" class="text-center p-3" style="display:none;">
                                <i class="bx bx-loader-alt bx-spin text-primary fs-4"></i>
                                <p class="small text-muted mb-0">Uploading...</p>
                            </div>
                        </div>
                        
                        
                                </div>
                            </div>
                            
                            {{-- Tab 3: Comments --}}
                            <div class="tab-pane fade" id="comment" role="tabpanel">
                                
                        <div class="mb-4">
                            <form method="post" id="taskComments">
                                @csrf
                                <input type="hidden" name="commenttaskid" value="{{ $task->id }}" />
                                <div class="d-flex gap-2">
                                    <div class="et-auth-avatar" style="width:32px; height:32px; border-radius:50%; background:#006666; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:0.8rem; font-weight:bold;">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="cf-input-box cf-textarea-box bg-white" style="padding:0; min-height:60px;">
                                            <textarea name="taskcomment" rows="2" id="commentInputs" placeholder="Write a comment… (Ctrl+Enter to post)" required style="width:100%; border:none; resize:none; padding:10px; border-radius:6px; outline:none;"></textarea>
                                        </div>
                                        <div class="mt-2 d-flex align-items-center gap-2">
                                            <button type="submit" class="btn btn-sm text-white" style="font-size:0.8rem; font-weight:600; padding:4px 16px; background:#006666; border-radius:6px;"><i class="bx bx-send"></i> Post</button>
                                            <span id="res1" class="small text-success fw-bold"></span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                            <div id="reloadMsg" class="mt-4">
                                @if(count($taskComments) > 0)
                                    <div class="d-flex flex-column gap-3">
                                    @foreach($taskComments as $c)
                                        @php $isMine = $c->uid == Auth::user()->id; @endphp
                                        <div class="d-flex gap-3 {{ $isMine ? 'flex-row-reverse' : '' }}">
                                            <div style="width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:0.75rem; font-weight:700; {{ $isMine ? 'background:rgba(0,102,102,0.12);color:#006666;' : 'background:rgba(26,115,232,0.10);color:#1a73e8;' }}">
                                                {{ strtoupper(substr($c->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="p-2 px-3 rounded shadow-sm" style="{{ $isMine ? 'background:#006666; color:#fff;' : 'background:#fff; border:1px solid #e8eaed;' }} max-width:85%;">
                                                <div class="small fw-bold mb-1" style="{{ $isMine ? 'color:rgba(255,255,255,0.9);' : 'color:#202124;' }}">{{ $c->name ?? 'Unknown' }}</div>
                                                <div class="small" style="line-height:1.4;">{{ $c->comments }}</div>
                                                <div style="font-size:0.65rem; margin-top:6px; {{ $isMine ? 'color:rgba(255,255,255,0.7);' : 'color:#9aa0a6;' }} text-align:{{ $isMine?'right':'left' }};">
                                                    {{ \Carbon\Carbon::parse($c->created_at)->format('d M Y, H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <div class="text-center p-4 text-muted small border rounded bg-light" style="border-style:dashed!important;">
                                        <i class="bx bx-comment fs-3 mb-2 opacity-50"></i><br>
                                        No comments yet. Be the first!
                                    </div>
                                @endif
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- SIDEBAR DETAILS (Properties) --}}
                    <div class="col-lg-4 order-1">
                        <div class="cf-section-title"><i class="bx bx-cog"></i> Properties</div>
                        <div class="bg-white border rounded p-3 mb-4" style="border-color:#d1d5db;">
                            
                            <div class="cf-field mb-3">
                                {{-- Status --}}
                                <label class="d-flex align-items-center gap-1"><i class="bx bx-radio-circle-marked"></i> Task Status</label>
                                @php
                                    $statusMap = ['0'=>['#80868b','Open'], '1'=>['#ea4335','Urgent'], '2'=>['#f29900','Pending'], '3'=>['#1a73e8','In Progress'], '4'=>['#34a853','Done'], '5'=>['#006666','Closed']];
                                    [$sColor, $sLabel] = $statusMap[$task->status] ?? ['#80868b','Open'];
                                @endphp
                                <div id="statusWrapper" class="cf-input-box px-2" style="border-color:{{ $sColor }}; border-width:2px; height:42px;">
                                    <select id="taskStatusSelect" class="w-100" style="color:{{ $sColor }}; font-weight:700; font-size:0.9rem;">
                                        @foreach($statusMap as $val => [$col, $lbl])
                                            <option value="{{ $val }}" {{ $task->status == $val ? 'selected' : '' }} style="color:{{ $col }};">{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Project --}}
                            <div class="cf-field mb-3">
                                <label class="d-flex align-items-center gap-1"><i class="bx bx-briefcase-alt-2"></i> Project</label>
                                <div class="cf-select2-wrap shadow-none" style="height:38px;">
                                    <select id="taskProjectSelect" class="w-100" style="height:100%; border:none; padding:0 10px;">
                                        <option value="">— No Project —</option>
                                        @foreach($projects as $proj)
                                            <option value="{{ $proj->id }}" {{ $task->project_id == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Assignees --}}
                            <div class="cf-field mb-3 border-top pt-3">
                                <label class="d-flex align-items-center gap-1"><i class="bx bx-group"></i> Assigned To</label>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @forelse($task->assignees as $assignee)
                                        <div class="badge bg-light text-dark border p-1 px-2 d-flex align-items-center gap-1" title="{{ $assignee->name }}">
                                            <div style="width:18px; height:18px; border-radius:50%; background:#006666; color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.5rem;">{{ strtoupper(substr($assignee->name, 0, 1)) }}</div>
                                            {{ explode(' ', $assignee->name)[0] }}
                                        </div>
                                    @empty
                                        @php $primary = $userSingle[0] ?? null; @endphp
                                        @if($primary)
                                            <div class="badge bg-light text-dark border p-1 px-2 d-flex align-items-center gap-1" title="{{ $primary->name }}">
                                                <div style="width:18px; height:18px; border-radius:50%; background:#006666; color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.5rem;">{{ strtoupper(substr($primary->name, 0, 1)) }}</div>
                                                {{ explode(' ', $primary->name)[0] }}
                                            </div>
                                        @else
                                            <span class="text-muted small">Unassigned</span>
                                        @endif
                                    @endforelse
                                </div>
                                
                                @if(in_array('tasks_edit', $roleArray) || in_array('All', $roleArray))
                                    <div class="bg-light border rounded px-1 pt-1 mt-2" style="max-height:160px; overflow-y:auto;">
                                        @foreach($allUsers as $u)
                                            <label class="d-flex align-items-center gap-2 mb-1 p-1 px-2 rounded" style="cursor:pointer; font-size:0.75rem; font-weight:500; transition:0.2s;">
                                                <input type="checkbox" class="et-assignee-chk" name="assignee_ids[]" value="{{ $u->id }}" {{ in_array($u->id, $currentAssigneeIds) ? 'checked' : '' }} style="accent-color:#006666; width:14px; height:14px;" />
                                                {{ $u->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm mt-2 w-100" id="saveAssigneesBtn" data-taskid="{{ $task->id }}" style="background:rgba(0,102,102,0.1); color:#006666; font-weight:600;">
                                        <i class="bx bx-save"></i> Update Assignees
                                    </button>
                                @endif
                            </div>

                        </div>
                        
                        {{-- Time Log --}}
                        @if(count($taskHistory) > 0)
                            <div class="cf-section-title"><i class="bx bx-time-five"></i> Time Log</div>
                            <div class="bg-white border rounded p-3" style="border-color:#d1d5db;">
                                <div class="bg-light border rounded px-2 pt-2 mb-2" style="max-height:120px; overflow-y:auto;">
                                    @foreach($taskHistory as $t)
                                        @php
                                            $s  = strtotime($t->start_time ?? '');
                                            $e  = strtotime($t->end_time ?? '');
                                            $dm = $e > $s ? intval(($e - $s) / 60) : 0;
                                            $dh = intval($dm / 60);
                                            $dmin = $dm % 60;
                                        @endphp
                                        <div class="d-flex justify-content-between small text-muted mb-2 border-bottom pb-1">
                                            <span>{{ date_format(date_create($t->created_at), 'd M Y') }}</span>
                                            <strong class="text-dark">{{ $dh }}h {{ $dmin }}m</strong>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="d-flex justify-content-between align-items-center px-1">
                                    <span class="small text-muted fw-bold"><i class="bx bx-calculator"></i> Total Logged Time</span>
                                    <strong style="color:#006666; font-size:1.1rem;">{{ $th }}h {{ $tm }}m</strong>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
(function () {
    /* 1. Auto-resize title textarea */
    const titleTA = document.getElementById('tasktitle');
    function resizeTitle() {
        titleTA.style.height = 'auto';
        titleTA.style.height = titleTA.scrollHeight + 'px';
    }
    if (titleTA) { resizeTitle(); titleTA.addEventListener('input', resizeTitle); }

    /* 2. Live label dot update */
    const colorSel = document.getElementById('colorpalet');
    const labelDot = document.getElementById('labelicon');
    if (colorSel && labelDot) {
        colorSel.addEventListener('change', function () {
            labelDot.style.background = this.value || '#787878';
            labelDot.style.color      = this.value || '#787878';
        });
    }

    /* 3. Ctrl+Enter to submit comment */
    const commentTA = document.getElementById('commentInputs');
    if (commentTA) {
        commentTA.addEventListener('keydown', function (e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('taskComments').dispatchEvent(new Event('submit', { bubbles: true }));
            }
        });
    }

    /* 4. Live running timer counter in Stop button */
    const stopBtn = document.querySelector('.et-timer-running');
    if (stopBtn) {
        let startMs  = Date.now();
        const baseMin = parseFloat(stopBtn.dataset.taskhr || 0) * 60000;
        const span    = stopBtn.querySelector('span');
        if (span) {
            setInterval(function () {
                const totalMs = baseMin + (Date.now() - startMs);
                const h = Math.floor(totalMs / 3600000);
                const m = Math.floor((totalMs % 3600000) / 60000);
                const s = Math.floor((totalMs % 60000) / 1000);
                span.textContent = 'Stop \u2022 ' + (h ? h + 'h ' : '') + m + 'm ' + s + 's';
            }, 1000);
        }
    }

    /* 5. Status change AJAX */
    const statusSel = document.getElementById('taskStatusSelect');
    if (statusSel) {
        statusSel.addEventListener('change', function () {
            const taskId = document.getElementById('taskid').value;
            const statusMap = {
                '0': { color: '#80868b', label: 'Open' },
                '1': { color: '#ea4335', label: 'Urgent' },
                '2': { color: '#f29900', label: 'Pending' },
                '3': { color: '#1a73e8', label: 'In Progress' },
                '4': { color: '#34a853', label: 'Done' },
                '5': { color: '#006666', label: 'Closed' }
            };
            const sc = statusMap[this.value] || statusMap['0'];

            // 1. Update Modal UI instantly
            const wrapper = document.getElementById('statusWrapper');
            if(wrapper) wrapper.style.borderColor = sc.color;
            this.style.color = sc.color;

            // 2. Update Kanban Board Card instantly
            const card = document.querySelector(`.tk-card[data-taskid="${taskId}"]`);
            if (card) {
                card.style.borderLeftColor = sc.color;
                
                // Update Status Pill
                const pill = card.querySelector('.tk-status-pill');
                if (pill) {
                    pill.textContent = sc.label;
                    pill.style.background = sc.color + '18'; // 9% opacity
                    pill.style.color = sc.color;
                }

                // Update Label Dot
                const labelDot = card.querySelector('.tk-card-label-dot');
                if (labelDot) {
                    labelDot.style.background = sc.color;
                }

                // Update Timer Icon in Task Card
                const actionDiv = card.querySelector('.tk-card-action');
                if (actionDiv) {
                    const icon = actionDiv.querySelector('i');
                    if (icon) {
                        if (this.value === '0') {
                            icon.className = 'bx bx-time';
                            icon.title = 'Start Timer';
                        } else {
                            icon.className = 'bx bx-stopwatch';
                            icon.title = 'Running';
                        }
                    }
                }
            }

            fetch('{{ route("task.meta.update") }}', {
                method : 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ task_id: taskId, status: this.value })
            }).then(r => r.json()).then(d => {
                if (!d.success) console.warn('Status update failed', d);
            });
        });
    }

    /* 6. Project change AJAX (with Select2 support) */
    setTimeout(function() {
        if (typeof $.fn.select2 !== 'undefined' && $('#taskProjectSelect').length > 0) {
            $('#taskProjectSelect').select2({
                placeholder: "Search Project...",
                allowClear: true,
                dropdownParent: $('#taskAjaxContainer')
            }).on('change', function() {
                const taskId = document.getElementById('taskid').value;
                fetch('{{ route("task.meta.update") }}', {
                    method : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ task_id: taskId, project_id: this.value || null })
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        console.log('Project updated successfully');
                    } else {
                        console.warn('Project update failed', d);
                    }
                });
            });
        } else {
            const projSel = document.getElementById('taskProjectSelect');
            if (projSel) {
                projSel.addEventListener('change', function () {
                    const taskId = document.getElementById('taskid').value;
                    fetch('{{ route("task.meta.update") }}', {
                        method : 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ task_id: taskId, project_id: this.value || null })
                    }).then(r => r.json()).then(d => {
                        if (d.success) {
                            // Show small "saved" feedback
                            const fb = document.createElement('span');
                            fb.className = 'text-success small';
                            fb.textContent = ' ✓ Saved';
                            projSel.parentNode.appendChild(fb);
                            setTimeout(() => fb.remove(), 2000);
                        }
                    });
                });
            }
        }
    }, 50);

    /* 7. Save Assignees AJAX */
    const saveAssBtn = document.getElementById('saveAssigneesBtn');
    if (saveAssBtn) {
        saveAssBtn.addEventListener('click', function () {
            const taskId = this.dataset.taskid;
            const checked = Array.from(document.querySelectorAll('.et-assignee-chk:checked'))
                                  .map(c => parseInt(c.value));

            // Visual feedback - START
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';
            this.disabled = true;

            fetch('{{ route("task.meta.update") }}', {
                method : 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ task_id: taskId, assignee_ids: checked })
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    document.getElementById('et-assignee-row').innerHTML = d.avatarHtml;
                    this.textContent = '✓ Saved!';
                    this.classList.replace('btn-primary', 'btn-success');
                    
                    // Update Board Card Assignees (if it exists)
                    const boardCard = document.querySelector(`.tk-card[data-taskid="${taskId}"] .tk-assignees-row`);
                    if (boardCard && d.boardAvatarHtml) {
                        boardCard.innerHTML = d.boardAvatarHtml;
                    }

                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.classList.replace('btn-success', 'btn-primary');
                        this.disabled = false;
                    }, 2000);
                }
            }).catch(err => {
                console.error('Assignee update error:', err);
                this.innerHTML = originalHTML;
                this.disabled = false;
            });
        });
    }

    /* 8. Attachment upload/delete */
    window.uploadTaskAttachment = function (input) {
        if (!input.files || input.files.length === 0) return;
        let formData = new FormData();
        formData.append('file', input.files[0]);
        formData.append('task_id', '{{ $task->id }}');
        formData.append('_token', '{{ csrf_token() }}');

        document.getElementById('attachmentLoader').style.display = 'block';
        if (document.getElementById('noAttachmentsMsg')) {
            document.getElementById('noAttachmentsMsg').style.display = 'none';
        }

        fetch('{{ route("task.attachment.upload") }}', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                document.getElementById('attachmentLoader').style.display = 'none';
                if (data.status === 'success') {
                    const att  = data.attachment;
                    const html = `
                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-light" id="attachment-${att.id}">
                        <a href="/${att.file_path}" target="_blank" class="d-flex align-items-center gap-2 text-decoration-none text-truncate" style="max-width: 80%;">
                            <i class="bx bxs-file text-primary" style="font-size:1.6rem;"></i>
                            <span class="text-dark small fw-medium text-truncate">${att.file_name}</span>
                        </a>
                        <button type="button" class="btn btn-sm text-danger border-0 bg-transparent" onclick="deleteAttachment(${att.id})">
                            <i class="bx bx-trash" style="font-size: 1.1rem;"></i>
                        </button>
                    </div>`;
                    document.getElementById('attachmentsWrap').insertAdjacentHTML('beforeend', html);
                    
                    // Update counters
                    let countSpan = document.getElementById('attachmentCount');
                    let countTab  = document.getElementById('attachmentCountTab');
                    if (countSpan) countSpan.innerText = parseInt(countSpan.innerText) + 1;
                    if (countTab)  countTab.innerText  = parseInt(countTab.innerText) + 1;
                } else {
                    alert(data.message || 'Error uploading file');
                }
            })
            .catch(error => {
                document.getElementById('attachmentLoader').style.display = 'none';
                alert('Error uploading file');
                console.error(error);
            });
        input.value = '';
    };

    window.deleteAttachment = function (id) {
        if (!confirm('Delete this attachment?')) return;
        fetch(`/task-attachment/${id}`, {
            method : 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => response.json()).then(data => {
            if (data.status === 'success') {
                document.getElementById('attachment-' + id).remove();
                let countSpan = document.getElementById('attachmentCount');
                let newCount  = parseInt(countSpan.innerText) - 1;
                countSpan.innerText = newCount;
                if (newCount === 0 && document.getElementById('noAttachmentsMsg')) {
                    document.getElementById('noAttachmentsMsg').style.display = 'block';
                }
            }
        });
    };
})();
</script>
