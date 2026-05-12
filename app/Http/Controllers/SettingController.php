<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController; 
use Illuminate\Support\Facades\Crypt;

use App\Models\Companies;
use App\Models\Roles;
use App\Models\SmtpSettings;
use App\Models\EmailTemplate;

class SettingController extends Controller
{
    function roleSettings(){
        
        $roles = Roles::leftjoin('companies','roles.cid','=','companies.id')
            ->select('companies.name','roles.*')
            ->get();
        
        return view('roleSettings',['roles'=>$roles]);
    }
    function manageRoleSettings(Request $request){
        
        $roles = Roles::leftjoin('companies','roles.cid','=','companies.id')
            ->select('companies.name','roles.*')
            ->where('roles.id','=',($request->id ?? ''))->first();
        
        return view('manageRole',['roles'=>$roles]);
    }
    function manageRoleSettingsPost(Request $request){
        
        // Extracting and processing permissions
        $permissions = $request->input('permissions', []); // Default to empty array if none are selected

        $featurePermissions = [];
        foreach ($permissions as $feature => $actions) {
            foreach ($actions as $action) {
                $featurePermissions[] = "{$feature}_{$action}";
            }
        }
        
        $features = implode(',', array_keys($permissions));
        
        $access = implode(',', $featurePermissions);
        
        if(empty($request->id)){
            
            $roleSettings = new Roles();
            
            $roleSettings->title = ($request->role ?? '');
            $roleSettings->subtitle = ($request->subrole ?? '');
            $roleSettings->features = $features;
            $roleSettings->permissions = $access;
            $roleSettings->status = ($request->status ?? '');
            
            $roleSettings->save();
            
            return redirect('manage-role-setting')->with('success', 'New user role was successfully added.');
            
            return redirect('manage-role-setting')->with('error', 'Opps! Something has gone wrong.');
            
        }else{
            
            $id = $request->id ?? '';
            
            $roleSettings = Roles::find($id);
            
            $roleSettings->title = ($request->role ?? '');
            $roleSettings->subtitle = ($request->subrole ?? '');
            $roleSettings->features = $features;
            $roleSettings->permissions = $access;
            $roleSettings->status = ($request->status ?? '');
            
            $roleSettings->update();
            
            return redirect('manage-role-setting?id='.$id)->with('success', 'Successfully updated.');
            
            return redirect('manage-role-setting?id='.$id)->with('error', 'Opps! Something has gone wrong.');
            
        }
    }
    function smtpSetup(Request $request){
        
        $smtpsetup = SmtpSettings::where('user_id','=',(Auth::User()->id ?? ''))->first();
        
        return view('smtpSettings',['smtpsetup'=>$smtpsetup]);
    }
    public function smtpSetupPost(Request $request)
    {
        $validated = $request->validate([
            'mailer' => 'required',
            'host' => 'required',
            'port' => 'required|integer',
            'username' => 'required',
            'password' => 'required',
            'encryption' => 'nullable',
            'from_address' => 'required|email',
            'from_name' => 'required',
        ]);
        
         if (isset($validated['password'])) {
             // Use encryptString for better type handling and suitability for DB text columns
            $validated['password'] = Crypt::encryptString($validated['password']);
        }
    
        SmtpSettings::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated + [
            'user_id' => Auth::id()
        ]
        );
    
        return redirect()->back()->with('success', 'SMTP settings saved successfully!');
    }

    public function smtpTest(Request $request)
    {
        $request->validate([
            'to' => 'required|email'
        ]);

        try {
            $baseService = new \App\Services\BaseService();
            $success = $baseService->sendMail($request->to, 'SMTP Configuration Test', 'emails.test', ['body' => 'Your SMTP configuration is working correctly!']);

            if ($success) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'message' => 'Check your SMTP credentials. The connection failed.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    public function index()
    {
        $templates = EmailTemplate::orderBy('module')->get();
        return view('email_templates', compact('templates'));
    }

    public function create()
    {
        return view('create_email_template');
    }

    public function store(Request $request)
    {
        $request->validate([
            'module' => 'required',
            'event' => 'required',
            'subject' => 'required',
            'body' => 'required'
        ]);

        EmailTemplate::create($request->all());
        return redirect()->route('email-templates.index')
            ->with('success', 'Template created successfully');
    }

    public function edit($id)
    {
        $template = EmailTemplate::findOrFail($id);
        return view('edit_email_template', compact('template'));
    }


    public function update(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);
        $template->update($request->all());

        return redirect()->route('email-templates.index')
            ->with('success', 'Template updated');
    }

    public function toggle($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $template->is_active = !$template->is_active;
        $template->save();

        return back();
    }
    public function inboxSetup(Request $request)
    {
        $inboxes = \App\Models\EmailInbox::where('cid', Auth::user()->cid)->get();
        return view('inboxSettings', ['inboxes' => $inboxes]);
    }

    public function inboxSetupPost(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'imap_host' => 'required',
            'imap_port' => 'required|integer',
            'imap_encryption' => 'required',
            'username' => 'required',
            'password' => 'required',
        ]);

        \App\Models\EmailInbox::updateOrCreate(
            ['email' => $validated['email'], 'cid' => Auth::user()->cid],
            $validated + ['cid' => Auth::user()->cid, 'user_id' => Auth::id()]
        );

        return redirect()->back()->with('success', 'Inbox configured successfully!');
    }

    public function inboxSync(Request $request)
    {
        $service = new \App\Services\EmailSyncService();
        $result = $service->syncAll(Auth::user()->cid);

        if (isset($result['success']) && !$result['success']) {
            return response()->json($result);
        }

        return response()->json(['success' => true, 'message' => 'Sync completed.']);
    }
}
