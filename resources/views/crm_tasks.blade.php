@extends('layout')
@section('title', 'CRM Follow-Up Tasks - Rusan')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => 'CRM Follow-Up Tasks'])

        <div class="dash-container">

            {{-- Toolbar --}}
            <div class="leads-toolbar mb-4">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count">
                        <i class="bx bx-list-check"></i>
                        Follow-Up Tasks &mdash;
                        <span id="taskTotalCount">{{ $tasks->count() }}</span> scheduled
                    </span>
                </div>
                <div class="leads-toolbar-right gap-2">
                    {{-- Type filter --}}
                    <div class="crt-filter-group">
                        <button class="crt-filter active" data-filter="all">All</button>
                        <button class="crt-filter" data-filter="Call"><i class="bx bx-phone"></i> Call</button>
                        <button class="crt-filter" data-filter="Meeting"><i class="bx bx-group"></i> Meeting</button>
                        <button class="crt-filter" data-filter="Email"><i class="bx bx-envelope"></i> Email</button>
                        <button class="crt-filter" data-filter="To-Do"><i class="bx bx-task"></i> To-Do</button>
                    </div>
                    {{-- Overdue badge --}}
                    @php $overdueCount = $tasks->filter(fn($t) => \Carbon\Carbon::parse($t->due_date)->isPast() && $t->status !== 'Completed')->count(); @endphp
                    @if($overdueCount > 0)
                        <span class="crt-overdue-badge">
                            <i class="bx bx-error-circle"></i> {{ $overdueCount }} Overdue
                        </span>
                    @endif
                </div>
            </div>

            <div class="row g-4">

                {{-- ── ADD TASK CARD ── --}}
                <div class="col-lg-4">
                    <div class="ml-card crt-add-card">
                        <div class="ml-card-header">
                            <div class="ml-card-icon" style="background:rgba(0,102,102,0.10);color:#006666;">
                                <i class="bx bx-plus-circle"></i>
                            </div>
                            <div>
                                <h6 class="ml-card-title">New Follow-Up</h6>
                                <span class="ml-card-sub">Schedule a task or activity</span>
                            </div>
                        </div>
                        <div class="ml-card-body">
                            <form action="{{ route('crm_tasks.store') }}" method="POST" class="row g-3">
                                @csrf

                                <div class="col-12">
                                    <label class="ml-label">Task Name / Subject <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-pencil"></i></span>
                                        <input type="text" name="name" class="form-control"
                                               placeholder="e.g. Call regarding quotation" required>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <label class="ml-label">Type <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-category"></i></span>
                                        <select name="type" class="form-select" required>
                                            <option value="Call">📞 Call</option>
                                            <option value="Meeting">👥 Meeting</option>
                                            <option value="Email">✉️ Email</option>
                                            <option value="To-Do">✅ To-Do</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <label class="ml-label">Related To</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-link"></i></span>
                                        <select name="rel_type" class="form-select">
                                            <option value="">None</option>
                                            <option value="Lead" {{ request('rel_type') == 'Lead' ? 'selected' : '' }}>Lead</option>
                                            <option value="Customer" {{ request('rel_type') == 'Customer' ? 'selected' : '' }}>Customer</option>
                                            <option value="Opportunity" {{ request('rel_type') == 'Opportunity' ? 'selected' : '' }}>Opportunity</option>
                                            <option value="Project" {{ (request('rel_type') == 'Project' || request('project_id')) ? 'selected' : '' }}>Project</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="ml-label">Related ID</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-hash"></i></span>
                                        <input type="number" name="rel_id" class="form-control" placeholder="e.g. 42" value="{{ request('rel_id') }}">
                                    </div>
                                </div>

                                <input type="hidden" name="project_id" value="{{ request('project_id') }}">
                                <input type="hidden" name="parent_id" value="{{ request('parent_id') }}">

                                <div class="col-12">
                                    <label class="ml-label">Due Date &amp; Time <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                        <input type="datetime-local" name="due_date" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="lb-btn lb-btn-primary w-100">
                                        <i class="bx bx-check-circle"></i> Schedule Task
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ── TASK LIST ── --}}
                <div class="col-lg-8">
                    <div class="ml-card">
                        <div class="ml-card-header">
                            <div class="ml-card-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                                <i class="bx bx-list-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="ml-card-title">Scheduled Tasks</h6>
                                <span class="ml-card-sub">Upcoming follow-ups &amp; activities</span>
                            </div>
                        </div>
                        <div class="ml-card-body p-0">
                            <div id="taskListWrap">
                                @forelse($tasks as $task)
                                    @php
                                        $due       = \Carbon\Carbon::parse($task->due_date);
                                        $isOverdue = $due->isPast() && $task->status !== 'Completed';
                                        $isDone    = $task->status === 'Completed';
                                        $isDueToday= $due->isToday() && !$isDone;
                                        $typeConfig = [
                                            'Call'    => ['#1a73e8', 'bx bx-phone'],
                                            'Meeting' => ['#f29900', 'bx bx-group'],
                                            'Email'   => ['#9334e9', 'bx bx-envelope'],
                                            'To-Do'   => ['#34a853', 'bx bx-task'],
                                        ];
                                        [$tc, $ti] = $typeConfig[$task->type] ?? ['#80868b', 'bx bx-circle'];
                                    @endphp

                                    <div class="crt-task-row {{ $isDone ? 'crt-done' : '' }} {{ $isOverdue ? 'crt-overdue' : '' }} {{ $isDueToday ? 'crt-today' : '' }}"
                                         data-type="{{ $task->type }}" id="task-{{ $task->id }}">

                                        {{-- Checkbox --}}
                                        <label class="crt-check-wrap" title="{{ $isDone ? 'Mark pending' : 'Mark done' }}">
                                            <input type="checkbox" class="task-check visually-hidden"
                                                   data-id="{{ $task->id }}" {{ $isDone ? 'checked' : '' }}>
                                            <span class="crt-check-circle {{ $isDone ? 'crt-checked' : '' }}">
                                                <i class="bx bx-check"></i>
                                            </span>
                                        </label>

                                        {{-- Type icon --}}
                                        <div class="crt-type-icon" style="background:{{ $tc }}18;color:{{ $tc }};">
                                            <i class="{{ $ti }}"></i>
                                        </div>

                                        {{-- Info --}}
                                        <div class="crt-info flex-grow-1 min-w-0">
                                            <div class="crt-task-name {{ $isDone ? 'crt-name-done' : '' }}">
                                                {{ $task->name }}
                                            </div>
                                            <div class="crt-task-meta">
                                                @if($task->rel_type)
                                                    <span class="crt-rel-badge">
                                                        <i class="bx bx-link-alt"></i>
                                                        {{ $task->rel_type }} #{{ $task->rel_id }}
                                                    </span>
                                                @endif
                                                <span class="crt-due {{ $isOverdue ? 'crt-due-overdue' : ($isDueToday ? 'crt-due-today' : '') }}">
                                                    <i class="bx bx-time-five"></i>
                                                    @if($isOverdue)
                                                        Overdue &bull;
                                                    @elseif($isDueToday)
                                                        Today &bull;
                                                    @endif
                                                    {{ $due->format('d M Y, h:i A') }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Type pill --}}
                                        <span class="crt-type-pill" style="background:{{ $tc }}18;color:{{ $tc }};">
                                            {{ $task->type }}
                                        </span>

                                    </div>
                                @empty
                                    <div class="kb-empty-col" style="padding:40px 0;">
                                        <i class="bx bx-check-double" style="font-size:2.2rem;"></i>
                                        <span>No upcoming tasks. You're all caught up!</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <script>
    $(function () {
        /* ── Mark complete/pending via checkbox ── */
        $(document).on('change', '.task-check', function () {
            const taskId  = $(this).data('id');
            const checked = $(this).is(':checked');
            const $row    = $('#task-' + taskId);
            const $circle = $row.find('.crt-check-circle');
            const $name   = $row.find('.crt-task-name');

            $.post("{{ route('crm_tasks.update_status') }}", {
                _token: "{{ csrf_token() }}", id: taskId,
                status: checked ? 'Completed' : 'Pending'
            }, function () {
                if (checked) {
                    $circle.addClass('crt-checked');
                    $name.addClass('crt-name-done');
                    $row.addClass('crt-done').removeClass('crt-overdue crt-today');
                } else {
                    $circle.removeClass('crt-checked');
                    $name.removeClass('crt-name-done');
                    $row.removeClass('crt-done');
                }
            }).fail(function () { alert('Error updating task.'); });
        });

        /* ── Filter by type ── */
        $('.crt-filter').on('click', function () {
            $('.crt-filter').removeClass('active');
            $(this).addClass('active');
            const f = $(this).data('filter');
            if (f === 'all') {
                $('.crt-task-row').show();
            } else {
                $('.crt-task-row').hide().filter('[data-type="' + f + '"]').show();
            }
        });
    });
    </script>

@endsection
