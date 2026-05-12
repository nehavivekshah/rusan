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
        return view('register');
    }

    public function registerPost(Request $request)
    {

        if ($request->captcha != $request->captcha_answer) {
            return back()->withErrors(['captcha' => 'Incorrect captcha answer.']);
        }

        try {

            $to = $request->reg_email ?? '';
            $subject = 'Welcome to Our Rusan!';

            $message = "Thank you for registering with us. We are excited to have you on board!<br><br><b>Below are your panel login details:</b><br>
            <b>Username:</b> " . ($request->reg_email ?? '') . "<br>
            <b>Password:</b> " . ($request->reg_password ?? '') . "<br><br>
            If you have any questions or need assistance, feel free to reach out to our support team.<br><br>
            Thank you for your interest.<br><br>
            <b>Best regards,</b><br>Webbrella Global";

            $viewName = 'emails.welcome';
            $viewData = ["name" => ($request->reg_name ?? 'User'), "messages" => $message];

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

            // --- Email Verification Logic ---
            $token = Str::random(64);
            EmailVerificationToken::create([
                'user_id' => $user->id,
                'token' => $token
            ]);

            $verifyUrl = url('/verify-email?token=' . $token . '&email=' . urlencode($user->email));

            $subject = 'Verify Your Email - Rusan';
            $message = "Dear " . $user->name . ",<br><br>
            Thank you for registering! Please verify your email address to activate your account.<br><br>
            <a href='" . $verifyUrl . "' style='padding: 10px 20px; background: #006666; color: #fff; text-decoration: none; border-radius: 5px;'>Verify Email Address</a><br><br>
            If the button above doesn't work, copy and paste this link into your browser:<br>" . $verifyUrl . "<br><br>
            <b>Your login details (Active after verification):</b><br>
            <b>Username:</b> " . $user->email . "<br>
            <b>Password:</b> " . $request->reg_password . "<br><br>
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
        try {
            $credentials = [
                'email' => $request->login_email,
                'password' => $request->login_password,
            ];

            if (Auth::attempt($credentials, true)) {

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

                // Retrieve related company and role information
                $company = Companies::find($user->cid);
                $role = Roles::find($user->role);

                // Store information in session
                session([
                    'companies' => $company,
                    'roles' => $role,
                ]);

                session(['loginEmail' => $request->login_email ?? '']);

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
        try {
            $to = $request->forgot_email;

            $getUser = User::where('email', '=', $to)->first();

            if (!$getUser) {
                return back()->with('error', 'No user found with this email address.');
            }

            $getSociety = Companies::where('id', '=', $getUser->cid)->first();

            // Generate a secure random token and store it
            $token = \Illuminate\Support\Str::random(64);
            \DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $to],
                ['email' => $to, 'token' => Hash::make($token), 'created_at' => now()]
            );

            $resetUrl = url('/new-password?token=' . $token . '&email=' . urlencode($to));

            $subject = 'Reset Your Password for Your CRM Account';

            $message = "Dear " . $getUser->name . ",<br><br>
            We received a request to reset your password for your CRM account. If you did not make this request, please ignore this email. Otherwise, follow the instructions below to reset your password.<br><br>
            <b>Reset Your Password:</b><br>
            <ul>
                <li>Click on the following link or copy and paste it into your browser: <a href='" . $resetUrl . "'>Password Reset Link</a></li>
                <li>Enter your new password in the provided field.</li>
                <li>Confirm your new password by re-entering it.</li>
                <li>Click the <b>Submit</b> button to complete the process.</li>
            </ul><br>
            For your security, this link will expire in 24 hours.<br><br>
            Thank you for being a valued member of the Webbrella community!<br><br>
            <b>Best regards,</b><br>" . ($getSociety->name ?? 'Rusan');

            $viewName = 'emails.welcome';
            $viewData = ["name" => $getUser->name, "messages" => $message];

            $this->leadService->sendMail($to, $subject, $viewName, $viewData, $getUser->id, $getUser->cid);

            return back()->with('success', 'Reset password link has been sent to your registered email address!');
        } catch (\Throwable $e) {
            Log::error('Forgot Password Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to send reset link. Please check your SMTP settings.');
        }
    }

    public function newPassword(Request $request)
    {
        $token = $request->token ?? '';
        $email = $request->email ?? '';

        $record = \DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || !Hash::check($token, $record->token)) {
            return back()->with('error', 'This password reset link is invalid or has expired.');
        }

        $getUser = User::where('email', $email)->first();

        if (!$getUser) {
            return back()->with('error', 'No user found with this email address.');
        }

        return view('newPassword', ['id' => $getUser->id, 'token' => $token, 'email' => $email]);

    }

    public function newPasswordPost(Request $request)
    {
        try {
            $request->validate([
                'new_password' => 'required|min:8',
                'uid' => 'required|exists:users,id',
            ]);

            $id = $request->uid ?? '';
            $user = User::findOrFail($id);
            $user->password = Hash::make($request->new_password);
            $user->update();

            // Invalidate the token
            \DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            return redirect('login')->with('success', 'Your password has been successfully updated! You can now log in using your new password.');
        } catch (\Throwable $e) {
            Log::error('New Password Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update password. Please try again.');
        }
    }

    public function logout(Request $request)
    {
        // 1. Clear specific custom session data you added during login
        session()->forget('companies');   // Or 'companies' if you stored the object
        session()->forget('roles');      // Or 'roles' if you stored the object
        //session()->forget('user_smtp_config'); // Crucial to clear SMTP config

        // 2. Log the user out from Laravel's authentication system
        Auth::logout();

        // 3. Invalidate the user's session.
        $request->session()->invalidate();

        // 4. Regenerate the CSRF token.
        $request->session()->regenerateToken();

        // 5. Redirect the user to the login page (or homepage)
        return redirect('/login') // Or route('login') if you use named routes
            ->with('info', 'You have been successfully logged out.'); // Use 'info' or 'success'
    }

    public function triggerCurl(Request $request)
    {

        $ese = Eselicenses::leftJoin('projects', 'eselicenses.project_id', '=', 'projects.id')
            ->select('projects.deployment_url', 'eselicenses.*')
            ->where('eselicenses.id', $request->id ?? '')
            ->first();

        // URL to trigger the action-core/index.php with cURL
        $url = ($ese->deployment_url ?? '') . 'vendor/coreoptions/index.php';

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

            $verifyToken = EmailVerificationToken::where('token', $token)->first();

            if (!$verifyToken) {
                return redirect('/login')->with('error', 'Invalid or expired verification link.');
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
            return redirect('/login')->with('error', 'Verification failed: ' . $e->getMessage());
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
