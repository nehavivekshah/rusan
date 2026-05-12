<?php

namespace App\Http\Controllers;

// External Packages
use Carbon\Carbon;
use Illuminate\Http\Request;

// Laravel Facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// Application Specific Classes (Mail, Models, etc.)
use App\Mail\CustomMailable;
use App\Models\Companies;
use App\Models\Roles;
use App\Models\SmtpSettings;
use App\Models\User;
use App\Models\Eselicenses;
use App\Models\EmailVerificationToken;
use Illuminate\Support\Str;
use App\Traits\ActivityLogger;
use App\Services\LeadService;

class AuthController extends Controller
{
    use ActivityLogger;

    protected $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    public function register()
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        session(['captcha_answer' => $num1 + $num2]);

        return view('register', compact('num1', 'num2'));
    }

    public function registerPost(Request $request)
    {
        // --- Server-side Validation (Audit #2) ---
        $request->validate([
            'reg_name' => 'required|string|max:255',
            'reg_email' => 'required|email|unique:users,email',
            'reg_password' => 'required|min:8',
            'reg_mob' => 'required|string|max:15',
            'reg_company' => 'required|string|max:255',
            'reg_gst' => 'nullable|string|max:50',
            'captcha' => 'required',
        ]);

        // --- Captcha: now checked from session (Audit #10) ---
        if ($request->captcha != session('captcha_answer')) {
            return back()->withErrors(['captcha' => 'Incorrect captcha answer.'])->withInput();
        }

        try {
            // --- Wrap in DB transaction (Audit #14) ---
            $result = DB::transaction(function () use ($request) {
                $companies = new Companies();
                $companies->name = $request->reg_company ?? '';
                $companies->mob = $request->reg_mob ?? '';
                $companies->email = $request->reg_email ?? '';
                $companies->gst = $request->reg_gst ?? '';
                $companies->status = '1';
                $companies->save();

                $roles = new Roles();
                $roles->cid = $companies->id ?? '';
                $roles->title = 'Admin';
                $roles->subtitle = '';
                $roles->features = 'All';
                $roles->permissions = 'All';
                $roles->status = '1';
                $roles->save();

                $user = new User();
                $username = explode('@', $request->reg_email);
                $user->username = substr($request->reg_company, 0, 3) . $username[0];
                $user->name = $request->reg_name ?? '';
                $user->cid = $companies->id ?? '';
                $user->mob = $request->reg_mob ?? '';
                $user->email = $request->reg_email ?? '';
                $user->password = Hash::make($request->reg_password);
                $user->role = $roles->id ?? '';
                $user->status = 0; // Inactive until verified
                $user->save();

                // --- Hash verification token (Audit #6) ---
                $token = Str::random(64);
                EmailVerificationToken::create([
                    'user_id' => $user->id,
                    'token' => Hash::make($token)
                ]);

                return ['user' => $user, 'token' => $token];
            });

            $user = $result['user'];
            $token = $result['token'];
            $to = $user->email;

            $verifyUrl = url('/verify-email?token=' . $token . '&email=' . urlencode($user->email));

            // --- Removed plaintext password from email (Audit #1) ---
            $subject = 'Verify Your Email - Rusan';
            $message = "Dear " . $user->name . ",<br><br>
            Thank you for registering! Please verify your email address to activate your account.<br><br>
            <a href='" . $verifyUrl . "' style='padding: 10px 20px; background: #006666; color: #fff; text-decoration: none; border-radius: 5px;'>Verify Email Address</a><br><br>
            If the button above doesn't work, copy and paste this link into your browser:<br>" . $verifyUrl . "<br><br>
            Best regards,<br>Rusan Team";

            $viewName = 'emails.welcome';
            $viewData = ["name" => $user->name, "messages" => $message];

            try {
                $this->leadService->sendMail($to, $subject, $viewName, $viewData, $user->id, $user->cid);
            } catch (\Exception $e) {
                Log::error('Registration Email Failed: ' . $e->getMessage());
            }

            return redirect('/login')->with('success', 'Registration successful! Please check your email to verify your account before logging in.');

        } catch (\Throwable $e) {
            Log::error('Registration Error: ' . $e->getMessage());

            if (isset($e->errorInfo) && $e->errorInfo[1] == 1062) {
                return back()->with('error', 'Duplicate Entry. This email or mobile number is already registered.');
            }

            return back()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    public function login()
    {
        return view('login');
    }

    public function loginPost(Request $request)
    {
        // --- Server-side validation (Audit #3) ---
        $request->validate([
            'login_email' => 'required|email',
            'login_password' => 'required|string',
        ]);

        try {
            $credentials = [
                'email' => $request->login_email,
                'password' => $request->login_password,
            ];

            // --- Remember-me is now opt-in (Audit #24) ---
            $remember = $request->has('remember_me');

            if (Auth::attempt($credentials, $remember)) {

                // Get the authenticated user
                $user = Auth::user();

                // Check if the user account is active
                if ($user->status == 0) {
                    Auth::logout();
                    return back()->with('error', 'Your email address is not verified. Please check your inbox for the verification link.');
                }

                if ($user->status != 1) {
                    Auth::logout();
                    return back()->with('error', 'Your account has been deactivated. Please contact the support team for assistance.');
                }

                // Regenerate session to prevent fixation
                $request->session()->regenerate();

                // Retrieve related company and role information
                $company = Companies::find($user->cid);
                $role = Roles::find($user->role);

                // Store information in session
                session([
                    'companies' => $company,
                    'roles' => $role,
                ]);

                $this->logLogin();

                return redirect('/home')->with('success', 'Successfully logged in.');
            }

            return back()->with('error', 'Invalid login credentials.');

        } catch (\Throwable $e) {
            Log::error('Login Error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred during login. Please try again.');
        }
    }

    public function forgotPassword()
    {
        return view('forgotPassword');
    }

    public function forgotPasswordPost(Request $request)
    {
        $request->validate(['forgot_email' => 'required|email']);

        try {
            $to = $request->forgot_email;
            $getUser = User::where('email', '=', $to)->first();

            // --- Generic message to prevent user enumeration (Audit #13) ---
            if (!$getUser) {
                return back()->with('success', 'If an account exists with this email, a reset link has been sent.');
            }

            $getSociety = Companies::where('id', '=', $getUser->cid)->first();

            // Generate a secure random token and store it
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $to],
                ['email' => $to, 'token' => Hash::make($token), 'created_at' => now()]
            );

            $resetUrl = url('/new-password?token=' . $token . '&email=' . urlencode($to));

            $subject = 'Reset Your Password for Your CRM Account';

            $message = "Dear " . $getUser->name . ",<br><br>
            We received a request to reset your password for your CRM account. If you did not make this request, please ignore this email.<br><br>
            <b>Reset Your Password:</b><br>
            <ul>
                <li>Click on the following link: <a href='" . $resetUrl . "'>Password Reset Link</a></li>
                <li>Enter and confirm your new password.</li>
                <li>Click <b>Submit</b> to complete the process.</li>
            </ul><br>
            For your security, this link will expire in 24 hours.<br><br>
            <b>Best regards,</b><br>" . ($getSociety->name ?? 'Rusan');

            $viewName = 'emails.welcome';
            $viewData = ["name" => $getUser->name, "messages" => $message];

            $this->leadService->sendMail($to, $subject, $viewName, $viewData, $getUser->id, $getUser->cid);

            return back()->with('success', 'If an account exists with this email, a reset link has been sent.');
        } catch (\Throwable $e) {
            Log::error('Forgot Password Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to send reset link. Please try again later.');
        }
    }

    public function newPassword(Request $request)
    {
        $token = $request->token ?? '';
        $email = $request->email ?? '';

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        // --- Token expiry check: 24 hours (Audit #5) ---
        if (!$record || !Hash::check($token, $record->token)) {
            return redirect('/forgot-password')->with('error', 'This password reset link is invalid or has expired.');
        }

        if (Carbon::parse($record->created_at)->addHours(24)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect('/forgot-password')->with('error', 'This password reset link has expired. Please request a new one.');
        }

        $getUser = User::where('email', $email)->first();

        if (!$getUser) {
            return redirect('/forgot-password')->with('error', 'Invalid reset link.');
        }

        return view('newPassword', ['id' => $getUser->id, 'token' => $token, 'email' => $email]);
    }

    public function newPasswordPost(Request $request)
    {
        try {
            $request->validate([
                'new_password' => 'required|min:8',
                'token' => 'required|string',
                'email' => 'required|email',
            ]);

            // --- Re-validate token on POST to prevent direct uid manipulation (Audit #12) ---
            $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

            if (!$record || !Hash::check($request->token, $record->token)) {
                return redirect('/forgot-password')->with('error', 'Invalid or expired reset token.');
            }

            if (Carbon::parse($record->created_at)->addHours(24)->isPast()) {
                DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                return redirect('/forgot-password')->with('error', 'This reset link has expired. Please request a new one.');
            }

            $user = User::where('email', $request->email)->firstOrFail();
            $user->password = Hash::make($request->new_password);
            $user->update();

            // Invalidate the token
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            return redirect('login')->with('success', 'Your password has been successfully updated! You can now log in using your new password.');
        } catch (\Throwable $e) {
            Log::error('New Password Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update password. Please try again.');
        }
    }

    public function logout(Request $request)
    {
        // --- Log logout before clearing auth (Audit #32) ---
        $this->logLogout();

        // 1. Clear specific custom session data
        session()->forget('companies');
        session()->forget('roles');

        // 2. Log the user out from Laravel's authentication system
        Auth::logout();

        // 3. Invalidate the user's session.
        $request->session()->invalidate();

        // 4. Regenerate the CSRF token.
        $request->session()->regenerateToken();

        // 5. Redirect the user to the login page
        return redirect('/login')
            ->with('info', 'You have been successfully logged out.');
    }


        // Data to send with the request 
        $data = [
            'status' => $request->status ?? '',  // Action to export database
            'token' => $ese->eselicense_key ?? '',
        ];

        // Make cURL POST request
        $response = $this->sendCurlRequest($url, $data);

        // Handle response
        return response()->json($response);
    }

    public function verifyEmail(Request $request)
    {
        try {
            $token = $request->token;
            $email = $request->email;

            // --- Match hashed token (Audit #6) + expiry check (Audit #7) ---
            $verifyTokens = EmailVerificationToken::whereHas('user', function ($q) use ($email) {
                $q->where('email', $email);
            })->get();

            $verifyToken = null;
            foreach ($verifyTokens as $record) {
                if (Hash::check($token, $record->token)) {
                    $verifyToken = $record;
                    break;
                }
            }

            if (!$verifyToken) {
                return redirect('/login')->with('error', 'Invalid or expired verification link.');
            }

            // --- Token expiry: 48 hours (Audit #7) ---
            if ($verifyToken->created_at->addHours(48)->isPast()) {
                $verifyToken->delete();
                return redirect('/login')->with('error', 'Verification link has expired. Please register again.');
            }

            $user = User::where('id', $verifyToken->user_id)->where('email', $email)->first();

            if (!$user) {
                return redirect('/login')->with('error', 'User not found.');
            }

            // Activate User
            $user->status = 1;
            $user->email_verified_at = now();
            $user->update();

            // Delete Token
            $verifyToken->delete();

            return redirect('/login')->with('success', 'Email verified successfully! You can now log in.');
        } catch (\Throwable $e) {
            Log::error('Verify Email Error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Verification failed. Please try again.');
        }
    }

    private function sendCurlRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response
        return json_decode($response, true);
    }
}
