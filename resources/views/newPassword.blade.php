@extends('layout')
@section('title','New Password - Rusan')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center h-90">
        <div class="col-md-4">
            <div class="card w-100 shadow pl-3 pr-3">
                <form id="resetPasswordForm" action="{{ route('newPasswordPost') }}" method="POST" class="card-body" onsubmit="return validatePassword()">
                    <h4 class="card-title mb-4 mt-3">Reset Password <br><span class="small">Create a new password</span></h4>
                    @csrf
                    <input type="hidden" name="email" value="{{ $email ?? '' }}">
                    <input type="hidden" name="token" value="{{ $token ?? '' }}">
                    
                    <div class="form-group">
                        <div class="input-group">
                            <img src="{{ asset('assets/icons/lock.svg'); }}" class="input-icon" />
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="new Password" required minlength="8" />
                            <button type="button" class="btn btn-trans" 
                                onclick="togglePassword('new_password', 'toggleIconNew')">
                                <i class="bx bx-show-alt" id="toggleIconNew"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <img src="{{ asset('assets/icons/lock.svg'); }}" class="input-icon" />
                            <input type="password" name="cn_password" id="cn_password" class="form-control" placeholder="Confirm new Password" required minlength="8" />
                            <button type="button" class="btn btn-trans" 
                                onclick="togglePassword('cn_password', 'toggleIconCn')">
                                <i class="bx bx-show-alt" id="toggleIconCn"></i>
                            </button>
                        </div><br>
                        <div id="passwordError" class="text-danger newPassword" style="display:none;">Passwords do not match.</div>
                    </div>
                    
                    <div class="form-group">
                        <button class="btn btn-primary bg-primary text-white w-100">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId, iconId) {
    var passwordField = document.getElementById(fieldId); 
    var toggleIcon = document.getElementById(iconId); 
    if (passwordField.type === 'password') { 
        passwordField.type = 'text'; 
        toggleIcon.className = 'bx bx-hide'; 
    } else { 
        passwordField.type = 'password'; 
        toggleIcon.className = 'bx bx-show-alt'; 
    } 
}

function validatePassword() {
    var newPassword = document.getElementById('new_password').value;
    var confirmPassword = document.getElementById('cn_password').value;
    var passwordError = document.getElementById('passwordError');
    
    if (newPassword !== confirmPassword) {
        passwordError.style.display = 'block';
        return false;
    } else {
        passwordError.style.display = 'none';
        return true;
    }
}

document.getElementById('new_password').addEventListener('keyup', validatePasswordMatch);
document.getElementById('cn_password').addEventListener('keyup', validatePasswordMatch);

function validatePasswordMatch() {
    var newPassword = document.getElementById('new_password').value;
    var confirmPassword = document.getElementById('cn_password').value;
    var passwordError = document.getElementById('passwordError');

    if (newPassword !== confirmPassword) {
        passwordError.style.display = 'block';
    } else {
        passwordError.style.display = 'none';
    }
}
</script>

@endsection

