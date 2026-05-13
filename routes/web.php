<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AjaxController;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\NewLeadController;
use App\Http\Controllers\SchedulerTestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*Proposal Actions*/
Route::get('/proposal/{id}/{token}', [LeadController::class, 'proposal']);
Route::get('/proposal/{id}/{token}/download', [LeadController::class, 'downloadPdf'])->name('proposal.download');
Route::post('/proposal/{id}/{token}/accept', [LeadController::class, 'acceptProposal'])->name('proposal.accept');
Route::post('/proposal/{id}/{token}/decline', [LeadController::class, 'declineProposal'])->name('proposal.decline');


Route::post('/send', [HomeController::class, 'send']);

Route::group(['middleware' => 'guest'], function () {
    Route::get('/', [HomeController::class, 'redirectLogin'])->name('redirectLogin');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerPost'])->name('register');
    Route::get('/verify-email', [AuthController::class, 'verifyEmail'])->name('verifyEmail');
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginPost'])->name('login');
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPasswordPost'])->name('forgotPassword');

    Route::get('/new-password', [AuthController::class, 'newPassword'])->name('newPassword');
    Route::post('/new-password', [AuthController::class, 'newPasswordPost'])->name('newPassword');

    Route::get('/export-lead-all', [LeadController::class, 'exportAllLeads'])->name('exportAllLeads');
    // NOTE: /reminders is also registered inside the auth group (for logged-in use)
    // This one serves as a cron/scheduler hook (unauthenticated scheduler calls)
    Route::get('/reminders', [LeadController::class, 'reminderScript'])->name('reminderScript');
    Route::post('/enquiry-submit', [AjaxController::class, 'storeEnquiry'])->name('enquiry.submit');

    /* Email Tracking */
    Route::get('/t/o/{token}', [\App\Http\Controllers\EmailTrackingController::class, 'trackOpen'])->name('email.track_open');
    Route::get('/t/c/{token}', [\App\Http\Controllers\EmailTrackingController::class, 'trackClick'])->name('email.track_click');
});

Route::group(['middleware' => ['auth', 'checkplan']], function () {
    Route::get('/home', [HomeController::class, 'home']);

    // Routes for managing todo list items
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index']);
    Route::get('/reports/email-tracking', [\App\Http\Controllers\ReportController::class, 'emailTracking']);
    Route::get('/todo-lists', [TaskController::class, 'index']); // Fetch all tasks
    Route::post('/manage-todolist-item', [TaskController::class, 'store']); // Create new task
    Route::put('/manage-todolist-item/{id}', [TaskController::class, 'update']); // Update task completion
    Route::post('/todo-lists/reorder', [TaskController::class, 'reorder']); // Reorder tasks
    Route::delete('/manage-todolist-item/{id}', [TaskController::class, 'destroy']); // Delete a task
    Route::delete('/manage-todolist-item/clear', [TaskController::class, 'clearAll']); // Clear all tasks
    Route::post('/save-token', [TaskController::class, 'saveToken']); // Save FCM token

    Route::get('/firebase-messaging-sw.js', function () {
        $content = "importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js');
    importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js');
    
    firebase.initializeApp({
        apiKey: '" . env('FIREBASE_API_KEY') . "',
        authDomain: '" . env('FIREBASE_AUTH_DOMAIN') . "',
        projectId: '" . env('FIREBASE_PROJECT_ID') . "',
        storageBucket: '" . env('FIREBASE_STORAGE_BUCKET') . "',
        messagingSenderId: '" . env('FIREBASE_MESSAGING_SENDER_ID') . "',
        appId: '" . env('FIREBASE_APP_ID') . "',
        measurementId: '" . env('FIREBASE_MEASUREMENT_ID') . "'
    });
    
    const messaging = firebase.messaging();
    
    messaging.onBackgroundMessage(function(payload) {
        console.log('[firebase-messaging-sw.js] Received background message ', payload);
        const notificationTitle = payload.notification.title;
        const notificationOptions = {
            body: payload.notification.body,
            icon: '/favicon.ico'
        };
    
        self.registration.showNotification(notificationTitle,
            notificationOptions);
    });";
        return response($content)->header('Content-Type', 'application/javascript');
    });



    /*Task Managment Router*/
    Route::get('/task', [TaskController::class, 'task']);
    Route::post('/task', [TaskController::class, 'taskPost'])->name('task');
    Route::get('/edit-task', [TaskController::class, 'taskEdit'])->name('edit-task');

    Route::controller(TaskController::class)->group(function () {
        Route::match(['get', 'post'], '/tasksubmit', 'tasksubmit')->name('tasksubmit');
        Route::get('/task-details/{id}', 'getTaskDetailsAjax')->name('task.details.ajax');
        Route::get('/task-details/{id}', 'getTaskDetailsAjax')->name('task.details.ajax');
        Route::post('/task-attachment/upload', 'uploadAttachment')->name('task.attachment.upload');
        Route::delete('/task-attachment/{id}', 'deleteAttachment')->name('task.attachment.delete');
        Route::post('/task-meta/update', 'updateTaskMeta')->name('task.meta.update');
    });




    /*Leads Management Router*/
    Route::get('/leads', [LeadController::class, 'leads'])->middleware('permission:leads,assign');
    Route::get('/leads/kanban', [\App\Http\Controllers\LeadUIController::class, 'kanbanView'])->name('leads.kanban')->middleware('permission:leads,assign');
    Route::get('/leads/kanban-data', [\App\Http\Controllers\LeadUIController::class, 'kanbanData'])->name('leads.kanban_data');
    Route::get('/get-lead-industries', [\App\Http\Controllers\LeadUIController::class, 'getLeadIndustries']);
    Route::post('/leads/update-status', [\App\Http\Controllers\LeadUIController::class, 'updateStatus'])->name('leads.update_status')->middleware('permission:leads,edit');
    Route::get('/view-single-lead', [LeadController::class, 'singleLeadsGet'])->name('singleLead');

    /* Assign Leads Router*/
    Route::get('/leads-list', [LeadController::class, 'leadList']);
    Route::post('/leads', [LeadController::class, 'leadsPost'])->name('leads')->middleware('permission:leads,assign');

    Route::get('/newleads', [NewLeadController::class, 'newleads'])->name('leads.index')->middleware('permission:leads,assign');
    Route::post('/bulk-assign-leads', [NewLeadController::class, 'bulkAssign'])->name('leads.bulkAssign')->middleware('permission:leads,assign');

    Route::get('/get-lead-details/{id}', [NewLeadController::class, 'getLeadDetails']);
    Route::post('/leads/update-profile', [NewLeadController::class, 'updateLead'])->name('leads.update')->middleware('permission:leads,edit');
    Route::post('/leads/store-comment', [NewLeadController::class, 'storeComment'])->name('leads.storeComment');
    Route::post('/delete-lead', [NewLeadController::class, 'deleteLead'])->name('leads.delete')->middleware('permission:leads,delete');

    /*Manage Lead Data*/
    Route::get('/manage-lead', [LeadController::class, 'manageLead'])->name('manageLead')->middleware('permission:leads,add');
    Route::post('/manage-lead', [LeadController::class, 'manageLeadPost'])->name('manageLead')->middleware('permission:leads,add');

    /*Import & export Leads Data Router*/
    Route::post('/import-leads-file', [LeadController::class, 'importLeads'])->name('importLeads')->middleware('permission:leads,import');
    Route::get('/export-leads-file', [LeadController::class, 'exportLeads'])->name('exportLeads')->middleware('permission:leads,export');

    /*Leads Comments Management Router*/
    Route::get('/lead-comments', [LeadController::class, 'leadComments']);
    Route::get('/manage-lead-comment', [LeadController::class, 'manageLeadComment'])->name('manageLeadComment');
    Route::get('/send-proposal-whatsapp/{id}', [LeadController::class, 'sendProposalWhatsApp'])->name('sendProposalWhatsApp')->middleware('permission:proposals,edit');
    Route::post('/manage-lead-comment', [LeadController::class, 'manageLeadCommentPost'])->name('manageLeadComment');



    /*Manage Proposal Router*/
    Route::get('/proposals', [LeadController::class, 'proposals'])->middleware('permission:proposals,assign');
    Route::get('/manage-proposal', [LeadController::class, 'manageProposal'])->name('manageProposal')->middleware('permission:proposals,add');
    Route::post('/manage-proposal', [LeadController::class, 'manageProposalPost'])->name('manageProposal')->middleware('permission:proposals,add');

    /* Sales Pipeline (Opportunities) */
    Route::get('/opportunities', [\App\Http\Controllers\OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/kanban-data', [\App\Http\Controllers\OpportunityController::class, 'kanbanData'])->name('opportunities.kanban_data');
    Route::post('/opportunities/store', [\App\Http\Controllers\OpportunityController::class, 'store'])->name('opportunities.store');
    Route::post('/opportunities/update-stage', [\App\Http\Controllers\OpportunityController::class, 'updateStage'])->name('opportunities.update_stage');

    /* CRM Follow-Up Tasks */
    Route::get('/crm-tasks', [\App\Http\Controllers\CrmTaskController::class, 'index'])->name('crm_tasks.index');
    Route::post('/crm-tasks/store', [\App\Http\Controllers\CrmTaskController::class, 'store'])->name('crm_tasks.store');
    Route::post('/crm-tasks/update-status', [\App\Http\Controllers\CrmTaskController::class, 'updateStatus'])->name('crm_tasks.update_status');

    /* CRM Reports & Analytics */
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');

    /* Automation Workflows */
    Route::get('/automations', [\App\Http\Controllers\AutomationController::class, 'index'])->name('automations.index')->middleware('permission:automations,assign');
    Route::post('/automations/store', [\App\Http\Controllers\AutomationController::class, 'store'])->name('automations.store')->middleware('permission:automations,add');
    Route::post('/automations/toggle-status', [\App\Http\Controllers\AutomationController::class, 'toggleStatus'])->name('automations.toggle_status')->middleware('permission:automations,edit');

    /* Marketing Campaigns */
    Route::get('/campaigns', [\App\Http\Controllers\CampaignController::class, 'index'])->name('campaigns.index')->middleware('permission:campaigns,assign');
    Route::post('/campaigns/store', [\App\Http\Controllers\CampaignController::class, 'store'])->name('campaigns.store')->middleware('permission:campaigns,add');
    Route::post('/campaigns/launch', [\App\Http\Controllers\CampaignController::class, 'launch'])->name('campaigns.launch')->middleware('permission:campaigns,edit');
    Route::delete('/campaigns/{id}', [\App\Http\Controllers\CampaignController::class, 'destroy'])->name('campaigns.destroy')->middleware('permission:campaigns,delete');


    /*Proposal Actions*/
    Route::get('/quotation/{id}/{token}', [LeadController::class, 'proposal']);
    Route::get('/quotation/{id}/{token}/download', [LeadController::class, 'downloadPdf'])->name('proposal.download');
    Route::post('/quotation/{id}/{token}/accept', [LeadController::class, 'acceptProposal'])->name('proposal.accept');
    Route::post('/quotation/{id}/{token}/decline', [LeadController::class, 'declineProposal'])->name('proposal.decline');


    /*Clients Management Router*/
    Route::get('/clients', [ClientController::class, 'clients'])->middleware('permission:clients,assign');
    Route::get('/get-client/{clientId}', [ClientController::class, 'getClient']);
    Route::get('/clients-list', [ClientController::class, 'clientList']);
    Route::post('/clients', [ClientController::class, 'clientsPost'])->name('clients');
    Route::post('/clients/toggle-status', [ClientController::class, 'toggleClientStatus'])->name('clients.toggle_status');
    Route::get('/view-single-client', [ClientController::class, 'singleClientGet'])->name('singleClient');
    Route::get('/manage-client', [ClientController::class, 'manageClient'])->name('manageClient')->middleware('permission:clients,edit');
    Route::post('/manage-client', [ClientController::class, 'manageClientPost'])->name('manageClient')->middleware('permission:clients,edit');

    /* Product & Catalog Management */
    Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
    Route::post('/products/store', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
    Route::post('/products/update/{id}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/delete/{id}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/products/get/{id}', [\App\Http\Controllers\ProductController::class, 'getProductAjax'])->name('products.get');

    /* Customer 360 View */
    Route::get('/customer-360/{type}/{id}', [\App\Http\Controllers\Customer360Controller::class, 'view'])->name('customer.360');
    Route::post('/initiate-call', [\App\Http\Controllers\AjaxController::class, 'initiateExotelCall'])->name('call.initiate');
    Route::post('/manage-client/interaction', [ClientController::class, 'storeInteraction'])->name('clients.interaction');

    /* Third-Party Integrations */
    Route::get('/integrations', [\App\Http\Controllers\IntegrationsController::class, 'index'])->name('integrations.index');
    Route::post('/integrations', [\App\Http\Controllers\IntegrationsController::class, 'store'])->name('integrations.store');

    /*Client Comments Management Router*/
    Route::get('/client-comments', [LeadController::class, 'clientComments']);
    Route::get('/manage-client-comment', [LeadController::class, 'manageClientComment'])->name('manageClientComment');
    Route::post('/manage-client-comment', [LeadController::class, 'manageClientCommentPost'])->name('manageClientComment');



    /*Recoveries's Account Management Router*/
    Route::get('/recoveries', [ClientController::class, 'recoveries'])->middleware('permission:recoveries,assign');
    Route::get('/manage-recovery', [ClientController::class, 'manageRecovery'])->name('manageRecovery')->middleware('permission:recoveries,add');
    Route::post('/manage-recovery', [ClientController::class, 'manageRecoveryPost'])->name('manageRecovery')->middleware('permission:recoveries,add');
    Route::get('/recovery/{id}/{title}', [ClientController::class, 'recovery'])->name('recovery');
    Route::post('/recovery', [ClientController::class, 'recoveryPost'])->name('recovery')->middleware('permission:recoveries,edit');
    Route::get('/update-recovery-amount', [ClientController::class, 'updateRecoveryAmount'])->name('recovery')->middleware('permission:recoveries,edit');
    Route::get('/delete-recovery-amount', [AjaxController::class, 'ajaxSend'])->middleware('permission:recoveries,delete');
    Route::get('/delete-recovery-project', [AjaxController::class, 'ajaxSend'])->middleware('permission:recoveries,delete');



    /*Project's Account Management Router*/
    Route::get('/projects', [ClientController::class, 'projects'])->middleware('permission:projects,assign');
    Route::get('/project/view/{id}', [ClientController::class, 'viewProject'])->name('project.view');
    Route::get('/get-projects/{clientId}', [ClientController::class, 'getProjects']);
    Route::get('/view-single-project', [ClientController::class, 'singleProjectGet'])->name('singleProject');
    Route::get('/manage-project', [ClientController::class, 'manageProject'])->name('manageProject')->middleware('permission:projects,add');
    Route::post('/manage-project', [ClientController::class, 'manageProjectPost'])->name('manageProject')->middleware('permission:projects,add');



    /*Contract's Account Management Router*/
    Route::get('/contracts', [ClientController::class, 'contracts'])->middleware('permission:contracts,assign');
    Route::get('/manage-contract', [ClientController::class, 'manageContract'])->name('manageContract')->middleware('permission:contracts,add');
    Route::post('/manage-contract', [ClientController::class, 'manageContractPost'])->name('manageContract')->middleware('permission:contracts,add');



    /*Manage Licensing Router*/
    Route::get('/licensing', [ClientController::class, 'licensing']);
    Route::get('/manage-license', [ClientController::class, 'manageLicense'])->name('manageLicense');
    Route::post('/manage-license', [ClientController::class, 'manageLicensePost'])->name('manageLicense');



    /*Invoice's Router*/
    Route::get('/invoices', [ClientController::class, 'invoices'])->middleware('permission:invoice,assign');
    Route::get('/invoices/preview/{id}', [ClientController::class, 'invoicePreview'])->name('invoicePreview');
    Route::get('/invoices/pdf/preview/{id}', [ClientController::class, 'invoicePdfPreview'])->name('invoicePdfPreview');
    Route::get('/invoices/download/{id}', [ClientController::class, 'invoiceDownload'])->name('invoiceDownload')->middleware('permission:invoice,export');
    Route::get('/manage-invoice', [ClientController::class, 'manageInvoice'])->name('manageInvoice')->middleware('permission:invoice,add');
    Route::post('/manage-invoice', [ClientController::class, 'manageInvoicePost'])->name('manageInvoice')->middleware('permission:invoice,add');
    Route::post('/manage-invoice-client', [ClientController::class, 'manageInvoiceClientPost'])->middleware('permission:invoice,add');



    /*User's Attendances Management Router*/
    Route::get('/attendances', [UserController::class, 'attendances'])->middleware('permission:attendances,assign');
    Route::get('/manage-attendance', [UserController::class, 'manageAttendance'])->name('manageAttendance')->middleware('permission:attendances,add');
    Route::post('/manage-attendance', [UserController::class, 'manageAttendancePost'])->name('manageAttendance')->middleware('permission:attendances,add');



    /*User's Account Management Router*/
    Route::get('/users', [UserController::class, 'users'])->middleware('permission:users,assign');
    Route::get('/manage-user', [UserController::class, 'manageUser'])->name('manageUser')->middleware('permission:users,add');
    Route::post('/manage-user', [UserController::class, 'manageUserPost'])->name('manageUser')->middleware('permission:users,add');
    Route::post('/users/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle_status');



    /*Companies Management Router*/
    Route::get('/companies', [UserController::class, 'companies']);
    Route::get('/manage-company', [UserController::class, 'manageCompany'])->name('manageCompany');
    Route::get('/view-company', [UserController::class, 'viewCompany'])->name('viewCompany');
    Route::get('/subscriptions', [UserController::class, 'subscriptions']);
    Route::get('/manage-plan', [UserController::class, 'managePlan']);
    Route::post('/manage-plan', [UserController::class, 'managePlanPost'])->name('managePlan');
    Route::get('/delete-plan', [UserController::class, 'deletePlan']);

    /* Enquiry Management Router */
    Route::get('/enquiries', [UserController::class, 'enquiries'])->name('enquiries');
    Route::get('/manage-enquiry', [UserController::class, 'manageEnquiry']);
    Route::post('/manage-enquiry', [UserController::class, 'manageEnquiryPost']);
    Route::get('/delete-enquiry', [UserController::class, 'deleteEnquiry']);

    // Support Routes
    Route::get('/support', [App\Http\Controllers\SupportController::class, 'index'])->name('support')->middleware('permission:support,assign');
    Route::get('/manage-support', [App\Http\Controllers\SupportController::class, 'manageSupport'])->middleware('permission:support,add');
    Route::post('/manage-support', [App\Http\Controllers\SupportController::class, 'storeSupport'])->middleware('permission:support,add');
    Route::get('/delete-support', [App\Http\Controllers\SupportController::class, 'deleteSupport'])->middleware('permission:support,delete');

    Route::post('/manage-company', [UserController::class, 'manageCompanyPost'])->name('manageCompany');

    /*Admin's Account Management Router*/
    Route::get('/admins', [UserController::class, 'users']);
    Route::get('/manage-admin', [UserController::class, 'manageUser'])->name('manageUser');
    Route::post('/manage-admin', [UserController::class, 'manageUserPost'])->name('manageUser');

    /*Employee's Account Management Router*/
    Route::get('/employees', [UserController::class, 'users']);
    Route::get('/manage-employee', [UserController::class, 'manageUser'])->name('manageUser');
    Route::post('/manage-employee', [UserController::class, 'manageUserPost'])->name('manageUser');

    /*My Profile Management Router*/
    Route::get('/my-profile', [UserController::class, 'manageUser']);
    Route::post('/my-profile', [UserController::class, 'manageUserPost'])->name('manageUser');

    /*My Company Profile Management Router*/
    Route::get('/my-company', [UserController::class, 'manageCompany'])->middleware('permission:company,edit');
    Route::post('/my-company', [UserController::class, 'manageCompanyPost'])->name('manageCompany')->middleware('permission:company,edit');

    Route::get('/reset-password', [UserController::class, 'resetPassword']);
    Route::post('/reset-password', [UserController::class, 'resetPasswordPost'])->name('resetPassword');

    /*User's Role Management Router*/
    Route::get('/role-settings', [SettingController::class, 'roleSettings'])->middleware('permission:settings,edit');
    Route::get('/manage-role-setting', [SettingController::class, 'manageRoleSettings'])->name('manageRoleSettings')->middleware('permission:settings,edit');
    Route::post('/manage-role-setting', [SettingController::class, 'manageRoleSettingsPost'])->name('manageRoleSettings')->middleware('permission:settings,edit');

    Route::resource('email-templates', SettingController::class);
    Route::post('email-templates/{id}/toggle', [SettingController::class, 'toggle'])
        ->name('email-templates.toggle');

    /*── Permission-Protected Delete Routes ──*/
    Route::get('/delete-project', [AjaxController::class, 'ajaxSend'])->middleware('permission:projects,delete');
    Route::get('/delete-invoice', [AjaxController::class, 'ajaxSend'])->middleware('permission:invoice,delete');
    Route::get('/delete-proposal', [AjaxController::class, 'ajaxSend'])->middleware('permission:proposals,delete');
    Route::get('/delete-client', [AjaxController::class, 'ajaxSend'])->middleware('permission:clients,delete');
    Route::get('/delete-contract', [AjaxController::class, 'ajaxSend'])->middleware('permission:contracts,delete');
    Route::get('/delete-user', [AjaxController::class, 'ajaxSend'])->middleware('permission:users,delete');
    Route::get('/delete-lead-ajax', [AjaxController::class, 'ajaxSend'])->middleware('permission:leads,delete');
    Route::get('/delete-attendance', [AjaxController::class, 'ajaxSend'])->middleware('permission:attendances,delete');

    Route::get('/ajax-send', [AjaxController::class, 'ajaxSend']);
    Route::get('/task-search', [AjaxController::class, 'taskSearch'])->name('taskSearch');
    Route::get('/global-search', [AjaxController::class, 'globalSearch'])->name('globalSearch');

    //SMTP Email Setup
    Route::get('/smtp-settings', [SettingController::class, 'smtpSetup'])->name('smtpSetup')->middleware('permission:smtp,edit');
    Route::post('/smtp-settings', [SettingController::class, 'smtpSetupPost'])->name('smtpSetup')->middleware('permission:smtp,edit');
    Route::post('/smtp-test', [SettingController::class, 'smtpTest'])->name('smtpTest')->middleware('permission:smtp,edit');

    //Inbox Setup (IMAP)
    Route::get('/inbox-settings', [SettingController::class, 'inboxSetup'])->name('inboxSetup')->middleware('permission:smtp,edit');
    Route::post('/inbox-settings', [SettingController::class, 'inboxSetupPost'])->name('inboxSetup')->middleware('permission:smtp,edit');
    Route::post('/inbox-sync', [SettingController::class, 'inboxSync'])->name('inboxSync')->middleware('permission:smtp,edit');

    //Notification Reminders
    Route::get('/reminders', [LeadController::class, 'reminderScript'])->name('reminderScript');
    Route::get('/trigger-url', [AuthController::class, 'triggerCurl']);

    Route::delete('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/signout', function () {
        Auth::logout();

        return redirect()->route('login');
    });
});

Route::get('/test-scheduler', [SchedulerTestController::class, 'run']);

Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:cache');
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('view:clear');
    $exitCode = Artisan::call('route:clear');

    // php artisan config:clear
    // php artisan config:cache
    // php artisan view:clear
    // php artisan route:clear

    return 'DONE';
});

Route::get('/debug-fcm', function (Request $request) {
    try {
        $diag = [
            'current_auth_user' => Auth::user() ? ['id' => Auth::user()->id, 'name' => Auth::user()->name] : 'Not Logged In',
            'database_status' => 'Testing...',
            'users_with_tokens' => []
        ];

        try {
            \DB::connection()->getPdo();
            $diag['database_status'] = 'Connected';
            $diag['users_with_tokens'] = \App\Models\User::whereNotNull('fcm_token')->get(['id', 'name', 'fcm_token'])->toArray();
        } catch (\Exception $e) {
            $diag['database_status'] = 'Failed: ' . $e->getMessage();
        }

        return response()->json($diag);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/debug-send-notif', function (Request $request) {
    try {
        $token = $request->query('token');
        $source = 'URL Parameter';

        if (!$token) {
            $user = \App\Models\User::whereNotNull('fcm_token')->latest()->first();
            $token = $user ? $user->fcm_token : null;
            $source = $user ? "Database (User ID: {$user->id}, Name: {$user->name})" : 'None found in DB';
        }

        if (!$token) {
            return response()->json(['status' => 'Error', 'message' => 'No token found', 'source' => $source]);
        }

        \Log::info("Attempting to send FCM to token: " . substr($token, 0, 20) . "... [Source: $source]");

        $factory = (new Factory)->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
        $messaging = $factory->createMessaging();

        $message = CloudMessage::fromArray([
            'token' => $token,
            'notification' => [
                'title' => 'Esecrm Test',
                'body' => 'Test notification from debug route at ' . now()->toDateTimeString(),
            ],
            'data' => [
                'click_action' => url('/home'),
                'test' => 'true'
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'default_channel'
                ]
            ],
            'webpush' => [
                'headers' => [
                    'Urgency' => 'high'
                ]
            ]
        ]);

        try {
            $report = $messaging->send($message);
            return response()->json([
                'status' => 'Success',
                'token_source' => $source,
                'token_used' => $token,
                'firebase_report' => $report
            ]);
        } catch (\Exception $e) {
            \Log::error("FCM Debug Send Error: " . $e->getMessage());
            return response()->json([
                'status' => 'Error',
                'message' => $e->getMessage(),
                'token_source' => $source,
                'token_used' => $token
            ]);
        }
    } catch (\Exception $e) {
        return response()->json(['status' => 'Fatal Error', 'message' => $e->getMessage()]);
    }
});

Route::get('/test-firebase', function () {
    try {
        $factory = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));

        $messaging = $factory->createMessaging();
        return "Firebase initialized successfully!";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
