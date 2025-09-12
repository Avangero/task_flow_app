<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait WithForgetCache
{
    protected function forgetCache(string|array $keys): void
    {
        foreach ((array) $keys as $key) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            Cache::forget($key);
        }
    }
}
