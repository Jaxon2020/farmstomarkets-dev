
<?php

protected function redirectTo($request)
{
    \Log::info('Authenticate middleware triggered. User is not authenticated.');
    if (!$request->expectsJson()) {
        return route('login');
    }
}
