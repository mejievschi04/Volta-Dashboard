<?php

return [
    'owner_id' => env('DEV_MODE_OWNER_ID'),
    'owner_username' => env('DEV_MODE_OWNER_USERNAME'),
    'state_file' => storage_path('app/dev-mode.json'),
];
