<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /** Admin/staff may preview unpublished content on the public site. */
    protected function canPreview(): bool
    {
        $user = auth()->user();

        return $user && ($user->is_superuser == 1 || $user->is_staff == 1);
    }

    /** Keep tracking / token pages out of search engines. */
    protected function noindex($response)
    {
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
