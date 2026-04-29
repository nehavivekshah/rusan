@extends('layout')
@section('title', 'Security - eseCRM')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => 'Security Settings'])

        <div class="dash-container d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 120px); background: #f8f9fa;">
            <div class="dash-card p-0" style="width: 100%; max-width: 440px; background: #fff; border: 1px solid #e8eaed; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.04);">
                
                {{-- Card Header --}}
                <div class="p-4 border-bottom text-center" style="background: linear-gradient(135deg, #006666, #004d4d); border-radius: 20px 20px 0 0;">
                    <div class="mb-3" style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i class="bx bx-shield-quarter text-white" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="text-white mb-1 fw-700">Update Password</h4>
                    <p class="text-white-50 small mb-0">Ensure your account uses a secure password</p>
                </div>

                <form action="{{ route('resetPassword') }}" method="POST" class="p-4">
                    @csrf
                    
                    {{-- New Password --}}
                    <div class="mb-4">
                        <label class="form-label fw-600 small text-muted mb-2">New Password*</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                <i class="bx bx-lock-alt text-muted"></i>
                            </span>
                            <input type="password" name="new_password" id="new_password" 
                                class="form-control border-start-0 bg-light shadow-none" 
                                placeholder="Min. 8 characters" required 
                                style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; padding: 10px 14px; font-size: 0.9rem;">
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-4">
                        <label class="form-label fw-600 small text-muted mb-2">Confirm Password*</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                <i class="bx bx-check-shield text-muted"></i>
                            </span>
                            <input type="password" name="cn_password" id="cn_password" 
                                class="form-control border-start-0 bg-light shadow-none" 
                                placeholder="Repeat password" required 
                                style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; padding: 10px 14px; font-size: 0.9rem;">
                        </div>
                        <div id="passwordError" class="text-danger small mt-2 fw-500" style="display:none; font-size: 0.75rem;">
                            <i class="bx bx-error-circle me-1"></i> Passwords do not match.
                        </div>
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="d-grid gap-2 mt-4 pt-2">
                        <button type="submit" id="submitButtonv" class="lb-btn lb-btn-primary py-2 fw-600">
                            <i class="bx bx-save me-1"></i> Update Security
                        </button>
                        <button type="reset" class="lb-btn lb-btn-secondary py-2 border-0 bg-transparent text-muted">
                            Cancel
                        </button>
                    </div>
                </form>

                <div class="p-3 text-center bg-light border-top" style="border-radius: 0 0 20px 20px;">
                    <div class="small text-muted" style="font-size: 0.72rem;">
                        <i class="bx bx-info-circle me-1"></i> 
                        Changing your password will prompt a re-login on next visit.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function() {
            const pass = document.getElementById('new_password');
            const conf = document.getElementById('cn_password');
            const err = document.getElementById('passwordError');
            const btn = document.getElementById('submitButtonv');

            function validate() {
                if (pass.value && conf.value && pass.value !== conf.value) {
                    err.style.display = 'block';
                    btn.disabled = true;
                    btn.style.opacity = '0.7';
                } else {
                    err.style.display = 'none';
                    btn.disabled = false;
                    btn.style.opacity = '1';
                }
            }

            pass.addEventListener('input', validate);
            conf.addEventListener('input', validate);
        })();
    </script>
@endsection

