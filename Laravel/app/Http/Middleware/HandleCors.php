<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\HandleCors as Middleware;

class HandleCors extends Middleware
{
    protected $allowedOrigins = [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ];

    protected $allowedMethods = ['*'];

    protected $allowedHeaders = ['*'];

    protected $supportsCredentials = false;
}
