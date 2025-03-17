<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    protected $except = [
        'loginUser',  // Add the route you want to exclude from CSRF protection
        'api/*',       // Optionally exclude all routes under the 'api' prefix
    ];
}
