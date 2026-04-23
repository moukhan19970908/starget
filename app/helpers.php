<?php

use App\Models\ActivityLog;
use App\Models\User;

if (!function_exists('activity_log')) {
    function activity_log(User $user, string $type, $subject = null, string $description = ''): void
    {
        ActivityLog::create([
            'user_id'      => $user->id,
            'type'         => $type,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->id,
            'description'  => $description,
        ]);
    }
}
