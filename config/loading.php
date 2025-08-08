<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Loading Overlay Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the first-run loading overlay that appears
    | after successful login.
    |
    */

    'enabled' => env('LOADING_OVERLAY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Minimum Display Duration
    |--------------------------------------------------------------------------
    |
    | Minimum time (in milliseconds) to display the loading overlay, even if
    | data loads quickly. Set to 0 to disable enforced minimum.
    |
    */

    'min_duration' => env('LOADING_OVERLAY_MIN_DURATION', 3000),

    /*
    |--------------------------------------------------------------------------
    | Loading Messages
    |--------------------------------------------------------------------------
    |
    | Array of tech-themed messages to cycle through during loading.
    |
    */

    'messages' => [
        'Bootstrapping kernel…',
        'Invoking Turing routine…',
        'Mounting /humility',
        'Linking Von Neumann unit…',
        'Paging Schrödinger\'s cache…',
        'Negotiating handshakes with HAL (be nice)',
        'Compiling manners',
        'Initializing neural pathways…',
        'Parsing elegance algorithms…',
        'Loading quantum courtesy protocols…',
        'Synchronizing with universe.dll…',
        'Defragmenting social protocols…',
        'Optimizing human.exe…',
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Rotation Interval
    |--------------------------------------------------------------------------
    |
    | Time interval (in milliseconds) between message rotations.
    |
    */

    'message_interval' => env('LOADING_MESSAGE_INTERVAL', 900),

];