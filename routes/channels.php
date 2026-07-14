<?php

use Illuminate\Support\Facades\Broadcast;



Broadcast::channel('chat.recruiter.{id}', function ($user, $id) {
    \Log::info('? Broadcasting Auth Request', [
        'Authenticated User ID' => $user->id ?? null,
        'Channel ID Requested' => $id,
        'Socket ID' => request()->input('socket_id'),
        'Channel Name' => request()->input('channel_name'),
        'Auth Header' => request()->header('Authorization'),
    ]);

    return $user->id == (int) $id;
});
 
// For Candidate channel
Broadcast::channel('chat.candidate.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

