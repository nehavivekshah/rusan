@extends('layout')
@section('title','CRM Register')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center h-90">
        <div class="col-md-4">
            <div class="col-md-12 text-center py-2">
                <img src="{{ asset('logo.png') }}" class="w-50" />
            </div>
            <div class="card w-100 shadow pl-3 pr-3">
                <form action="{{ route('register') }}" method="POST" class="card-body"  autocomplete="off">
                    <h4 class="card-title mb-4">Welcome<br><span class="small">Create a new CRM account</span></h4>
                    
                    @csrf
                    
                    <div class="form-group">
                        <div class="input-group">
                            <img src="{{ asset('assets/icons/user.svg') }}" class="input-icon" />
                            <input type="text" name="reg_name" class="form-control" placeholder="Enter your name" required />
                        </div>
                         @error('reg_name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <img src="{{ asset('assets/icons/mob.svg') }}" class="input-icon" />
                            <input type="text" name="reg_mob" class="form-control" placeholder="Enter your mobile no." required />
                        </div>
                        @error('reg_mob')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <img src="{{ asset('assets/icons/email.svg') }}" class="input-icon" />
                            <input type="email" name="reg_email" class="form-control" placeholder="Enter your email id" required />
                        </div>
                         @error('reg_email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <img src="{{ asset('assets/icons/lock.svg') }}" class="input-icon" />
                            <input type="password" name="reg_password" class="form-control" placeholder="Password" required />
                        </div>
                         @error('reg_password')
                            <span class="text-danger">{{ $message }}</span>
                         @enderror
                    </div>
                    <!--Company Details-->
                    <h4 class="h5 card-title-2">Company Details</h4>
                    <div class="form-group">
                        <div class="input-group">
                            <img src="{{ asset('assets/icons/edit.svg'); }}" class="input-icon" />
                            <input type="text" name="reg_company" class="form-control" placeholder="Enter your company name" required />
                        </div>
                        @error('reg_company')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <img src="{{ asset('assets/icons/edit.svg'); }}" class="input-icon" />
                            <input type="text" name="reg_gst" class="form-control" placeholder="Enter your gst no." />
                        </div>
                         @error('reg_gst')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                     <!-- Captcha -->
                    @php
                        $num1 = rand(1, 10);
                        $num2 = rand(1, 10);
                        $captcha_answer = $num1 + $num2;
                    @endphp
                    <div class="form-group">
                        <label for="captcha" class="text-muted">Please solve: {{ $num1 }} + {{ $num2 }} = ?</label>
                        <input type="text" name="captcha" class="form-control" placeholder="Your answer" required />
                        <input type="hidden" name="captcha_answer" value="{{ $captcha_answer }}">
                           @error('captcha')
                            <span class="text-danger">{{ $message }}</span>
                           @enderror
                    </div>
                   <!-- Captcha End -->
                    <div class="form-group">
                        <button class="btn btn-default w-100">Submit</button>
                    </div>
                    <div class="form-group text-center">
                        Already Have an Account <a class="text-dark w-100" href="/login">Click Here</a>
                    </div>
                </form>
            </div>
            <!--@if (Session::has('success'))
                <div class="response-msg">
                    <div class="alert alert-success shadow" role="alert">
                        {{ Session::get('success') }}
                    </div>
                </div>
            @endif-->
        </div>
    </div>
</div>
@endsection
