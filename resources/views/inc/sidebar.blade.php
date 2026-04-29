@php
    $company = session('companies');
    $roles = session('roles');
    $features = explode(',', ($roles->features ?? ''));
    $permissions = explode(',', ($roles->permissions ?? ''));
    $roleArray = array_merge($features, $permissions);
    
    if(Auth::user()->isMaster()) {
        $roleArray[] = 'All';
    }
    $standard = ["standard", "premium", "pro"];
    $premium = ["premium", "pro"];
    $pro = ["premium", "pro"];
@endphp
<div class="sidebar @if(isset($_COOKIE['sidebarOpen']) && $_COOKIE['sidebarOpen'] == 'open') open @endif">
    <div class="logo_details sidebar-toggle-trigger">
        @if(!empty($company->logo))
            <img src="{{ asset('assets/images/company/logos/' . ($company->logo ?? '')) }}" alt="{{$company->name ?? ''}}">
        @else
            <div class="logo_name text-white">{{ $company->name ?? 'Admin Panel' }}</div>
        @endif

    </div>
    <ul class="nav-list" id="accordion">

        <li class="profile sidebar-toggle-trigger">
            <div class="profile_details">
                @if(!empty(Auth::user()->photo))
                    <img src="{{ asset('assets/images/profile/' . (Auth::user()->photo ?? '')) }}" class="shadow-sm"
                        alt="{{Auth::user()->name ?? ''}}">
                @else
                    <img src="{{ asset('assets/images/profile/user.png') }}" alt="profile image">
                @endif
                <div class="profile_content">
                    <div class="name">{{ Auth::user()->name ?? '' }}</div>
                    <div class="designation">{{ $roles->title ?? '' }}</div>
                </div>
            </div>
        </li>

        <li class="nav-title">MAIN</li>
        <li>
            <a href="/home" @if(Request::segment(1) == '' || Request::segment(1) == 'home') class="active" @endif>
                <i class="bx bx-grid-alt"></i>
                <span class="link_name">Dashboard</span>
            </a>
        </li>

        @if(Auth::user()->role != 'master' && (in_array('tasks', $roleArray) || in_array('All', $roleArray)))
            <li>
                <a href="/task" @if(Request::segment(1) == 'task' || Request::segment(1) == 'edit-task') class="active" @endif>
                    <i class="bx bx-task"></i>
                    <span class="link_name">Project Tasks</span>
                </a>
            </li>
        @endif

        @if(Auth::user()->role == 'master')
            <li>
                <a href="/companies" @if(Request::segment(1) == 'companies') class="active" @endif><i
                        class="bx bx-building"></i> <span class="link_name">Companies</span></a>
            </li>
            <li>
                <a href="/subscriptions" @if(Request::segment(1) == 'subscriptions') class="active" @endif>
                    <i class="bx bx-crown"></i>
                    <span class="link_name">Subscriptions</span>
                </a>
            </li>
            <li>
                <a href="/enquiries" @if(Request::segment(1) == 'enquiries') class="active" @endif>
                    <i class="bx bx-mail-send"></i>
                    <span class="link_name">Landing Enquiries</span>
                </a>
            </li>
        @endif


        @if(Auth::user()->role != 'master')
            <li class="nav-title">CRM</li>
            @if(in_array('leads', $roleArray) || in_array('All', $roleArray))
                <li>
                    <span class="divider" data-bs-toggle="collapse" data-bs-target="#leads-menu">
                        <span class="divider-left"><i class="bx bx-user-check"></i><label>Leads</label></span>
                        <i class="bx bx-chevron-down"></i>
                    </span>
                    <div id="leads-menu"
                        class="collapse @if(Request::segment(1) == 'leads' || Request::segment(1) == 'leads' && Request::segment(2) == 'kanban') show @endif"
                        data-bs-parent="#accordion">
                        <ul class="sb_submenu">
                            <li>
                                <a href="/leads" @if(Request::segment(1) == 'leads' && Request::segment(2) == '') class="active"
                                @endif>
                                    <i class="bx bx-list-ul"></i>
                                    <span class="link_name">List View</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('leads.kanban') }}" @if(Request::segment(1) == 'leads' && Request::segment(2) == 'kanban') class="active" @endif>
                                    <i class="bx bx-grid-alt"></i>
                                    <span class="link_name">Kanban View</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            <li class="nav-title">ANALYTICS</li>
            <li>
                <a href="/reports" @if(Request::segment(1) == 'reports' && Request::segment(2) == '') class="active" @endif>
                    <i class="bx bx-bar-chart-alt-2"></i>
                    <span class="link_name">Sales Analytics</span>
                </a>
            </li>
            <li>
                <a href="/reports/email-tracking" @if(Request::segment(1) == 'reports' && Request::segment(2) == 'email-tracking') class="active" @endif>
                    <i class="bx bx-mail-send"></i>
                    <span class="link_name">Email Analytics</span>
                </a>
            </li>

            @if(in_array('clients', $roleArray) || in_array('All', $roleArray))
                <li>
                    <a href="/clients" @if(Request::segment(1) == 'clients') class="active" @endif>
                        <i class="bx bx-user"></i>
                        <span class="link_name">Customers</span>
                    </a>

                </li>
            @endif

            @if(in_array('projects', $roleArray) || in_array('All', $roleArray))
                @if(in_array(($company->plan ?? ''), $premium))
                    <li>
                        <a href="/projects" @if(Request::segment(1) == 'projects' || Request::segment(1) == 'manage-project') class="active" @endif>
                            <i class="bx bx-briefcase"></i>
                            <span class="link_name">Projects</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="javascript:void(0);" onclick="upgradeAlert('Projects')">
                            <i class="bx bx-lock text-warning"></i>
                            <span class="link_name text-muted">Projects</span>
                        </a>
                    </li>
                @endif
            @endif

            <li class="nav-title">SALES</li>
            <li>
                @if(in_array(($company->plan ?? ''), $premium))
                <span class="divider" data-bs-toggle="collapse" data-bs-target="#sales-menu">
                    <span class="divider-left"><i class="bx bx-trending-up"></i><label>Sales Pipeline</label></span>
                    <i class="bx bx-chevron-down"></i>
                </span>
                <div id="sales-menu"
                    class="collapse @if(Request::segment(1) == 'proposals' || Request::segment(1) == 'invoices' || Request::segment(1) == 'contracts' || Request::segment(1) == 'recoveries' || Request::segment(1) == 'manage-proposal' || Request::segment(1) == 'manage-invoice' || Request::segment(1) == 'manage-contract' || Request::segment(1) == 'manage-recovery') show @endif"
                    data-bs-parent="#accordion">
                    <ul class="sb_submenu">

                        @if(in_array('proposals', $roleArray) || in_array('All', $roleArray))
                            @if(in_array(($company->plan ?? ''), $premium))
                                <li>
                                    <a href="/proposals" @if(Request::segment(1) == 'proposals' || Request::segment(1) == 'manage-proposal') class="active" @endif>
                                        <i class="bx bx-briefcase"></i>
                                        <span class="link_name">Proposals</span>
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="javascript:void(0);" onclick="upgradeAlert('Proposals')">
                                        <i class="bx bx-lock text-warning"></i>
                                        <span class="link_name text-muted">Proposals</span>
                                    </a>
                                </li>
                            @endif
                        @endif

                        @if(in_array('invoice', $roleArray) || in_array('All', $roleArray))
                            @if(in_array(($company->plan ?? ''), $premium))
                                <li>
                                    <a href="/invoices" @if(Request::segment(1) == 'invoices' || Request::segment(1) == 'manage-invoice') class="active" @endif>
                                        <i class="bx bx-file"></i>
                                        <span class="link_name">Invoices</span>
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="javascript:void(0);" onclick="upgradeAlert('Invoices')">
                                        <i class="bx bx-lock text-warning"></i>
                                        <span class="link_name text-muted">Invoices</span>
                                    </a>
                                </li>
                            @endif
                        @endif

                        @if(in_array('contracts', $roleArray) || in_array('All', $roleArray))
                            @if(in_array(($company->plan ?? ''), $premium) || Auth::user()->role == 'master')
                                <li>
                                    <a href="/contracts" @if(Request::segment(1) == 'contracts' || Request::segment(1) == 'manage-contract') class="active" @endif>
                                        <i class="bx bx-box"></i>
                                        <span class="link_name">Contracts</span>
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="javascript:void(0);" onclick="upgradeAlert('Contracts')">
                                        <i class="bx bx-lock text-warning"></i>
                                        <span class="link_name text-muted">Contracts</span>
                                    </a>
                                </li>
                            @endif
                        @endif

                        @if(in_array('recoveries', $roleArray) || in_array('All', $roleArray))
                            @if(in_array(($company->plan ?? ''), $premium))
                                <li>
                                    <a href="/recoveries" @if(Request::segment(1) == 'recoveries' || Request::segment(1) == 'manage-recovery') class="active" @endif>
                                        <i class="bx bx-money"></i>
                                        <span class="link_name">Recovery</span>
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="javascript:void(0);" onclick="upgradeAlert('Recovery')">
                                        <i class="bx bx-lock text-warning"></i>
                                        <span class="link_name text-muted">Recovery</span>
                                    </a>
                                </li>
                            @endif
                        @endif
                    </ul>
                </div>
                @else
                <span class="divider cursor-pointer" onclick="upgradeAlert('Sales Pipeline')">
                    <span class="divider-left"><i class="bx bx-lock text-warning"></i><label class="text-muted" style="cursor:pointer">Sales Pipeline</label></span>
                </span>
                @endif
            </li>

            <li class="nav-title">MARKETING</li>
            @if(in_array('campaigns', $roleArray) || in_array('All', $roleArray))
                @if(in_array(($company->plan ?? ''), $premium))
                    <li>
                        <a href="/campaigns" @if(Request::segment(1) == 'campaigns') class="active" @endif>
                            <i class="bx bx-broadcast"></i>
                            <span class="link_name">Campaigns</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="javascript:void(0);" onclick="upgradeAlert('Campaigns')">
                            <i class="bx bx-lock text-warning"></i>
                            <span class="link_name text-muted">Campaigns</span>
                        </a>
                    </li>
                @endif
            @endif

            @if(in_array('automations', $roleArray) || in_array('All', $roleArray))
                @if(in_array(($company->plan ?? ''), $premium))
                    <li>
                        <a href="/automations" @if(Request::segment(1) == 'automations') class="active" @endif>
                            <i class="bx bx-git-branch"></i>
                            <span class="link_name">Automations</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="javascript:void(0);" onclick="upgradeAlert('Automations')">
                            <i class="bx bx-lock text-warning"></i>
                            <span class="link_name text-muted">Automations</span>
                        </a>
                    </li>
                @endif
            @endif

            <li class="nav-title">OPERATIONS</li>

            @if(in_array('reports', $roleArray) || in_array('All', $roleArray))
                @if(in_array(($company->plan ?? ''), $premium))
                    <li>
                        <a href="/reports" @if(Request::segment(1) == 'reports') class="active" @endif>
                            <i class="bx bx-line-chart"></i>
                            <span class="link_name">Reports</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="javascript:void(0);" onclick="upgradeAlert('Reports')">
                            <i class="bx bx-lock text-warning"></i>
                            <span class="link_name text-muted">Reports</span>
                        </a>
                    </li>
                @endif
            @endif

            @if(in_array('attendances', $roleArray) || in_array('All', $roleArray))
                @if(in_array(($company->plan ?? ''), $premium))
                    <li>
                        <a href="/attendances" @if(Request::segment(1) == 'attendances') class="active" @endif>
                            <i class="bx bx-calendar-check"></i>
                            <span class="link_name">Attendance</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="javascript:void(0);" onclick="upgradeAlert('Attendance')">
                            <i class="bx bx-lock text-warning"></i>
                            <span class="link_name text-muted">Attendance</span>
                        </a>
                    </li>
                @endif
            @endif

            @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray))
                @if(in_array(($company->plan ?? ''), $premium))
                    <li>
                        <a href="/users" @if(Request::segment(1) == 'users' || Request::segment(1) == 'manage-user') class="active" @endif>
                            <i class="bx bx-group"></i>
                            <span class="link_name">Users</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="javascript:void(0);" onclick="upgradeAlert('Users')">
                            <i class="bx bx-lock text-warning"></i>
                            <span class="link_name text-muted">Users</span>
                        </a>
                    </li>
                @endif
            @endif
        @endif

        @if(in_array('support_assign', $roleArray) || in_array('All', $roleArray))
        <li>
            <a href="/support" @if(Request::segment(1) == 'support') class="active" @endif>
                <i class="bx bx-help-circle"></i>
                <span class="link_name">Support</span>
            </a>
        </li>
        @endif

        <li class="nav-title">SYSTEM</li>
        <li>
            <a href="/integrations" @if(Request::segment(1) == 'integrations') class="active" @endif>
                <i class="bx bx-cog"></i>
                <span class="link_name">Integrations</span>
            </a>
        </li>
        <li>
            <a href="/products" @if(Request::segment(1) == 'products') class="active" @endif>
                <i class="bx bx-package"></i>
                <span class="link_name">Products Master</span>
            </a>
        </li>

        @if(Auth::user()->role == 'master')
            <li>
                <a href="/licensing" @if(Request::segment(1) == 'licensing' || Request::segment(1) == 'manage-license')
                class="active" @endif>
                    <i class="bx bx-file"></i>
                    <span class="link_name">Licensing</span>
                </a>
                <span class="tooltip">Licensing</span>
            </li>
        @endif

        <li>
            @if(in_array(($company->plan ?? ''), $premium))
            <span class="divider" data-bs-toggle="collapse" data-bs-target="#s">
                <span class="divider-left"><i class="bx bx-cog"></i><label>Settings</label></span>
                <i class="bx bx-chevron-down"></i>
            </span>
            <div id="s"
                class="collapse @if(Request::segment(1) == 'my-profile' || Request::segment(1) == 'smtp-settings' || Request::segment(1) == 'email-templates' || Request::segment(1) == 'my-company' || Request::segment(1) == 'reset-password' || Request::segment(1) == 'role-settings' || Request::segment(1) == 'manage-role-setting') show @endif"
                data-bs-parent="#accordion">
                <ul class="sb_submenu">

                    @if(in_array('company_edit', $roleArray) || in_array('All', $roleArray))
                        @if(in_array(($company->plan ?? ''), $premium))
                            <li>
                                <a href="/my-company" @if(Request::segment(1) == 'my-company') class="active" @endif><i
                                        class="bx bx-building"></i> <span class="link_name">My Company</span></a>
                            </li>
                        @else
                            <li>
                                <a href="javascript:void(0);" onclick="upgradeAlert('My Company')">
                                    <i class="bx bx-lock text-warning"></i>
                                    <span class="link_name text-muted">My Company</span>
                                </a>
                            </li>
                        @endif
                    @endif

                    @if(Auth::user()->role != 'master')
                        <li>
                            <a href="/my-profile" @if(Request::segment(1) == 'my-profile') class="active" @endif><i
                                    class="bx bx-user"></i> <span class="link_name">My Profile</span></a>
                        </li>
                    @endif

                    @if(in_array('smtp_edit', $roleArray) || in_array('All', $roleArray))
                        @if(in_array(($company->plan ?? ''), $premium))
                            <li>
                                <a href="/smtp-settings" @if(Request::segment(1) == 'smtp-settings') class="active" @endif><i
                                        class="bx bx-cog"></i> <span class="link_name">SMTP Settings</span></a>
                                <span class="tooltip">SMTP Settings</span>
                            </li>
                            <li>
                                <a href="/email-templates" @if(Request::segment(1) == 'email-templates') class="active" @endif>
                                    <i class="bx bx-envelope"></i>
                                    <span class="link_name">Email Templates</span>
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="javascript:void(0);" onclick="upgradeAlert('SMTP Settings')">
                                    <i class="bx bx-lock text-warning"></i>
                                    <span class="link_name text-muted">SMTP Settings</span>
                                </a>
                                <span class="tooltip">SMTP Settings</span>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="upgradeAlert('Email Templates')">
                                    <i class="bx bx-lock text-warning"></i>
                                    <span class="link_name text-muted">Email Templates</span>
                                </a>
                            </li>
                        @endif
                    @endif

                    <li>
                        <a href="/reset-password" @if(Request::segment(1) == 'reset-password') class="active" @endif><i
                                class="bx bx-lock"></i> <span class="link_name">Reset Password</span></a>

                    </li>

                    @if(in_array('settings', $roleArray) || in_array('All', $roleArray))
                        @if(in_array(($company->plan ?? ''), $premium))
                            <li>
                                <a href="/role-settings" @if(Request::segment(1) == 'role-settings') class="active" @endif><i
                                        class="bx bx-shield"></i> <span class="link_name">Role Settings</span></a>
                            </li>
                        @else
                            <li>
                                <a href="javascript:void(0);" onclick="upgradeAlert('Role Settings')">
                                    <i class="bx bx-lock text-warning"></i>
                                    <span class="link_name text-muted">Role Settings</span>
                                </a>
                            </li>
                        @endif
                    @endif
                </ul>

            </div>
            @else
            <span class="divider cursor-pointer" onclick="upgradeAlert('Settings')">
                <span class="divider-left"><i class="bx bx-lock text-warning"></i><label class="text-muted" style="cursor:pointer">Settings</label></span>
            </span>
            @endif
        </li>
    </ul>
</div>
