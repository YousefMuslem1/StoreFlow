<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
         
        });

        // $this->reportable(function (Throwable $e, $request) {
        //     if ($e instanceof TokenMismatchException) {
        //         return redirect()->route('login');
        //     }
    
        //     return parent::render($request, $e);
        // });
    }
}
