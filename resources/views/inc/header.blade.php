<header class="dashboard-header">

    {{-- LEFT: Mobile menu toggle + Page Title --}}
    <div class="header-left">
        <button class="header-menu-btn" id="mbtn" aria-label="Open sidebar">
            <i class="bx bx-menu"></i>
            <span class="d-none d-md-inline" style="font-size: 0.82rem; font-weight: 700; margin-left: 8px;">MENU</span>
        </button>
        <div class="header-title-block">
            <h1 class="header-page-title">{{ $title ?? 'Dashboard' }}</h1>
            <span class="header-greeting d-none d-md-block">
                Good {{ \Carbon\Carbon::now()->hour < 12 ? 'morning' : (\Carbon\Carbon::now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', Auth::user()->name)[0] }}
            </span>
        </div>
    </div>

    {{-- RIGHT: Actions bar --}}
    <div class="header-right">

        @if(Auth::user()->role != 'master')
            {{-- Quick Add --}}
            <div class="dropdown">
                <button class="header-action-btn header-action-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-plus"></i>
                    <span class="d-none d-lg-inline">Quick Add</span>
                </button>
                <ul class="dropdown-menu header-dropdown dropdown-menu-end mt-2">
                    <li class="dropdown-section-label">Create New</li>
                    <li>
                        <a class="dropdown-item header-dropdown-item" href="/manage-lead">
                            <span class="hdi-icon" style="background:rgba(26,115,232,0.08); color:#1a73e8;"><i class="bx bx-user-plus"></i></span>
                            <div>
                                <div class="hdi-title">New Lead</div>
                                <small class="hdi-sub">Add to sales pipeline</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item header-dropdown-item" href="/manage-client">
                            <span class="hdi-icon" style="background:rgba(52,168,83,0.08); color:#34a853;"><i class="bx bx-group"></i></span>
                            <div>
                                <div class="hdi-title">New Client</div>
                                <small class="hdi-sub">Add to client base</small>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1 mx-3"></li>
                    <li>
                        <a class="dropdown-item header-dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#todoListModal">
                            <span class="hdi-icon" style="background:rgba(251,188,4,0.10); color:#f9a825;"><i class="bx bx-task"></i></span>
                            <div>
                                <div class="hdi-title">Add Task</div>
                                <small class="hdi-sub">Add to your to-do list</small>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- My Tasks Button --}}
            <button type="button" class="header-action-btn d-none d-sm-flex" data-bs-toggle="modal" data-bs-target="#todoListModal" title="My Tasks">
                <i class="bx bx-check-double"></i>
                <span class="d-none d-lg-inline">Tasks</span>
            </button>
        @endif

        {{-- Notifications --}}
        <div class="dropdown">
            <button class="header-icon-btn position-relative" type="button" data-bs-toggle="dropdown" aria-label="Notifications">
                <i class="bx bx-bell"></i>
                @php 
                    if(Auth::user()->role == 'master') {
                        $supportOpenCount = \App\Models\SupportTicket::where('status', 0)->count();
                        $totalNotifs = $supportOpenCount;
                        $notifType = 'master';
                    } else {
                        $newLeadsCount = \App\Models\Leads::where('status', 0)->count(); 
                        $overdueCount = \App\Models\Leads::where('leads.status', 1)
                            ->join('lead_comments', 'leads.id', '=', 'lead_comments.lead_id')
                            ->where('lead_comments.next_date', '<', now())
                            ->distinct()
                            ->count('leads.id');
                        $totalNotifs = $newLeadsCount + $overdueCount;
                        $notifType = 'company';
                    }
                @endphp
                @if($totalNotifs > 0)
                    <span class="header-notif-badge">{{ $totalNotifs > 99 ? '99+' : $totalNotifs }}</span>
                @endif
            </button>
            <ul class="dropdown-menu header-dropdown dropdown-menu-end mt-2" style="min-width: 300px;">
                <li class="dropdown-section-label">Notifications</li>
                
                @if(Auth::user()->role == 'master')
                    @php 
                        $openTickets = \App\Models\SupportTicket::with('company')->where('status', 0)->latest()->take(5)->get();
                    @endphp
                    @forelse($openTickets as $ticket)
                        <li>
                            <a class="dropdown-item header-dropdown-item" href="/support">
                                <span class="hdi-icon" style="background:rgba(26,115,232,0.08); color:#1a73e8;"><i class="bx bx-help-circle"></i></span>
                                <div>
                                    <div class="hdi-title">{{ $ticket->subject }}</div>
                                    <small class="hdi-sub">From: {{ $ticket->company->name ?? 'Unknown' }}</small>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li>
                            <div class="header-dropdown-empty">
                                <i class="bx bx-bell-off"></i>
                                <span>No open tickets</span>
                            </div>
                        </li>
                    @endforelse
                @else
                    @if($newLeadsCount > 0)
                        <li>
                            <a class="dropdown-item header-dropdown-item" href="/newleads?status=0">
                                <span class="hdi-icon" style="background:rgba(26,115,232,0.08); color:#1a73e8;"><i class="bx bx-user-plus"></i></span>
                                <div>
                                    <div class="hdi-title">Fresh Leads</div>
                                    <small class="hdi-sub">{{ $newLeadsCount }} fresh leads waiting</small>
                                </div>
                            </a>
                        </li>
                    @endif
                    @if($overdueCount > 0)
                        <li>
                            <a class="dropdown-item header-dropdown-item" href="/newleads?status=1">
                                <span class="hdi-icon" style="background:rgba(234,67,53,0.08); color:#ea4335;"><i class="bx bx-time-five"></i></span>
                                <div>
                                    <div class="hdi-title">Overdue Follow-ups</div>
                                    <small class="hdi-sub" style="color:#ea4335;">{{ $overdueCount }} tasks past reminder</small>
                                </div>
                            </a>
                        </li>
                    @endif
                    @if($totalNotifs === 0)
                        <li>
                            <div class="header-dropdown-empty">
                                <i class="bx bx-bell-off"></i>
                                <span>No new alerts</span>
                            </div>
                        </li>
                    @endif
                @endif
            </ul>
        </div>

        {{-- Global Search Button - Hide only for master if it only searches CRM data --}}
        @if(Auth::user()->role != 'master')
            <button class="header-icon-btn d-flex" title="Search" onclick="openGlobalSearch()">
                <i class="bx bx-search"></i>
            </button>
        @endif

        {{-- User Avatar Dropdown --}}
        <div class="dropdown">
            <a href="#" class="header-user-btn no-caret dropdown-toggle" data-bs-toggle="dropdown" aria-label="Account menu">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=006666&color=ffff00&size=64"
                     class="header-avatar" width="34" height="34" alt="{{ Auth::user()->name }}">
                <i class="bx bx-chevron-down d-none d-lg-block" style="font-size: 0.9rem; color: #5f6368;"></i>
            </a>
            <ul class="dropdown-menu header-dropdown dropdown-menu-end mt-2" style="min-width: 200px;">
                <li class="header-user-meta px-3 py-2">
                    <div class="fw-bold" style="font-size: 0.83rem; color: #202124;">{{ Auth::user()->name }}</div>
                    <div style="font-size: 0.72rem; color: #5f6368;">{{ Auth::user()->email }}</div>
                </li>
                <li><hr class="dropdown-divider my-1 mx-2"></li>
                <li>
                    <a class="dropdown-item header-dropdown-item-sm" href="/my-profile">
                        <i class="bx bx-user"></i> My Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item header-dropdown-item-sm text-danger" href="/signout">
                        <i class="bx bx-log-out"></i> Sign Out
                    </a>
                </li>
            </ul>
        </div>

    </div>

</header>

{{-- ═══════════════════════════════════════════════════════════
     GLOBAL SEARCH MODAL — Ctrl+K Universal CRM Search
════════════════════════════════════════════════════════════ --}}
<div id="globalSearchOverlay" onclick="closeGlobalSearch()" style="display:none;"></div>
<div id="globalSearchModal" style="display:none;" role="dialog" aria-label="Global Search">
    <div class="gs-header">
        <i class="bx bx-search gs-icon"></i>
        <input type="text" id="gsInput" class="gs-input" placeholder="Search leads, clients, projects, proposals…" autocomplete="off" spellcheck="false">
        <button class="gs-close" onclick="closeGlobalSearch()" title="Close (Esc)"><i class="bx bx-x"></i></button>
    </div>
    <div id="gsResults" class="gs-results">
        <div class="gs-hint">
            <span><kbd>↑</kbd><kbd>↓</kbd> Navigate</span>
            <span><kbd>Enter</kbd> Open</span>
            <span><kbd>Esc</kbd> Close</span>
        </div>
    </div>
</div>

<style>
/* ── Sidebar Toggle Button (Hamburger) ── */
.header-menu-btn {
    background: #006666 !important;
    border: none;
    border-radius: 8px;
    width: 98px;
    height: 42px;
    display: flex !important;
    align-items: center;
    justify-content: center;
    color: #ffffff !important;
    font-size: 1.6rem;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    margin-right: 18px;
    /* box-shadow: 0 4px 12px rgba(0, 102, 102, 0.2); */
    z-index: 100;
}

.header-menu-btn:hover {
    background: #004d4d !important;
    transform: scale(1.08);
    box-shadow: 0 6px 16px rgba(0, 102, 102, 0.3);
}

.header-menu-btn:active {
    transform: scale(0.92);
}

/* ── Mobile View Adjustments ── */
@media (max-width: 768px) {
    .header-menu-btn {
        width: 42px !important;
        margin-right: 12px;
    }
    .header-menu-btn span {
        display: none !important; /* Hide "MENU" text on small mobile */
    }
}
.header-search-trigger {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #f1f3f4;
    border: 1px solid #e0e4e8;
    border-radius: 20px;
    padding: 6px 14px;
    color: #5f6368;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.18s;
    white-space: nowrap;
    min-width: 160px;
}
.header-search-trigger:hover { background: #e8eaed; border-color: #00666630; color: #3c4043; }
.header-search-trigger i { font-size: 1rem; }
.header-search-trigger kbd {
    margin-left: auto; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.2px;
    background: #fff; border: 1px solid #dce1e7; border-radius: 5px;
    padding: 1px 5px; color: #80868b;
}

/* ── Global Search Overlay ── */
#globalSearchOverlay {
    position: fixed; inset: 0;
    background: rgba(32,33,36,0.45);
    backdrop-filter: blur(3px);
    z-index: 99990;
    animation: gsOverlayIn 0.18s ease;
}

/* ── Global Search Modal ── */
#globalSearchModal {
    position: fixed;
    top: 80px; left: 50%; transform: translateX(-50%);
    width: 640px; max-width: calc(100vw - 32px);
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 24px 80px rgba(0,0,0,0.22);
    z-index: 99995;
    overflow: hidden;
    animation: gsModalIn 0.2s cubic-bezier(.4,0,.2,1);
}

.gs-header {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 16px;
    border-bottom: 1px solid #e8eaed;
    background: #fff;
}
.gs-icon { font-size: 1.3rem; color: #006666; flex-shrink: 0; }
.gs-input {
    flex: 1; border: none; outline: none;
    font-size: 1rem; color: #202124; background: transparent;
    font-weight: 400;
}
.gs-input::placeholder { color: #9aa0a6; }
.gs-close {
    background: #f1f3f4; border: none; width: 30px; height: 30px;
    border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: #5f6368; font-size: 1.1rem; flex-shrink: 0; transition: background 0.15s;
}
.gs-close:hover { background: #e8eaed; }

.gs-results { max-height: 400px; overflow-y: auto; }

.gs-hint {
    display: flex; gap: 16px; align-items: center; justify-content: center;
    padding: 20px 16px;
    color: #9aa0a6; font-size: 0.75rem;
}
.gs-hint kbd {
    background: #f1f3f4; border: 1px solid #dce1e7; border-radius: 5px;
    padding: 1px 5px; font-size: 0.68rem; margin-right: 2px; color: #5f6368;
}
.gs-hint span { display: flex; align-items: center; gap: 3px; }

.gs-section-label {
    font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px;
    color: #9aa0a6; padding: 10px 16px 4px;
}
.gs-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 16px; cursor: pointer; text-decoration: none;
    color: #202124; transition: background 0.12s;
    border-left: 3px solid transparent;
}
.gs-item:hover, .gs-item.active {
    background: #f8f9fa;
    border-left-color: #006666;
    color: #202124;
}
.gs-item-icon {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 1rem;
}
.gs-item-body { flex: 1; min-width: 0; }
.gs-item-title { font-size: 0.875rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gs-item-sub { font-size: 0.72rem; color: #80868b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gs-item-type { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; color: #9aa0a6; flex-shrink: 0; }

.gs-empty { text-align: center; padding: 32px 16px; color: #9aa0a6; }
.gs-empty i { font-size: 2rem; display: block; margin-bottom: 8px; }

.gs-loading { text-align: center; padding: 20px; }
.gs-spinner {
    width: 24px; height: 24px; border: 3px solid #e8eaed;
    border-top-color: #006666; border-radius: 50%;
    animation: gsSpin 0.7s linear infinite; display: inline-block;
}

@keyframes gsOverlayIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes gsModalIn { from { opacity: 0; transform: translateX(-50%) translateY(-12px) scale(0.97); } to { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); } }
@keyframes gsSpin { to { transform: rotate(360deg); } }

@media (max-width: 576px) {
    #globalSearchModal { top: 60px; }
    .header-search-trigger { min-width: unset; }
}
</style>

<script>
(function () {
    let gsTimer = null;
    let gsActiveIndex = -1;
    let gsItems = [];

    window.openGlobalSearch = function () {
        document.getElementById('globalSearchOverlay').style.display = 'block';
        document.getElementById('globalSearchModal').style.display = 'block';
        setTimeout(() => document.getElementById('gsInput').focus(), 60);
        resetResults();
    };

    window.closeGlobalSearch = function () {
        document.getElementById('globalSearchOverlay').style.display = 'none';
        document.getElementById('globalSearchModal').style.display = 'none';
        document.getElementById('gsInput').value = '';
        gsActiveIndex = -1; gsItems = [];
    };

    // Keyboard shortcut Ctrl+K / Cmd+K
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const modal = document.getElementById('globalSearchModal');
            modal.style.display === 'none' ? openGlobalSearch() : closeGlobalSearch();
        }
        if (e.key === 'Escape' && document.getElementById('globalSearchModal').style.display !== 'none') {
            closeGlobalSearch();
        }
        if (document.getElementById('globalSearchModal').style.display !== 'none') {
            if (e.key === 'ArrowDown') { e.preventDefault(); navigateResults(1); }
            if (e.key === 'ArrowUp')   { e.preventDefault(); navigateResults(-1); }
            if (e.key === 'Enter')     { e.preventDefault(); openActive(); }
        }
    });

    // Search input
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('gsInput');
        if (!input) return;
        input.addEventListener('input', function () {
            clearTimeout(gsTimer);
            const q = this.value.trim();
            if (q.length < 2) { resetResults(); return; }
            showLoading();
            gsTimer = setTimeout(() => doSearch(q), 280);
        });
    });

    function doSearch(q) {
        fetch('/global-search?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => renderResults(data, q))
        .catch(() => renderError());
    }

    function renderResults(data, q) {
        const el = document.getElementById('gsResults');
        gsItems = [];
        let html = '';

        const sections = [
            { key: 'leads',     label: 'Leads',     icon: 'bx bx-user-check',   color: '#1a73e8', bg: 'rgba(26,115,232,0.08)' },
            { key: 'clients',   label: 'Clients',   icon: 'bx bx-group',        color: '#34a853', bg: 'rgba(52,168,83,0.08)'  },
            { key: 'projects',  label: 'Projects',  icon: 'bx bx-layer',        color: '#006666', bg: 'rgba(0,102,102,0.08)'  },
            { key: 'proposals', label: 'Proposals', icon: 'bx bx-file',         color: '#f29900', bg: 'rgba(242,153,0,0.08)'  },
        ];

        let hasAny = false;
        sections.forEach(s => {
            const arr = data[s.key] || [];
            if (!arr.length) return;
            hasAny = true;
            html += `<div class="gs-section-label">${s.label}</div>`;
            arr.forEach(item => {
                gsItems.push({ url: item.url });
                html += `<a class="gs-item" href="${item.url}">
                    <span class="gs-item-icon" style="background:${s.bg}; color:${s.color}"><i class="${s.icon}"></i></span>
                    <span class="gs-item-body">
                        <span class="gs-item-title">${highlight(item.title, q)}</span>
                        <span class="gs-item-sub">${item.sub || ''}</span>
                    </span>
                    <span class="gs-item-type">${s.label.slice(0,-1)}</span>
                </a>`;
            });
        });

        if (!hasAny) {
            html = `<div class="gs-empty"><i class="bx bx-search-alt"></i>No results for "<strong>${escHtml(q)}</strong>"</div>`;
        }

        el.innerHTML = html;
        gsItems = el.querySelectorAll('.gs-item');
        gsActiveIndex = -1;
    }

    function highlight(text, q) {
        if (!q) return escHtml(text);
        const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return escHtml(text).replace(re, '<mark style="background:#fff3cd;padding:0 1px;border-radius:2px;">$1</mark>');
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function navigateResults(dir) {
        if (!gsItems.length) return;
        if (gsActiveIndex >= 0) gsItems[gsActiveIndex].classList.remove('active');
        gsActiveIndex = Math.max(0, Math.min(gsItems.length - 1, gsActiveIndex + dir));
        gsItems[gsActiveIndex].classList.add('active');
        gsItems[gsActiveIndex].scrollIntoView({ block: 'nearest' });
    }

    function openActive() {
        if (gsActiveIndex >= 0 && gsItems[gsActiveIndex]) {
            window.location.href = gsItems[gsActiveIndex].href;
        }
    }

    function showLoading() {
        document.getElementById('gsResults').innerHTML = '<div class="gs-loading"><span class="gs-spinner"></span></div>';
    }

    function resetResults() {
        document.getElementById('gsResults').innerHTML = '<div class="gs-hint"><span><kbd>↑</kbd><kbd>↓</kbd> Navigate</span><span><kbd>Enter</kbd> Open</span><span><kbd>Esc</kbd> Close</span></div>';
        gsActiveIndex = -1; gsItems = [];
    }

    function renderError() {
        document.getElementById('gsResults').innerHTML = '<div class="gs-empty"><i class="bx bx-error-circle"></i>Search error. Please try again.</div>';
    }
})();
</script>

