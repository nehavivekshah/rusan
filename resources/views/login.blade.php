@extends('layout')
@section('title','Rusan Login')
@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center h-90">
        <div class="col-md-4">
            <div class="col-md-12 text-center py-2">
                <img src="{{ asset('logo.png') }}" class="w-50" />
            </div>
            <div class="card w-100 shadow pl-3 pr-3">
                <form action="{{ route('login.post') }}" method="POST" class="card-body" id="loginFRM">
                    <h4 class="card-title mb-4 mt-3">Welcome Back<br><span class="small">Login To Your Account</span></h4>
                    
                    @csrf
                    
                    <div class="form-group">
                        <div class="input-group">
                            <img src="{{ asset('assets/icons/email.svg') }}" class="input-icon" />
                            <input type="email" id="email" name="login_email" class="form-control" placeholder="Email Id" required />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <img src="{{ asset('assets/icons/lock.svg') }}" class="input-icon" />
                            <input type="password" id="password" name="login_password" class="form-control" placeholder="Password" required />
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="form-check">
                                <input type="checkbox" name="remember_me" id="remember_me" class="form-check-input">
                                <label for="remember_me" class="form-check-label small text-muted">Remember Me</label>
                            </div>
                            <a href="/forgot-password" class="forgotPassword">Forgot Password?</a>
                        </div>
                    </div>
                    
                    <div class="form-group mt-4">
                        <button type="button" class="btn btn-default w-100" onclick="submitLogin()">Login</button>
                    </div>
                    <div class="form-group text-center m-0">
                        Don't Have an Account? <a class="text-dark w-100" href="/register">Click Here</a>
                    </div>
                </form>
            </div>
            <!--@if (Session::has('error'))
                <div class="response-msg">
                    <div class="alert alert-danger shadow" role="alert">
                        {{ Session::get('error') }}
                    </div>
                </div>
            @endif-->
        </div>
    </div>
</div>
@endsection
