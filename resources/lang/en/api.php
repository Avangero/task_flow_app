<?php

return [
    // JWT errors
    'jwt' => [
        'token_expired' => 'Token has expired',
        'token_invalid' => 'Invalid token',
        'token_refresh_failed' => 'Could not refresh token',
        'token_error' => 'Token is missing',
        'token_missing' => 'Token is missing',
    ],

    // HTTP errors
    'http' => [
        'not_found' => 'Resource not found',
        'method_not_allowed' => 'Method not allowed',
        'validation_error' => 'Validation error',
        'unauthorized' => 'Unauthorized access',
        'internal_server_error' => 'Internal server error',
    ],

    // Authentication
    'auth' => [
        'invalid_credentials' => 'Invalid credentials',
        'user_not_found' => 'User not found',
        'registration_failed' => 'Registration failed',
        'logout_success' => 'Successfully logged out',
        'login_success' => 'Successfully logged in',
        'registration_success' => 'Successfully registered',
        'profile_retrieved' => 'Profile retrieved',
        'token_refreshed' => 'Token refreshed',
    ],

    // General messages
    'success' => 'Operation completed successfully',
    'error' => 'An error occurred',
    'access_denied' => 'Access denied',
    'unauthorized' => 'Unauthorized access',
];
