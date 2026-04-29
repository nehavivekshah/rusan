<?php
$content = file_get_contents('resources/views/inc/sidebar.blade.php');

$replacements = [
    [
        'feature' => 'projects',
        'label' => 'Projects',
        'icon' => 'bx bx-briefcase',
        'url' => '/projects',
        'active' => "Request::segment(1) == 'projects' || Request::segment(1) == 'manage-project'"
    ],
    [
        'feature' => 'proposals',
        'label' => 'Proposals',
        'icon' => 'bx bx-briefcase',
        'url' => '/ ',
        'active' => "Request::segment(1) == 'proposals' || Request::segment(1) == 'manage-proposal'"
    ],
    [
        'feature' => 'invoice',
        'label' => 'Invoices',
        'icon' => 'bx bx-file',
        'url' => '/invoices',
        'active' => "Request::segment(1) == 'invoices' || Request::segment(1) == 'manage-invoice'"
    ],
    [
        'feature' => 'contracts',
        'label' => 'Contracts',
        'icon' => 'bx bx-box',
        'url' => '/contracts',
        'active' => "Request::segment(1) == 'contracts' || Request::segment(1) == 'manage-contract'"
    ],
    [
        'feature' => 'recoveries',
        'label' => 'Recovery',
        'icon' => 'bx bx-money',
        'url' => '/recoveries',
        'active' => "Request::segment(1) == 'recoveries' || Request::segment(1) == 'manage-recovery'"
    ],
    [
        'feature' => 'campaigns',
        'label' => 'Campaigns',
        'icon' => 'bx bx-broadcast',
        'url' => '/campaigns',
        'active' => "Request::segment(1) == 'campaigns'"
    ],
    [
        'feature' => 'automations',
        'label' => 'Automations',
        'icon' => 'bx bx-git-branch',
        'url' => '/automations',
        'active' => "Request::segment(1) == 'automations'"
    ],
    [
        'feature' => 'reports',
        'label' => 'Reports',
        'icon' => 'bx bx-line-chart',
        'url' => '/reports',
        'active' => "Request::segment(1) == 'reports'"
    ],
    [
        'feature' => 'attendances',
        'label' => 'Attendance',
        'icon' => 'bx bx-calendar-check',
        'url' => '/attendances',
        'active' => "Request::segment(1) == 'attendances'"
    ],
    [
        'feature' => 'users_assign',
        'label' => 'Users',
        'icon' => 'bx bx-group',
        'url' => '/users',
        'active' => "Request::segment(1) == 'users' || Request::segment(1) == 'manage-user'"
    ],
    [
        'feature' => 'company_edit',
        'label' => 'My Company',
        'icon' => 'bx bx-building',
        'url' => '/my-company',
        'active' => "Request::segment(1) == 'my-company'"
    ],
    [
        'feature' => 'smtp_edit',
        'label' => 'SMTP Settings',
        'icon' => 'bx bx-cog',
        'url' => '/smtp-settings',
        'active' => "Request::segment(1) == 'smtp-settings'",
        'extra' => '<span class="tooltip">SMTP Settings</span>'
    ],
    [
        'feature' => 'smtp_edit',
        'label' => 'Email Templates',
        'icon' => 'bx bx-envelope',
        'url' => '/email-templates',
        'active' => "Request::segment(1) == 'email-templates'"
    ],
    [
        'feature' => 'settings',
        'label' => 'Role Settings',
        'icon' => 'bx bx-shield',
        'url' => '/role-settings',
        'active' => "Request::segment(1) == 'role-settings'"
    ]
];

foreach ($replacements as $rep) {
    $f = $rep['feature'];
    // Find the @if block for this feature. Regex needs to match the whole block.
    // The existing blocks usually look like:
    // @if(in_array('projects', $roleArray) || ...)
    //     <li>
    //         <a href="/projects" ...> ... </a>
    //     </li>
    // @endif

    // Some features like Contracts have Auth::user()->role == 'master' in the if condition.
    // So let's write a generic block replacement.

    $extra = isset($rep['extra']) ? "\n                            " . $rep['extra'] : "";

    $newBlock = "
        @if(in_array('{$f}', \$roleArray) || in_array('All', \$roleArray))
            @if(in_array((\$company->plan ?? ''), \$premium))
                <li>
                    <a href=\"{$rep['url']}\" @if({$rep['active']}) class=\"active\" @endif>
                        <i class=\"{$rep['icon']}\"></i>
                        <span class=\"link_name\">{$rep['label']}</span>
                    </a>{$extra}
                </li>
            @else
                <li>
                    <a href=\"javascript:void(0);\" onclick=\"upgradeAlert()\">
                        <i class=\"bx bx-lock text-warning\"></i>
                        <span class=\"link_name text-muted\">{$rep['label']}</span>
                    </a>{$extra}
                </li>
            @endif
        @endif";

    // Regex to match the old block
    // We look for @if(in_array('FEATURE', ... ) until @endif
    $pattern = '/@if\(\s*in_array\(\'' . $f . '\'(?:.*?\))(?:\s*\|\|\s*.*?\))*\s*\)[\s\S]*?@endif/u';

    // For SMTP edit, there are two inside one @if block!
    // Wait, the original code has smtp_edit and email-templates inside ONE @if block:
    /*
                    @if(in_array('smtp_edit', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $premium)))
                        <li>...SMTP...</li>
                        <li>...Email Templates...</li>
                    @endif
    */
    // We should just write a custom script that replaces the whole file cleanly, since regex might mess up.
}
