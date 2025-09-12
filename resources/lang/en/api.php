<?php

return [
    'jwt' => [
        'token_expired' => 'Your session has expired. Please log in again to continue.',
        'token_invalid' => 'We couldn’t verify your session. Please log in again.',
        'token_refresh_failed' => 'Session refresh failed. Please log in again.',
        'token_error' => 'Authorization token is missing. Please sign in and try again.',
        'token_missing' => 'Authorization token is missing. Please sign in and try again.',
    ],

    'http' => [
        'not_found' => 'We can’t find what you’re looking for.',
        'method_not_allowed' => 'This action is not available for the requested resource.',
        'validation_error' => 'Some fields need your attention. Please check the form.',
        'unauthorized' => 'Please sign in to continue.',
        'internal_server_error' => 'Something went wrong on our side. Please try again later.',
        'forbidden' => 'You don’t have permission to perform this action.',
        'success' => 'Done! Your request has been processed.',
        'too_many_requests' => 'Too many attempts. Please wait a moment and try again.',
    ],

    'auth' => [
        'invalid_credentials' => 'Email or password is incorrect.',
        'user_not_found' => 'We couldn’t find your account. Please try again or sign up.',
        'user_blocked' => 'Your account is blocked. Please contact support.',
        'registration_failed' => 'We couldn’t create your account. Please try again.',
        'logout_success' => 'You have been logged out successfully.',
        'login_success' => 'Welcome back! You are now signed in.',
        'registration_success' => 'Your account has been created. Welcome aboard!',
        'profile_retrieved' => 'Your profile has been loaded.',
        'token_refreshed' => 'Your session has been refreshed.',
    ],

    'success' => 'Operation completed successfully.',
    'error' => 'We couldn’t complete your request. Please try again.',
    'access_denied' => 'You don’t have access to this resource.',
    'unauthorized' => 'Please sign in to continue.',
];
