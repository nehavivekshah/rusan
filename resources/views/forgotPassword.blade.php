@extends('layout')
@section('title','Forgot Password - Rusan')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center h-90">
        <div class="col-md-4">
            <div class="card w-100 shadow pl-3 pr-3">
                <form action="{{ route('forgotPassword') }}" method="POST" class="card-body">
                    <h4 class="card-title mb-4 mt-3">Forgot Password <br><span class="small">Reset Your Account Password</span></h4>
                    @csrf
                    <div class="form-group">
                        <!--label for="Username" class="text-muted">Username*:</label-->
                        <div class="input-group">
                            <img src="{{ asset('assets/icons/email.svg'); }}" class="input-icon" />
                            <input type="email" name="forgot_email" class="form-control" placeholder="Email Id" required />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button class="btn btn-default w-100">Submit</button>
                    </div>
                    <div class="form-group text-center m-0">
                        Back to login? <a class="text-dark w-100 small" href="{{ route('login') }}">Click Here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
