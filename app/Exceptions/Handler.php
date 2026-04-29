<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // If the request is a standard web request (not AJAX/JSON)
        if (!$request->expectsJson()) {
            // Ignore standard validation and auth exceptions which have their own handling
            if (!($e instanceof \Illuminate\Validation\ValidationException) && 
                !($e instanceof \Illuminate\Auth\AuthenticationException) &&
                !($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException)) {
                
                return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage())->withInput();
            }
        }

        return parent::render($request, $e);
    }
}
