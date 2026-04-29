<div class="modal fade" id="todoListModal" tabindex="-1" aria-labelledby="todoListModalLabel" aria-hidden="true"
    style="z-index:99999;">
    <div class="modal-dialog modal-dialog-centered todo-modal-dialog">
        <div class="modal-content todo-modal-content">

            <!-- Header -->
            <div class="todo-modal-header">
                <div class="todo-header-left">
                    <div class="todo-header-icon">
                        <i class="bx bx-check-double"></i>
                    </div>
                    <div>
                        <h5 class="todo-modal-title">My Todo List</h5>
                        <p class="todo-modal-subtitle" id="todoSubtitle">Loading tasks…</p>
                    </div>
                </div>
                <button type="button" class="todo-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bx bx-x"></i>
                </button>
            </div>

            <!-- Progress Bar -->
            <div class="todo-progress-wrap">
                <div class="todo-progress-bar">
                    <div class="todo-progress-fill" id="todoProgressFill" style="width:0%"></div>
                </div>
                <span class="todo-progress-label" id="todoProgressLabel">0%</span>
            </div>

            <!-- Filter Tabs -->
            <div class="todo-filters">
                <button class="todo-filter-btn active" data-filter="all">All</button>
                <button class="todo-filter-btn" data-filter="pending">Pending</button>
                <button class="todo-filter-btn" data-filter="done">Done</button>
            </div>

            <!-- Task List -->
            <div class="todo-list-wrap">
                <ul id="todoList" class="todo-list"></ul>
                <div class="todo-empty-state" id="todoEmptyState" style="display:none;">
                    <i class="bx bx-clipboard"></i>
                    <p>No tasks here. Add one below!</p>
                </div>
            </div>

            <!-- Add Task Footer -->
            <div class="todo-footer">
                <div class="todo-input-row">
                    <input type="text" id="taskInput" class="todo-input" placeholder="Add a new task and press Enter…"
                        autocomplete="off" />
                    <button id="addTask" class="todo-add-btn" title="Add Task">
                        <i class="bx bx-plus"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* ===== TODO MODAL ===== */
    .todo-modal-dialog {
        max-width: 500px;
    }

    .todo-modal-content {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18) !important;
        display: flex;
        flex-direction: column;
    }

    /* Header */
    .todo-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 22px 14px;
        background: linear-gradient(135deg, #163f7a 0%, #0f2d57 100%);
    }

    .todo-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .todo-header-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: #fff;
    }

    .todo-modal-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #fff !important;
    }

    .todo-modal-subtitle {
        margin: 0;
        font-size: 0.72rem;
        color: rgba(255, 255, 255, 0.72);
    }

    .todo-close-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.2rem;
        transition: background 0.2s;
    }

    .todo-close-btn:hover {
        background: rgba(255, 255, 255, 0.28);
    }

    /* Progress */
    .todo-progress-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 22px 0;
        background: linear-gradient(135deg, #163f7a 0%, #0f2d57 100%);
    }

    .todo-progress-bar {
        flex: 1;
        height: 5px;
        border-radius: 99px;
        background: rgba(255, 255, 255, 0.25);
        overflow: hidden;
    }

    .todo-progress-fill {
        height: 100%;
        border-radius: 99px;
        background: #fff;
        transition: width 0.4s ease;
    }

    .todo-progress-label {
        font-size: 0.70rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.85);
        min-width: 30px;
        text-align: right;
    }

    /* Filters */
    .todo-filters {
        display: flex;
        gap: 6px;
        padding: 14px 22px 10px;
        background: #f8fafb;
        border-bottom: 1px solid #eef0f2;
    }

    .todo-filter-btn {
        padding: 4px 14px;
        border-radius: 20px;
        border: 1.5px solid #e0e4e8;
        background: transparent;
        color: #6b7280;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.18s;
    }

    .todo-filter-btn.active {
        background: #163f7a;
        color: #fff;
        border-color: #163f7a;
    }

    .todo-filter-btn:hover:not(.active) {
        background: #f0f5f5;
        color: #163f7a;
        border-color: #163f7a;
    }

    /* List Wrap */
    .todo-list-wrap {
        padding: 10px 16px;
        max-height: 48vh;
        overflow-y: auto;
        background: #fff;
    }

    .todo-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    /* Task Item */
    .todo-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1.5px solid #eef0f2;
        margin-bottom: 7px;
        background: #fff;
        cursor: grab;
        transition: all 0.2s;
    }

    .todo-item:hover {
        border-color: #163f7a40;
        box-shadow: 0 2px 8px rgba(22, 63, 122, 0.08);
    }

    .todo-item.done {
        background: #f6faf8;
        opacity: 0.72;
    }

    .todo-item.dragging {
        opacity: 0.45;
        border-style: dashed;
    }

    /* Drag handle */
    .todo-drag {
        color: #c0c8d0;
        font-size: 1.1rem;
        flex-shrink: 0;
        cursor: grab;
    }

    /* Custom Checkbox */
    .todo-check {
        width: 18px;
        height: 18px;
        border-radius: 6px;
        border: 2px solid #c0c8d0;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.2s;
        position: relative;
        background: #fff;
    }

    .todo-check:checked {
        background: #163f7a;
        border-color: #163f7a;
    }

    .todo-check:checked::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 4px;
        width: 5px;
        height: 9px;
        border: 2px solid #fff;
        border-top: none;
        border-left: none;
        transform: rotate(45deg);
    }

    /* Text */
    .todo-text-wrap {
        flex: 1;
        min-width: 0;
    }

    .todo-task-text {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    .todo-item.done .todo-task-text {
        text-decoration: line-through;
        color: #9ca3af;
    }

    .todo-reminder-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 0.68rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 1px 7px;
        margin-top: 3px;
    }

    .todo-reminder-badge.info {
        background: #e0f2fe;
        color: #0277bd;
    }

    .todo-reminder-badge.danger {
        background: #fdecea;
        color: #c62828;
    }

    .todo-reminder-badge.done {
        background: #f1f5f9;
        color: #94a3b8;
    }

    /* Action buttons */
    .todo-actions {
        display: flex;
        gap: 5px;
        flex-shrink: 0;
    }

    .todo-btn {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.18s;
    }

    .todo-btn-edit {
        background: #fff8e1;
        color: #f57c00;
    }

    .todo-btn-edit:hover {
        background: #f57c00;
        color: #fff;
    }

    .todo-btn-delete {
        background: #fdecea;
        color: #c62828;
    }

    .todo-btn-delete:hover {
        background: #c62828;
        color: #fff;
    }

    /* Empty State */
    .todo-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #94a3b8;
    }

    .todo-empty-state i {
        font-size: 2.8rem;
        display: block;
        margin-bottom: 8px;
        color: #d1d5db;
    }

    .todo-empty-state p {
        font-size: 0.82rem;
        margin: 0;
    }

    /* Footer */
    .todo-footer {
        padding: 14px 18px 18px;
        background: #f8fafb;
        border-top: 1px solid #eef0f2;
    }

    .todo-input-row {
        display: flex;
        gap: 8px;
    }

    .todo-input {
        flex: 1;
        border: 1.5px solid #e0e4e8;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 0.2s;
        background: #fff;
        color: #1f2937;
    }

    .todo-input:focus {
        border-color: #163f7a;
        box-shadow: 0 0 0 3px rgba(22, 63, 122, 0.10);
    }

    .todo-add-btn {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        border: none;
        background: #163f7a;
        color: #fff;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .todo-add-btn:hover {
        background: #005555;
        transform: scale(1.06);
    }

    /* Inline Edit */
    .todo-edit-wrap {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .todo-edit-input {
        border: 1.5px solid #163f7a;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 0.875rem;
        outline: none;
        width: 100%;
    }

    .todo-edit-date {
        border: 1.5px solid #e0e4e8;
        border-radius: 8px;
        padding: 5px 10px;
        font-size: 0.8rem;
        outline: none;
        width: 100%;
    }

    .todo-edit-actions {
        display: flex;
        gap: 5px;
        margin-top: 2px;
    }

    .todo-save-btn {
        padding: 5px 14px;
        border-radius: 8px;
        border: none;
        background: #163f7a;
        color: #fff;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.18s;
    }

    .todo-save-btn:hover {
        background: #005555;
    }

    .todo-cancel-btn {
        padding: 5px 14px;
        border-radius: 8px;
        border: none;
        background: #f1f5f9;
        color: #6b7280;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.18s;
    }

    .todo-cancel-btn:hover {
        background: #e5e7eb;
    }

    /* Modal z-index */
    .modal-backdrop {
        z-index: 99980 !important;
    }

    .modal {
        z-index: 99999 !important;
    }
</style>

<script>
    (function () {
        // ── State ──────────────────────────────────────────────
        let allTasks = [];
        let activeFilter = 'all';

        // ── DOM Refs (lazy) ────────────────────────────────────
        const $ = id => document.getElementById(id);

        // ── Bootstrap modal open → fetch ───────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('todoListModal');
            if (!modalEl) return;

            modalEl.addEventListener('show.bs.modal', fetchTasks);

            // Filter buttons
            modalEl.querySelectorAll('.todo-filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    modalEl.querySelectorAll('.todo-filter-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    activeFilter = btn.dataset.filter;
                    renderTasks();
                });
            });

            // Add task
            const addBtn = $('addTask');
            const input = $('taskInput');
            if (addBtn) addBtn.addEventListener('click', addTask);
            if (input) input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); addTask(); } });
        });

        // ── Fetch ──────────────────────────────────────────────
        function fetchTasks() {
            fetch('/todo-lists')
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(data => { allTasks = data; renderTasks(); })
                .catch(err => console.error('Fetch tasks error:', err));
        }

        // ── Render ─────────────────────────────────────────────
        function renderTasks() {
            const list = $('todoList');
            const emptyEl = $('todoEmptyState');
            if (!list) return;
            list.innerHTML = '';

            const filtered = allTasks.filter(t => {
                if (activeFilter === 'pending') return !t.completed;
                if (activeFilter === 'done') return t.completed;
                return true;
            });

            // Update subtitle & progress
            const total = allTasks.length;
            const done = allTasks.filter(t => t.completed).length;
            const pct = total ? Math.round((done / total) * 100) : 0;
            const subtitle = $('todoSubtitle');
            const fill = $('todoProgressFill');
            const label = $('todoProgressLabel');
            if (subtitle) subtitle.textContent = `${done} of ${total} task${total !== 1 ? 's' : ''} completed`;
            if (fill) fill.style.width = pct + '%';
            if (label) label.textContent = pct + '%';

            if (!filtered.length) {
                if (emptyEl) emptyEl.style.display = 'block';
                return;
            }
            if (emptyEl) emptyEl.style.display = 'none';

            filtered.forEach((task, idx) => {
                const li = document.createElement('li');
                li.className = 'todo-item' + (task.completed ? ' done' : '');
                li.draggable = true;
                li.dataset.id = task.id;

                // Reminder badge
                let remBadge = '';
                if (task.reminder_at) {
                    const rDate = new Date(task.reminder_at);
                    const isOver = rDate < new Date() && !task.completed;
                    const cls = task.completed ? 'done' : isOver ? 'danger' : 'info';
                    const str = rDate.toLocaleString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                    remBadge = `<span class="todo-reminder-badge ${cls}"><i class="bx bx-time"></i>${str}</span>`;
                }

                li.innerHTML = `
                        <i class="bx bx-grid-vertical todo-drag"></i>
                        <input type="checkbox" class="todo-check toggleTask" data-id="${task.id}" ${task.completed ? 'checked' : ''} title="${task.completed ? 'Mark incomplete' : 'Mark complete'}">
                        <div class="todo-text-wrap">
                            <span class="todo-task-text" title="${escHtml(task.text)}">${escHtml(task.text)}</span>
                            ${remBadge}
                        </div>
                        <div class="todo-actions">
                            <button class="todo-btn todo-btn-edit editTask" data-id="${task.id}" title="Edit"><i class="bx bx-edit"></i></button>
                            <button class="todo-btn todo-btn-delete deleteTask" data-id="${task.id}" title="Delete"><i class="bx bx-trash"></i></button>
                        </div>`;

                // Drag events
                li.addEventListener('dragstart', e => {
                    li.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', idx);
                });
                li.addEventListener('dragend', () => {
                    li.classList.remove('dragging');
                    const newOrder = [...list.querySelectorAll('li')].map(el => el.dataset.id);
                    fetch('/todo-lists/reorder', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                        body: JSON.stringify({ order: newOrder })
                    });
                });
                li.addEventListener('dragover', e => {
                    e.preventDefault();
                    const dragging = list.querySelector('.dragging');
                    const siblings = [...list.querySelectorAll('li:not(.dragging)')];
                    const next = siblings.find(s => e.clientY <= s.getBoundingClientRect().top + s.offsetHeight / 2);
                    list.insertBefore(dragging, next || null);
                });

                list.appendChild(li);
            });

            bindListEvents();
        }

        // ── Bind row events ────────────────────────────────────
        function bindListEvents() {
            const list = $('todoList');
            if (!list) return;

            // Toggle completion
            list.querySelectorAll('.toggleTask').forEach(chk => {
                chk.addEventListener('change', () => {
                    fetch(`/manage-todolist-item/${chk.dataset.id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                        body: JSON.stringify({ completed: chk.checked })
                    }).then(fetchTasks);
                });
            });

            // Delete
            list.querySelectorAll('.deleteTask').forEach(btn => {
                btn.addEventListener('click', () => {
                    swal({ title: 'Delete this task?', icon: 'warning', buttons: ['Cancel', 'Yes, delete'], dangerMode: true })
                        .then(ok => {
                            if (!ok) return;
                            fetch(`/manage-todolist-item/${btn.dataset.id}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': csrfToken() }
                            }).then(fetchTasks);
                        });
                });
            });

            // Edit
            list.querySelectorAll('.editTask').forEach(btn => {
                btn.addEventListener('click', () => openEditMode(btn));
            });
        }

        // ── Inline Edit ────────────────────────────────────────
        function openEditMode(btn) {
            const li = btn.closest('li');
            if (li.querySelector('.todo-edit-wrap')) return; // already open
            const id = btn.dataset.id;
            const task = allTasks.find(t => t.id == id);
            if (!task) return;

            // Hide normal content
            li.querySelector('.todo-text-wrap').style.display = 'none';
            li.querySelector('.todo-actions').style.display = 'none';

            // Build inline editor
            const wrap = document.createElement('div');
            wrap.className = 'todo-edit-wrap';

            const textIn = document.createElement('input');
            textIn.type = 'text';
            textIn.className = 'todo-edit-input';
            textIn.value = task.text;

            const dateIn = document.createElement('input');
            dateIn.type = 'datetime-local';
            dateIn.className = 'todo-edit-date';
            if (task.reminder_at) dateIn.value = task.reminder_at.replace(' ', 'T').slice(0, 16);

            const acts = document.createElement('div');
            acts.className = 'todo-edit-actions';
            acts.innerHTML = `<button class="todo-save-btn"><i class="bx bx-check"></i> Save</button>
                                  <button class="todo-cancel-btn"><i class="bx bx-x"></i> Cancel</button>`;

            wrap.appendChild(textIn);
            wrap.appendChild(dateIn);
            wrap.appendChild(acts);
            li.insertBefore(wrap, li.querySelector('.todo-actions'));
            textIn.focus();

            const doSave = () => {
                const newText = textIn.value.trim();
                if (!newText) { renderTasks(); return; }
                fetch(`/manage-todolist-item/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                    body: JSON.stringify({ text: newText, completed: task.completed, reminder_at: dateIn.value })
                }).then(fetchTasks);
            };

            acts.querySelector('.todo-save-btn').onclick = doSave;
            acts.querySelector('.todo-cancel-btn').onclick = renderTasks;
            textIn.addEventListener('keydown', e => { if (e.key === 'Enter') doSave(); if (e.key === 'Escape') renderTasks(); });
        }

        // ── Add Task ───────────────────────────────────────────
        function addTask() {
            const input = $('taskInput');
            const val = input ? input.value.trim() : '';
            if (!val) return;
            fetch('/manage-todolist-item', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ text: val, completed: false })
            })
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(newTask => {
                    allTasks.unshift(newTask);
                    input.value = '';
                    renderTasks();
                })
                .catch(() => alert('Could not add task.'));
        }

        // ── Helpers ────────────────────────────────────────────
        function csrfToken() {
            const m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.content : '';
        }
        function escHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
    })();
</script>
