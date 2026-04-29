<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AuthController; 

use App\Models\Companies;
use App\Models\User;
use App\Models\Roles;
use App\Models\Attendances;
use App\Models\Holidays;
use App\Models\SubscriptionPlan;
use App\Models\Enquiry;
use Carbon\Carbon;

class UserController extends Controller
{
    protected $userService;

    public function __construct(\App\Services\UserService $userService)
    {
        $this->userService = $userService;
    }

    public function attendances(Request $request)
    {
        $user = Auth::user();
        $roles = session('roles');
        $isAdmin = $roles && $roles->title === 'Admin';
    
        $selectedUserId = $request->input('user_id');
        $range = $request->input('range', $isAdmin ? 'today' : '7days');

        $data = $this->userService->getAttendanceReport($user, $isAdmin, $selectedUserId, $range);

        return view('attendances', array_merge($data, [
            'isAdmin' => $isAdmin,
            'range' => $range,
            'selectedUserId' => $selectedUserId
        ]));
    }

    public function manageAttendance(Request $request)
    {
        $authUser = Auth::user();
        $roles    = session('roles');
        $isAdmin  = $roles && $roles->title === 'Admin';

        $id      = $request->input('id');
        $userId  = $request->input('user_id');
        $date    = $request->input('date');

        // Look up by primary key OR by user_id + date combination
        if ($id) {
            $att = Attendances::find($id);
        } elseif ($userId && $date) {
            $att = Attendances::where('user_id', $userId)->where('date', $date)->first();
        } else {
            $att = null;
        }

        // For admin: allow picking any user in their company
        $users = $isAdmin
            ? User::select('id', 'name')->get()
            : collect([$authUser]);

        $viewData = [
            'attendance'  => $att,
            'users'       => $users,
            'isAdmin'     => $isAdmin,
            'authUser'    => $authUser,
            'prefillUser' => $userId,
            'prefillDate' => $date,
        ];

        if ($request->has('ajax')) {
            return view('manageAttendanceForm', $viewData);
        }

        return view('manageAttendance', $viewData);
    }

    public function manageAttendancePost(Request $request)
    {
        $authUser = Auth::user();
        $roles    = session('roles');
        $isAdmin  = $roles && $roles->title === 'Admin';

        $request->validate([
            'user_id'   => 'required|integer',
            'date'      => 'required|date',
            'check_in'  => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'method'    => 'nullable|string|max:50',
            'status'    => 'required|string|max:20',
            'remarks'   => 'nullable|string|max:500',
        ]);

        // Non-admins can only save their own records
        $userId = $isAdmin ? $request->input('user_id') : $authUser->id;

        Attendances::updateOrCreate(
            ['user_id' => $userId, 'date' => $request->input('date')],
            [
                'check_in'  => $request->input('check_in')  ?: null,
                'check_out' => $request->input('check_out') ?: null,
                'method'    => $request->input('method')    ?: null,
                'status'    => $request->input('status'),
                'remarks'   => $request->input('remarks')   ?: null,
            ]
        );

        return redirect('/attendances')->with('success', 'Attendance record saved successfully.');
    }

    //User Controller
    public function users(Request $request)
    {
        $segment = $request->segment(1);
        $user = Auth::user();

        $users = $this->userService->getUsersBySegment($user, $segment);
        // Note: The original code fetched roles but didn't pass them to the 'users' view. 
        // We'll keep the view call standard. If roles are needed in view later, add them here.
        
        return view('users', ['users' => $users]);
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id',
            'status' => 'required|integer|in:1,2'
        ]);

        $user = User::find($request->id);
        
        // Prevent disabling yourself
        if ($user->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You cannot disable your own account.'], 403);
        }

        $user->status = $request->status;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.',
            'new_status' => $user->status
        ]);
    }
    
    public function manageUser(Request $request)
    {
        $segment = $request->segment(1);
        $uid = ($segment == 'my-profile') ? Auth::id() : $request->id;
        
        $data = $this->userService->getUserDetails($uid, Auth::user());
        
        return view('manageUser', $data);
    }
    
    function manageUserPost(Request $request){
        
        $assign = implode(',',($request->assign ?? []));
        $features = implode(',',($request->features ?? []));
        
        if(empty($request->id)){
            
            $user = new User();
            
            $username = explode('@',$request->email);
            
            $user->username = $username[0].substr($request->mob,0,3);
            $user->name = ($request->name ?? '');
            $user->email = ($request->email ?? '');
            $user->mob = ($request->mob ?? '');
            if(!empty($request->password)){
            $user->password = Hash::make($request->password);
            }
            
            if(!empty($request->file('profilePhoto'))):
                
                // $request->validate([
                //     'image' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
                // ]);
                $fileName = time().".".$request->profilePhoto->extension();
                $request->profilePhoto->move(public_path("/assets/images/profile"), $fileName);

            endif;

            $user->photo = $fileName ?? '';
            
            if(!empty($request->file('imgsign'))):
                
                // $request->validate([
                //     'image' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
                // ]);
                $fileName1 = time().".".$request->imgsign->extension();
                $request->imgsign->move(public_path("/assets/images/signs"), $fileName1);
                
                $user->imgsign = $fileName1 ?? '';

            endif;
            
            $user->role = ($request->role ?? '');
            $user->assign = $assign;
            $user->working_times = json_encode($request->time ?? []);
            $user->features = $features;
            $user->esign = ($request->emailSign ?? '');
            $user->status = ($request->status ?? '');
            
            $user->save();
            
            return redirect('manage-user')->with('success', 'New user role was successfully added.');
            
        }else{
            
            $id = $request->id ?? '';
            
            $user = User::find($id);
            
            $user->name = ($request->name ?? '');
            $user->email = ($request->email ?? '');
            $user->mob = ($request->mob ?? '');
            if(!empty($request->password)){
                $user->password = Hash::make($request->password);
            }
            
            if(!empty($request->file('profilePhoto'))):
                
                // $request->validate([
                //     'image' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
                // ]);
                $fileName = time().".".$request->profilePhoto->extension();
                $request->profilePhoto->move(public_path("/assets/images/profile"), $fileName);
                
                $user->photo = $fileName ?? '';

            endif;
            
            if(!empty($request->file('imgsign'))):
                
                // $request->validate([
                //     'image' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
                // ]);
                $fileName1 = time().".".$request->imgsign->extension();
                $request->imgsign->move(public_path("/assets/images/signs"), $fileName1);
                
                $user->imgsign = $fileName1 ?? '';

            endif;
            
            if(!empty($request->role)){
                $user->role = ($request->role ?? '');
            }
            
            $user->assign = $assign;
            $user->working_times = json_encode($request->time ?? []);
            $user->features = $features;
            $user->esign = ($request->emailSign ?? '');
            $user->status = ($request->status ?? '');
            
            $user->update();
            
            if(!empty($request->file('companyLogo'))):
                
                $company = Companies::find(Auth::user()->cid);
                
                // $request->validate([
                //     'image' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
                // ]);
                $fileName = time().".".$request->companyLogo->extension();
                $request->companyLogo->move(public_path("/assets/images/company"), $fileName);
                
                $company->img = $fileName ?? '';
                
                $company->update();

            endif;
            
            return back()->with('success', 'Successfully updated.');
        }
        
    }
    
    public function subscriptions(Request $request)
    {
        if (Auth::user()->role != 'master') {
            return abort(403);
        }

        $companies = Companies::all();
        $plans = SubscriptionPlan::all();

        if ($plans->isEmpty()) {
            // Seed defaults if empty
            $defaults = [
                ['name' => 'Standard', 'price' => 0.00, 'description' => 'A basic plan for essential operations.'],
                ['name' => 'Premium', 'price' => 29.99, 'description' => 'Advanced features for growing teams.'],
                ['name' => 'Pro', 'price' => 99.99, 'description' => 'Professional-grade control and analytics.']
            ];
            foreach ($defaults as $d) {
                SubscriptionPlan::create($d);
            }
            $plans = SubscriptionPlan::all();
        }

        // Calculate Plan Stats based on assigned plans in companies
        $stats = [
            'total' => $companies->count(),
            'standard' => $companies->where('plan', 'standard')->count(),
            'premium' => $companies->where('plan', 'premium')->count(),
            'pro' => $companies->where('plan', 'pro')->count(),
        ];

        return view('subscriptions', [
            'companies' => $companies,
            'plans' => $plans,
            'stats' => $stats
        ]);
    }

    public function managePlan(Request $request)
    {
        $id = $request->id ?? '';
        $plan = SubscriptionPlan::find($id);
        
        return view('managePlanForm', ['plan' => $plan]);
    }

    public function managePlanPost(Request $request)
    {
        $id = $request->id ?? '';
        $plan = SubscriptionPlan::updateOrCreate(
            ['id' => $id],
            [
                'name' => $request->name,
                'price' => $request->price,
                'description' => $request->description,
                'features' => $request->features // Assuming JSON input for now, adjust if needed
            ]
        );

        return back()->with('success', 'Subscription Plan successfully updated.');
    }

    public function deletePlan(Request $request)
    {
        $id = $request->id ?? '';
        SubscriptionPlan::find($id)?->delete();
        return back()->with('success', 'Plan successfully removed.');
    }

    //Company Controller
    function companies(Request $request){
        $status = $request->input('status');
        $query = Companies::query();
        if($status !== null && $status !== ''){
            $query->where('status', $status);
        }
        $companies = $query->get();
        $plans = SubscriptionPlan::all();
        
        return view('companies', [
            'companies' => $companies,
            'plans' => $plans,
            'status' => $status
        ]);
    }
    function manageCompany(Request $request){
        
        $segment = $request->segment(1);
        
        $cid = ($segment == 'my-company') ? Auth::user()->cid : ($request->id ?? '');
        
        $companies = Companies::where('id','=',$cid)->first();
        $plans = SubscriptionPlan::all();
        
        $viewData = [
            'company' => $companies,
            'plans' => $plans
        ];

        if ($request->has('ajax')) {
            return view('manageCompanyForm', $viewData);
        }
        
        return view('manageCompany', $viewData);
    }
    function viewCompany(Request $request){
        $cid = $request->id ?? '';
        $company = Companies::find($cid);
        if (!$company) {
            return response('<div class="p-5 text-center text-danger">Company not found.</div>', 404);
        }
        return view('viewCompanyForm', ['company' => $company]);
    }
    public function manageCompanyPost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mob' => 'nullable|string|max:15',
            'gst' => 'nullable|string|max:20',
            'vat' => 'nullable|string|max:20',
            'tax_rates' => 'nullable|array',
            'tax_rates.*' => 'numeric',
            'bank_details' => 'nullable|array',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zipcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'subscription' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $taxRates = implode(',', $request->tax_rates ?? []);
        $bankDetails = json_encode($request->bank_details ?? []);
        
        $segment = $request->segment(1);
        //dd($segment);
        $id = !empty($request->id) ? ($request->id ?? '') : (Auth::user()->cid ?? '');
        
        //dd($id);
        
        if (empty($id)) {
            $company = new Companies();
        } else {
            $company = Companies::find($id);
            if (!$company) {
                return back()->with('error', 'Company not found.');
            }
        }
        
        $company->name = $request->name;
        $company->email = $request->email;
        $company->mob = $request->mob;
        $company->gst = $request->gst;
        $company->vat = $request->vat;
        $company->tax = $taxRates;
        $company->bank_details = $bankDetails;
        $company->address = $request->address;
        $company->city = $request->city;
        $company->state = $request->state;
        $company->zipcode = $request->zipcode;
        $company->country = $request->country;
        if(!empty($request->id)){
        $company->plan = $request->subscription ?? 'standard';
        }
        if ($request->hasFile('logo')) {
            $fileName = time().'.'.$request->logo->extension();
            $request->logo->move(public_path('/assets/images/company/logos'), $fileName);
            $company->logo = $fileName;
        }
        if ($request->hasFile('img')) {
            $fileName = time().'.'.$request->img->extension();
            $request->img->move(public_path('/assets/images/company'), $fileName);
            $company->img = $fileName;
        }
        
        $company->save();
        
        return back()->with('success', 'Company details successfully saved.');
    }
    
    //Reset Password Controller
    function resetPassword(){
        return view('resetPassword');
    }
    function resetPasswordPost(Request $request){
        
        $id = Auth::user()->id ?? '';
            
        $user = User::find($id);
        $user->password = Hash::make($request->cn_password);
        
        $user->update();
        
        return redirect('reset-password')->with('success', 'Successfully updated.');
    }

    // Enquiry Management
    public function enquiries(Request $request)
    {
        if (Auth::user()->role != 'master') {
            return abort(403);
        }

        $enquiries = Enquiry::orderBy('created_at', 'DESC')->get();
        
        $stats = [
            'total'     => $enquiries->count(),
            'new'       => $enquiries->where('status', 0)->count(),
            'contacted' => $enquiries->where('status', 1)->count(),
            'closed'    => $enquiries->where('status', 2)->count(),
        ];

        return view('enquiries', [
            'enquiries' => $enquiries,
            'stats'     => $stats
        ]);
    }

    public function manageEnquiry(Request $request)
    {
        $id = $request->id ?? '';
        $enquiry = Enquiry::find($id);
        
        return view('manageEnquiryForm', ['enquiry' => $enquiry]);
    }

    public function manageEnquiryPost(Request $request)
    {
        $id = $request->id ?? '';
        
        $enquiry = Enquiry::updateOrCreate(
            ['id' => $id],
            [
                'name'    => $request->name,
                'email'   => $request->email,
                'mob'     => $request->mob,
                'subject' => $request->subject,
                'message' => $request->message,
                'status'  => $request->status ?? 0
            ]
        );

        return back()->with('success', 'Enquiry successfully updated.');
    }

    public function deleteEnquiry(Request $request)
    {
        $id = $request->id ?? '';
        Enquiry::find($id)?->delete();
        return back()->with('success', 'Enquiry successfully removed.');
    }
}
